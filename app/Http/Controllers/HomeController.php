<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Custom;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Maintainer;
use App\Models\MaintenanceRequest;
use App\Models\NoticeBoard;
use App\Models\PackageTransaction;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Subscription;
use App\Models\Support;
use App\Models\Tenant;
use App\Models\User;
use App\Models\FAQ;
use App\Models\Page;
use App\Models\HomePage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index()
    {
        if (\Auth::check()) {
            if (\Auth::user()->type == 'super admin') {
                // Legacy subscription/transaction KPIs removed on request. Instead provide
                // operational KPIs and today's services for the calendar widget.
                $result['totalOrganization'] = User::where('type', 'owner')->count();

                // Operational KPIs requested for the dashboard
                $result['activeCleaners'] = User::where('type', 'cleaner')->where('is_active', 1)->count();
                $result['activeRiders'] = User::where('type', 'rider')->where('is_active', 1)->count();
                $result['activeMaintenance'] = User::where('type', 'maintainer')->where('is_active', 1)->count();

                // Requests and support tickets
                $result['requestsToday'] = MaintenanceRequest::whereDate('request_date', date('Y-m-d'))->count();
                $result['openTickets'] = Support::where('status', 'open')->count();

                // Prepare events for FullCalendar (services for today)
                // Get maintenance requests
                $maintenanceRequests = MaintenanceRequest::with([
                    'properties' => function($query) {
                        $query->with(['propertyImages', 'totalUnits']);
                    },
                    'units',
                    'types',
                    'maintainers'
                ])
                ->orderBy('request_date', 'desc')
                ->get();

                // Add owner information to each request
                $maintenanceRequests->each(function ($request) {
                    if ($request->properties) {
                        $request->properties->owner = User::find($request->properties->parent_id);
                    }
                });

                // Debug maintenance requests
                \Log::info('Maintenance Requests:', ['count' => $maintenanceRequests->count()]);

                // Format events for FullCalendar with proper date handling
                $events = $maintenanceRequests->map(function ($request) {
                    $statusColors = [
                        'pending'     => '#ffc107',
                        'in_progress' => '#17a2b8',
                        'completed'   => '#28a745',
                    ];

                    // Build start datetime
                    $startDate = Carbon::parse($request->request_date);
                    if ($request->arrival_time) {
                        $startDateTime = $startDate->format('Y-m-d') . ' ' . $request->arrival_time;
                    } else {
                        $startDateTime = $startDate->format('Y-m-d');
                    }

                    // Build title with time if available
                    $title = __($request->types->title ?? 'No Issue');
                    if ($request->arrival_time) {
                        $title .= ' (' . Carbon::parse($request->arrival_time)->format('h:i A') . ')';
                    }

                    $event = [
                        'id'             => $request->id,
                        'title'          => $title,
                        'start'          => $startDateTime,
                        'className'      => 'status-' . $request->status,
                        'backgroundColor'=> $statusColors[$request->status] ?? '#6c757d',
                        'borderColor'    => $statusColors[$request->status] ?? '#6c757d',
                        'textColor'      => '#ffffff',
                        'extendedProps'  => [
                            'property'   => __($request->properties->name ?? '-'),
                            'unit'       => __($request->units->name ?? '-'),
                            'maintainer' => __($request->maintainers->name ?? '-'),
                            'status'     => __($request->status),
                            'attachment' => $request->issue_attachment,
                            'description'=> $request->description,
                            'owner'      => $request->properties->owner->name ?? 'Unknown',
                            'show_url'   => route('maintenance-request.show', $request->id),
                            'edit_url'   => route('maintenance-request.edit', $request->id),
                            'delete_url' => route('maintenance-request.destroy', $request->id),
                            'status_url' => route('maintenance-request.action', $request->id),
                        ]
                    ];

                    // Debug each event
                    \Log::info('Calendar Event:', $event);

                    return $event;
                })->toArray();

                // Format events for FullCalendar
                $events = $maintenanceRequests->map(function ($request) {
                    $statusColors = [
                        'pending'     => '#ffc107',
                        'in_progress' => '#17a2b8',
                        'completed'   => '#28a745',
                    ];

                    $title = __($request->types->title ?? 'No Issue');
                    if (!empty($request->arrival_time)) {
                        $title .= ' (' . \Carbon\Carbon::parse($request->arrival_time)->format('h:i A') . ')';
                    }

                    return [
                        'id'             => $request->id,
                        'title'          => $title,
                        'start'          => $request->arrival_time,
                        'className'      => 'status-' . $request->status,
                        'backgroundColor'=> $statusColors[$request->status] ?? '#6c757d',
                        'borderColor'    => $statusColors[$request->status] ?? '#6c757d',
                        'textColor'      => '#ffffff',
                        'extendedProps'  => [
                            'property'   => __($request->properties->name ?? '-'),
                            'unit'       => __($request->units->name ?? '-'),
                            'maintainer' => __($request->maintainers->name ?? '-'),
                            'status'     => __($request->status),
                            'attachment' => $request->issue_attachment,
                            'description'=> $request->description,
                            'owner'      => $request->properties->owner->name ?? 'Unknown',
                            'show_url'   => route('maintenance-request.show', $request->id),
                            'edit_url'   => route('maintenance-request.edit', $request->id),
                            'delete_url' => route('maintenance-request.destroy', $request->id),
                            'status_url' => route('maintenance-request.action', $request->id),
                        ]
                    ];
                })->toArray();

                // Notes for today (widget / shortcut)
                $notes = NoticeBoard::whereDate('created_at', date('Y-m-d'))->where('parent_id', parentId())->get();
                // Only mark widget available if the route exists to avoid route exceptions in view
                $notesWidgetAvailable = \Route::has('note.index');

                $result['organizationByMonth'] = $this->organizationByMonth();
                $result['paymentByMonth'] = $this->paymentByMonth();
                return view('dashboard.super_admin', compact('result', 'events', 'notes', 'notesWidgetAvailable'));
            } else {
                $result['totalNote'] = NoticeBoard::where('parent_id', parentId())->count();
                $result['totalContact'] = Contact::where('parent_id', parentId())->count();


                if (\Auth::user()->type == 'tenant') {
                    $tenant = Tenant::where('user_id', \Auth::user()->id)->first();
                    if (!empty($tenant)) {
                        $result['totalInvoice'] = Invoice::where('property_id', $tenant->property)->where('unit_id', $tenant->unit)->count();
                        $result['unit'] = PropertyUnit::find($tenant->unit);
                    } else {
                        $result['totalInvoice'] = 0;
                        $result['unit'] ='';
                    }


                    return view('dashboard.tenant', compact('result', 'tenant'));
                }

                if (\Auth::user()->type == 'maintainer') {
                    $maintainer = Maintainer::where('user_id', \Auth::user()->id)->first();
                    $result['totalRequest'] = MaintenanceRequest::where('maintainer_id', \Auth::user()->id)->count();
                    $result['todayRequest'] = MaintenanceRequest::whereDate('request_date', '=', date('Y-m-d'))->where('maintainer_id', \Auth::user()->id)->count();

                    return view('dashboard.maintainer', compact('result', 'maintainer'));
                }

                $result['totalProperty'] = Property::where('parent_id', parentId())->count();
                $result['totalUnit'] = PropertyUnit::where('parent_id', parentId())->count();
                $result['totalIncome'] = InvoicePayment::where('parent_id', parentId())->sum('amount');
                $result['totalExpense'] = Expense::where('parent_id', parentId())->sum('amount');
                $result['recentProperty'] = Property::where('parent_id', parentId())->orderby('id', 'desc')->limit(5)->get();
                $result['recentTenant'] = Tenant::where('parent_id', parentId())->orderby('id', 'desc')->limit(5)->get();
                $result['incomeExpenseByMonth'] = $this->incomeByMonth();
                $result['settings'] = settings();



                return view('dashboard.index', compact('result'));
            }
        } else {
            if (!file_exists(setup())) {
                header('location:install');
                die;
            } else {
                $landingPage = getSettingsValByName('landing_page');
                if ($landingPage == 'on') {
                    $subscriptions = Subscription::get();
                    $menus = Page::where('enabled', 1)->get();
                    $FAQs = FAQ::where('enabled', 1)->get();
                    return view('layouts.landing', compact('subscriptions', 'menus', 'FAQs'));
                } else {
                    return redirect()->route('login');
                }
            }
        }
    }

    public function organizationByMonth()
    {
        $start = strtotime(date('Y-01'));
        $end = strtotime(date('Y-12'));

        $currentdate = $start;

        $organization = [];
        while ($currentdate <= $end) {
            $organization['label'][] = date('M-Y', $currentdate);

            $month = date('m', $currentdate);
            $year = date('Y', $currentdate);
            $organization['data'][] = User::where('type', 'owner')->whereMonth('created_at', $month)->whereYear('created_at', $year)->count();
            $currentdate = strtotime('+1 month', $currentdate);
        }


        return $organization;
    }

    public function paymentByMonth()
    {
        $start = strtotime(date('Y-01'));
        $end = strtotime(date('Y-12'));

        $currentdate = $start;

        $payment = [];
        while ($currentdate <= $end) {
            $payment['label'][] = date('M-Y', $currentdate);

            $month = date('m', $currentdate);
            $year = date('Y', $currentdate);
            $payment['data'][] = PackageTransaction::whereMonth('created_at', $month)->whereYear('created_at', $year)->sum('amount');
            $currentdate = strtotime('+1 month', $currentdate);
        }

        return $payment;
    }

    public function incomeByMonth()
    {
        $start = strtotime(date('Y-01'));
        $end = strtotime(date('Y-12'));

        $currentdate = $start;

        $payment = [];
        while ($currentdate <= $end) {
            $payment['label'][] = date('M-Y', $currentdate);

            $month = date('m', $currentdate);
            $year = date('Y', $currentdate);
            $payment['income'][] = InvoicePayment::where('parent_id', parentId())->whereMonth('payment_date', $month)->whereYear('payment_date', $year)->sum('amount');
            $payment['expense'][] = Expense::where('parent_id', parentId())->whereMonth('date', $month)->whereYear('date', $year)->sum('amount');
            $currentdate = strtotime('+1 month', $currentdate);
        }

        return $payment;
    }
}
