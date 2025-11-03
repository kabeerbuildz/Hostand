<?php

namespace App\Http\Controllers;

use App\Models\Type;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Invoice;
use App\Models\Property;
use App\Models\InvoiceItem;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Mail\SendInvoiceMail;
use App\Models\InvoicePayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{

    public function index()
    {
        if (\Auth::user()->type == 'super admin'|| \Auth::user()->can('manage invoice')) {
            if (\Auth::user()->type == 'tenant') {
                $tenant = Tenant::where('user_id', \Auth::user()->id)->first();
                $invoices = Invoice::where('property_id', $tenant->property)->where('unit_id', $tenant->unit)->where('parent_id', parentId())->get();
            } else {
                $users = User::select('id', 'email')->get();
                $invoices = Invoice::where('parent_id', parentId())->get();
            }

            return view('invoice.index', compact('invoices', 'users'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


   public function create()
{
    if (\Auth::user()->type == 'super admin' || \Auth::user()->can('create invoice')) {

        if (Auth::user()->type == 'super admin') {
            $property = Property::get()->pluck('name', 'id');
        } else {
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
        }

        $property->prepend(__('Select Property'), '');

        if(Auth::user()->type == 'super admin')
        {
            $types = Type::where('type', 'invoice')
            ->get()
            ->pluck('title', 'id');
        }else{
            $types = Type::where('parent_id', parentId())
            ->where('type', 'invoice')
            ->get()
            ->pluck('title', 'id');
        }
        
        $types->prepend(__('Select Type'), '');

        $invoiceNumber = $this->invoiceNumber();

        return view('invoice.create', compact('types', 'property', 'invoiceNumber'));

    } else {
        return redirect()->back()->with('error', __('Permission Denied!'));
    }
}



    public function store(Request $request)
    {
        if (\Auth::user()->type == 'super admin'|| \Auth::user()->can('create invoice')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'property_id' => 'required',
                    'unit_id' => 'required',
                    'invoice_month' => 'required',
                    'end_date' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $invoice = new Invoice();
            $invoice->invoice_id = $request->invoice_id;
            $invoice->property_id = $request->property_id;
            $invoice->unit_id = $request->unit_id;
            $invoice->invoice_month = $request->invoice_month . '-01';
            $invoice->end_date = $request->end_date;
            $invoice->notes = $request->notes;
            $invoice->status = 'open';
            $invoice->parent_id = parentId();
            $invoice->save();
            $types = $request->types;

            for ($i = 0; $i < count($types); $i++) {
                $invoiceItem = new InvoiceItem();
                $invoiceItem->invoice_id = $invoice->id;
                $invoiceItem->invoice_type = $types[$i]['invoice_type'];
                $invoiceItem->amount = $types[$i]['amount'];
                $invoiceItem->description = $types[$i]['description'];
                $invoiceItem->save();
            }

            $module = 'invoice_create';
            $notification = Notification::where('parent_id', parentId())->where('module', $module)->first();
            $setting = settings();
            $errorMessage = '';
            if (!empty($notification) && $notification->enabled_email == 1 && !empty($invoice->tenants()->user->email)) {
                $notification_responce = MessageReplace($notification, $invoice->id);
                $datas['subject'] = $notification_responce['subject'];
                $datas['message'] = $notification_responce['message'];
                $datas['module'] = $module;
                $datas['logo'] =  $setting['company_logo'];

                $to = $invoice->tenants()->user->email;
                $response = commonEmailSend($to, $datas);
                if ($response['status'] == 'error') {
                    $errorMessage = $response['message'];
                }
            }
            return redirect()->route('invoice.index')->with('success', __('Invoice successfully created.') . '</br>' . $errorMessage);
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function show(Invoice $invoice)
    {
        if (\Auth::user()->type == 'super admin'|| \Auth::user()->can('show invoice')) {
            $invoiceNumber = $invoice->invoice_id;
            $tenant = Tenant::where('property', $invoice->property_id)->where('unit', $invoice->unit_id)->first();

            $invoicePaymentSettings = invoicePaymentSettings($invoice->parent_id);

            $notification = Notification::where('parent_id', parentId())->where('module', 'payment_reminder')->first();
            return view('invoice.show', compact('invoiceNumber', 'invoice', 'tenant', 'invoicePaymentSettings', 'notification'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function edit(Invoice $invoice)
    {
        if (\Auth::user()->type == 'super admin'|| \Auth::user()->can('edit invoice')) {
            $property = Property::where('parent_id', parentId())->get()->pluck('name', 'id');
            $property->prepend(__('Select Property'), '');
            $types = Type::where('parent_id', parentId())->where('type', 'invoice')->get()->pluck('title', 'id');
            $types->prepend(__('Select Type'), '');

            $invoiceNumber = $invoice->invoice_id;
            return view('invoice.edit', compact('types', 'property', 'invoiceNumber', 'invoice'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function update(Request $request, Invoice $invoice)
    {
        if (\Auth::user()->type == 'super admin'|| \Auth::user()->can('edit invoice')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'property_id' => 'required',
                    'unit_id' => 'required',
                    'invoice_month' => 'required',
                    'end_date' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $invoice->property_id = $request->property_id;
            $invoice->unit_id = $request->unit_id;
            $invoice->invoice_month = $request->invoice_month . '-01';
            $invoice->end_date = $request->end_date;
            $invoice->notes = $request->notes;
            $invoice->save();
            $types = $request->types;

            for ($i = 0; $i < count($types); $i++) {
                $invoiceItem = InvoiceItem::find($types[$i]['id']);
                if ($invoiceItem == null) {
                    $invoiceItem = new InvoiceItem();
                    $invoiceItem->invoice_id = $invoice->id;
                }

                $invoiceItem->invoice_type = $types[$i]['invoice_type'];
                $invoiceItem->amount = $types[$i]['amount'];
                $invoiceItem->description = $types[$i]['description'];
                $invoiceItem->save();
            }
            return redirect()->route('invoice.index')->with('success', __('Invoice successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }


    public function destroy(Invoice $invoice)
    {
        if (\Auth::user()->type == 'super admin'|| \Auth::user()->can('delete invoice')) {
            InvoiceItem::where('invoice_id', $invoice->id)->delete();
            InvoicePayment::where('invoice_id', $invoice->id)->delete();
            $invoice->delete();
            return redirect()->route('invoice.index')->with('success', __('Invoice successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function invoiceNumber()
    {
        $latest = Invoice::where('parent_id', parentId())->latest()->first();
        if ($latest == null) {
            return 1;
        } else {
            return $latest->invoice_id + 1;
        }
    }

    public function invoiceTypeDestroy(Request $request)
    {
        if (\Auth::user()->type == 'super admin'|| \Auth::user()->can('delete invoice type')) {
            $invoiceType = InvoiceItem::find($request->id);
            $invoiceType->delete();

            return response()->json([
                'status' => 'success',
                'msg' => __('Property successfully updated.'),
            ]);
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function invoicePaymentCreate($invoice_id)
    {
        $invoice = Invoice::find($invoice_id);
        return view('invoice.payment', compact('invoice_id', 'invoice'));
    }

    public function invoicePaymentStore(Request $request, $invoice_id)
    {

        if (\Auth::user()->type == 'super admin'|| \Auth::user()->can('create invoice payment')) {
            $invoice = Invoice::find($invoice_id);
            $dueAmount = $invoice->getInvoiceDueAmount();

            $validator = \Validator::make(
                $request->all(),
                [
                    'payment_date' => 'required',
                    'amount' => 'required|numeric|min:1|max:' . $dueAmount,
                ],

            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            if (!empty($request->receipt)) {
                $receiptFilenameWithExt = $request->file('receipt')->getClientOriginalName();
                $receiptFilename = pathinfo($receiptFilenameWithExt, PATHINFO_FILENAME);
                $receiptExtension = $request->file('receipt')->getClientOriginalExtension();
                $receiptFileName = $receiptFilename . '_' . time() . '.' . $receiptExtension;
                $dir = storage_path('upload/receipt');
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $request->file('receipt')->storeAs('upload/receipt/', $receiptFileName);
            }

            $payment = new InvoicePayment();
            $payment->invoice_id = $invoice_id;
            $payment->transaction_id = md5(time());
            $payment->payment_type = __('Manually');
            $payment->amount = $request->amount;
            $payment->payment_date = $request->payment_date;
            $payment->receipt = !empty($request->receipt) ? $receiptFileName : '';
            $payment->notes = $request->notes;
            $payment->parent_id = parentId();
            $payment->save();
            $invoice = Invoice::find($invoice_id);
            if ($invoice->getInvoiceDueAmount() <= 0) {
                $status = 'paid';
            } else {
                $status = 'partial_paid';
            }
            Invoice::statusChange($invoice->id, $status);
            return redirect()->back()->with('success', __('Invoice payment successfully added.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function invoicePaymentDestroy($invoice_id, $id)
    {
        if (\Auth::user()->type == 'super admin'|| \Auth::user()->can('delete invoice payment')) {
            $payment = InvoicePayment::find($id);
            $payment->delete();

            $invoice = Invoice::find($invoice_id);
            if ($invoice->getInvoiceDueAmount() <= 0) {
                $status = 'paid';
            } elseif ($invoice->getInvoiceDueAmount() == $invoice->getInvoiceSubTotalAmount()) {
                $status = 'open';
            } else {
                $status = 'partial_paid';
            }
            Invoice::statusChange($invoice->id, $status);
            return redirect()->back()->with('success', __('Invoice payment successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied!'));
        }
    }

    public function invoicePaymentRemind($id)
    {
        $notification = Notification::where('parent_id', parentId())->where('module', 'payment_reminder')->first();
        $short_code = $notification->short_code;
        $notification->short_code = json_decode($notification->short_code);

        $Notifications = Notification::$modules;
        $notification_option = [];
        foreach ($Notifications as $key => $value) {
            $notification_option[$key] = $value['name'];
        }
        return view('invoice.remind', compact('notification', 'notification_option', 'Notifications', 'id'));
    }

    public function invoicePaymentRemindData(Request $request, $id)
    {
        $invoice = Invoice::find($id);
        $tenant = Tenant::where('property', $invoice->property_id)->where('unit', $invoice->unit_id)->first();
        $user = User::find($tenant->user_id);

        $notification = Notification::where('parent_id', parentId())->where('module', 'payment_reminder')->first();
        $module = 'payment_reminder';

        $setting = settings();
        $errorMessage = '';
        if (!empty($notification) && $notification->enabled_email == 1) {


            $return['subject'] = $request->subject;
            $return['message'] = $request->message;
            $settings = settings();

            if (!empty($request->subject) && !empty($request->message)) {
                $search = [];
                $replace = [];

                $invoice = Invoice::find($id);
                $user_name = $invoice->tenants()->user->name;
                $invoice_number = invoicePrefix() . $invoice->invoice_id;
                $search = ['{company_name}', '{company_email}', '{company_phone_number}', '{company_address}', '{company_currency}', '{user_name}', '{invoice_number}', '{invoice_date}', '{invoice_due_date}', '{amount}', '{invoice_description}'];
                $replace = [$settings['company_name'], $settings['company_email'], $settings['company_phone'], $settings['company_address'], $settings['CURRENCY_SYMBOL'], $user_name, $invoice_number, $invoice->created_at, $invoice->end_date, priceFormat($invoice->getInvoiceDueAmount()), $invoice->notes];

                $return['subject'] = str_replace($search, $replace, $request->subject);
                $return['message'] = str_replace($search, $replace, $request->message);
            }

            $datas['subject'] = $return['subject'];
            $datas['message'] = $return['message'];
            $datas['module'] = $module;
            $datas['logo'] =  $setting['company_logo'];
            $to = $user->email;
            $response = commonEmailSend($to, $datas);
            if ($response['status'] == 'error') {
                $errorMessage = $response['message'];
            }
        }

        return redirect()->back()->with('success', __('Email successfully sent.') . '</br>' . $errorMessage);
    }

  public function sendMail(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'invoice_id' => 'required|exists:invoices,id',
    ]);

    $user = User::findOrFail($request->user_id);
    $invoice = Invoice::findOrFail($request->invoice_id);

    Mail::to($user->email)->send(new SendInvoiceMail($invoice));

    return back()->with('success', __('invoice_email_to') . $user->email);
}

}
