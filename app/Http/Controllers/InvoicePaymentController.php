<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Stripe;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class InvoicePaymentController extends Controller
{
    public function paymentSettings()
    {
        $paymentSetting = invoicePaymentSettings(parentId());
        return $paymentSetting;
    }

    public function stripePayment(Request $request, $ids)
    {
        $settings = $this->paymentSettings();
        $id = \Illuminate\Support\Facades\Crypt::decrypt($ids);
        $invoice = Invoice::find($id);
        $amount = $request->amount;
        if ($invoice) {
            try {
                $transactionID = uniqid('', true);
                Stripe\Stripe::setApiKey($settings['STRIPE_SECRET']);
                $data = Stripe\Charge::create(
                    [
                        "amount" => 100 * $amount,
                        "currency" => $settings['CURRENCY'],
                        "source" => $request->stripeToken,
                        "description" => " Invoice - " . invoicePrefix() . $invoice->invoice_id,
                        "metadata" => ["order_id" => $transactionID],
                    ]
                );

                if ($data['amount_refunded'] == 0 && empty($data['failure_code']) && $data['paid'] == 1 && $data['captured'] == 1) {

                    if ($data['status'] == 'succeeded') {

                        $payment['invoice_id'] = $invoice->id;
                        $payment['transaction_id'] = $transactionID;
                        $payment['payment_type'] = 'Stripe';
                        $payment['amount'] = $amount;
                        $payment['receipt'] = isset($data['receipt_url']) ? $data['receipt_url'] : '';
                        $payment['notes'] = " Invoice - " . invoicePrefix() . $invoice->invoice_id;

                        Invoice::addPayment($payment);
                        return redirect()->back()->with('success', __('Invoice payment successfully completed.'));
                    } else {
                        return redirect()->back()->with('error', __('Your payment has failed.'));
                    }
                } else {
                    return redirect()->back()->with('error', __('Transaction has been failed.'));
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', __($e->getMessage()));
            }
        } else {
            return redirect()->back()->with('error', __('Invoice is deleted.'));
        }
    }


    public function invoicePaypal(Request $request, $id)
    {
        $invoiceId = \Illuminate\Support\Facades\Crypt::decrypt($id);
        $paypalSetting = $this->paymentSettings();

        if ($paypalSetting['paypal_mode'] == 'live') {
            config([
                'paypal.live.client_id' => isset($paypalSetting['paypal_client_id']) ? $paypalSetting['paypal_client_id'] : '',
                'paypal.live.client_secret' => isset($paypalSetting['paypal_secret_key']) ? $paypalSetting['paypal_secret_key'] : '',
                'paypal.mode' => isset($paypalSetting['paypal_mode']) ? $paypalSetting['paypal_mode'] : '',
                'paypal.currency' => isset($paypalSetting['CURRENCY']) ? $paypalSetting['CURRENCY'] : '',
            ]);
        } else {
            config([
                'paypal.sandbox.client_id' => isset($paypalSetting['paypal_client_id']) ? $paypalSetting['paypal_client_id'] : '',
                'paypal.sandbox.client_secret' => isset($paypalSetting['paypal_secret_key']) ? $paypalSetting['paypal_secret_key'] : '',
                'paypal.mode' => isset($paypalSetting['paypal_mode']) ? $paypalSetting['paypal_mode'] : '',
                'paypal.currency' => isset($paypalSetting['CURRENCY']) ? $paypalSetting['CURRENCY'] : '',
            ]);
        }

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));

        $paypalToken = $provider->getAccessToken();

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('invoice.paypal.status', [$invoiceId, 'success'], ['amount' => $request->amount]),
                "cancel_url" => route('invoice.paypal.status', [$invoiceId, 'cancel'], ['amount' => $request->amount]),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => isset($paypalSetting['CURRENCY']) ? $paypalSetting['CURRENCY'] : '',
                        "value" => $request->amount
                    ]
                ]
            ]
        ]);
        if (isset($response['id']) && $response['id'] != null) {
            // redirect to approve href
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    return redirect()->away($links['href']);
                }
            }
            return redirect()
                ->back()
                ->with('error', 'Something went wrong.');
        } else {
            return redirect()
                ->back()
                ->with('error', $response['message'] ?? 'Something went wrong.');
        }
    }

    public function invoicePaypalStatus(Request $request, $invoiceId, $status)
    {
        if ($status == 'success') {

            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            $transactionID = uniqid('', true);
            $invoice = Invoice::find($invoiceId);
            $response = $provider->capturePaymentOrder($request['token']);
            if (isset($response['status']) && $response['status'] == 'COMPLETED') {
                $payment['invoice_id'] = $invoiceId;
                $payment['transaction_id'] = $transactionID;
                $payment['payment_type'] = 'Stripe';
                $payment['amount'] = $request->amount;
                $payment['receipt'] = '';
                $payment['notes'] = " Invoice - " . invoicePrefix() . $invoice->invoice_id;

                Invoice::addPayment($payment);
                return redirect()->back()->with('success', __('Invoice payment successfully completed.'));
            } else {
                return redirect()
                    ->back()
                    ->with('error', $response['message'] ?? __('Something went wrong.'));
            }
        } else {
            return redirect()
                ->back()
                ->with('error', __('Transaction has been failed.'));
        }
    }

    public function banktransferPayment(Request $request, $id)
    {
        $invoiceId = \Illuminate\Support\Facades\Crypt::decrypt($id);
        $validator = \Validator::make(
            $request->all(),
            [
                'receipt' => 'required',
                'amount' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        if (!empty($request->receipt)) {
            $recieptFilenameWithExt = $request->file('receipt')->getClientOriginalName();
            $recieptFilename = pathinfo($recieptFilenameWithExt, PATHINFO_FILENAME);
            $recieptExtension = $request->file('receipt')->getClientOriginalExtension();
            $recieptFileName = $recieptFilename . '_' . time() . '.' . $recieptExtension;

            $directory = storage_path('upload/receipt');
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }
            $request->file('receipt')->storeAs('upload/receipt/', $recieptFileName);
            $payment['receipt'] = $recieptFileName;
        }


        $invoice = Invoice::find($invoiceId);
        $transactionID = uniqid('', true);

        $payment['invoice_id'] = $invoice->id;
        $payment['transaction_id'] = $transactionID;
        $payment['payment_type'] = 'Bank Transfer';
        $payment['amount'] = $request->amount;
        $payment['notes'] = $request->notes;

        Invoice::addPayment($payment);
        return redirect()->back()->with('success', __('Invoice payment successfully completed.'));
    }

    public function invoiceFlutterwave(Request $request, $invoice_id, $pay_id)
    {
        $invoiceID = \Illuminate\Support\Facades\Crypt::decrypt($invoice_id);
        $invoice   = Invoice::find($invoiceID);
        $this->invoiceData = $invoice;
        $paymentSetting = $this->paymentSettings();
        $result    = array();
        if ($invoice) {
            try {
                $detail = array(
                    'txref' => $pay_id,
                    'SECKEY' => $paymentSetting['flutterwave_secret_key'],
                );
                $url     = "https://api.ravepay.co/flwv3-pug/getpaidx/api/v2/verify";
                $headersData = array('Content-Type' => 'application/json');
                $bodyData    = \Unirest\Request\Body::json($detail);
                $responseData = \Unirest\Request::post($url, $headersData, $bodyData);
                if (!empty($responseData)) {
                    $responseData = json_decode($responseData->raw_body, true);
                }
                if (isset($responseData['status']) && $responseData['status'] == 'success') {

                    $invoiceTransId = uniqid('', true);
                    $data['invoice_id'] = $invoice->id;
                    $data['transaction_id'] = $invoiceTransId;
                    $data['payment_type'] = 'Flutterwave';
                    $data['amount'] = $request->amount;
                    $data['notes'] = $request->notes;
                    Invoice::addPayment($data);
                    return redirect()->back()->with('success', __('Invoice payment successfully completed.'));
                } else {
                    return redirect()->back()->with('error', __('Transaction has been failed! '));
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }
    }
}
