<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\FAQ;
use App\Models\Page;
use App\Models\User;
use App\Models\Custom;
use App\Models\Tenant;
use App\Models\Contact;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Support;
use App\Models\HomePage;
use App\Models\Property;
use App\Models\Maintainer;
use App\Models\NoticeBoard;
use App\Models\PropertyUnit;
use App\Models\Subscription;
use App\Models\InvoicePayment;
use App\Models\MaintenanceRequest;

use App\Models\PackageTransaction;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    protected $operatorController;

    public function __construct(OperatorController $operatorController)
    {
        $this->operatorController = $operatorController;
    }

    public function index()
    {
        if (\Auth::check()) {
            if (\Auth::user()->type == 'super admin') {
                $result['totalOrganization'] = User::where('type', 'owner')->count();
                $result['totalSubscription'] = Subscription::count();
                $result['totalTransaction'] = PackageTransaction::count();
                $result['totalIncome'] = PackageTransaction::sum('amount');
                $result['totalNote'] = NoticeBoard::where('parent_id', parentId())->count();
                $result['totalContact'] = Contact::where('parent_id', parentId())->count();

                $result['totalOwner'] = User::where('type', 'owner')->count();
                $result['totalService'] = MaintenanceRequest::where('status', 'in_progress')->count();
                $result['totalOpenTicket'] = Support::where('status', 'open')->count();

                $result['organizationByMonth'] = $this->organizationByMonth();
                $result['paymentByMonth'] = $this->paymentByMonth();

                $notes = NoticeBoard::whereDate('created_at', now()->toDateString())->get();
                $recentMessages = Contact::where('parent_id', Auth::user()->id)->latest()->limit(5)->get();

                // dd($notes);

                $maintenanceRequests = MaintenanceRequest::with([
                    'properties' => function ($query) {
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
                Log::info('Maintenance Requests:', ['count' => $maintenanceRequests->count()]);

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
                        'backgroundColor' => $statusColors[$request->status] ?? '#6c757d',
                        'borderColor'    => $statusColors[$request->status] ?? '#6c757d',
                        'textColor'      => '#ffffff',
                        'extendedProps'  => [
                            'property'   => __($request->properties->name ?? '-'),
                            'unit'       => __($request->units->name ?? '-'),
                            'maintainer' => __($request->maintainers->name ?? '-'),
                            'status'     => __($request->status),
                            'attachment' => $request->issue_attachment,
                            'description' => $request->description,
                            'owner'      => $request->properties->owner->name ?? 'Unknown',
                            'show_url'   => route('maintenance-request.show', $request->id),
                            'edit_url'   => route('maintenance-request.edit', $request->id),
                            'delete_url' => route('maintenance-request.destroy', $request->id),
                            'status_url' => route('maintenance-request.action', $request->id),
                        ]
                    ];

                    // Debug each event
                    Log::info('Calendar Event:', $event);

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
                        'backgroundColor' => $statusColors[$request->status] ?? '#6c757d',
                        'borderColor'    => $statusColors[$request->status] ?? '#6c757d',
                        'textColor'      => '#ffffff',
                        'extendedProps'  => [
                            'property'   => __($request->properties->name ?? '-'),
                            'unit'       => __($request->units->name ?? '-'),
                            'maintainer' => __($request->maintainers->name ?? '-'),
                            'status'     => __($request->status),
                            'attachment' => $request->issue_attachment,
                            'description' => $request->description,
                            'owner'      => $request->properties->owner->name ?? 'Unknown',
                            'show_url'   => route('maintenance-request.show', $request->id),
                            'edit_url'   => route('maintenance-request.edit', $request->id),
                            'delete_url' => route('maintenance-request.destroy', $request->id),
                            'status_url' => route('maintenance-request.action', $request->id),
                        ]
                    ];
                })->toArray();
                return view('dashboard.super_admin', compact('result', 'events', 'notes', 'recentMessages'));
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
                        $result['unit'] = '';
                    }


                    return view('dashboard.tenant', compact('result', 'tenant'));
                }

                if (\Auth::user()->type == 'maintainer') {
                    $maintainer = Maintainer::where('user_id', \Auth::user()->id)->first();
                    $result['totalRequest'] = MaintenanceRequest::where('maintainer_id', \Auth::user()->id)->count();
                    $result['todayRequest'] = MaintenanceRequest::whereDate('request_date', '=', date('Y-m-d'))->where('maintainer_id', \Auth::user()->id)->count();

                    // return view('dashboard.maintainer', compact('result', 'maintainer'));
                    // return app(OperatorController::class)->dailyPlan();
                    return $this->operatorController->dailyPlan();
                }

                $result['totalProperty'] = Property::where('parent_id', parentId())->count();
                $result['totalUnit'] = PropertyUnit::where('parent_id', parentId())->count();
                $result['totalIncome'] = InvoicePayment::where('parent_id', parentId())->sum('amount');

                // dd()

                $result['totalServices'] = MaintenanceRequest::where('status', 'in_progress')->count();

                $result['recentProperty'] = Property::where('parent_id', parentId())->orderby('id', 'desc')->limit(5)->get();
                $result['recentTenant'] = Tenant::where('parent_id', parentId())->orderby('id', 'desc')->limit(5)->get();
                $result['incomeExpenseByMonth'] = $this->incomeByMonth();
                $result['settings'] = settings();

                $maintenanceRequests = MaintenanceRequest::where('parent_id', parentId())->get();


                $maintenanceRequests->load([
                    'properties' => function ($query) {
                        $query->with(['propertyImages', 'totalUnits']);
                    },
                    'units',
                    'types',
                    'maintainers'
                ]);

                // ✅ Build events array
                $events = $maintenanceRequests->map(function ($request) {

                    // Try getting property via relationship first
                    $property = $request->properties;

                    // If not found, fetch manually using Eloquent
                    if (!$property && $request->property_id) {
                        $property = Property::where('id', $request->property_id)->first();
                    }

                    // Try getting unit via relationship first
                    $unit = $request->units;

                    // If not found, fetch manually
                    if (!$unit && $request->unit_id) {
                        $unit = \App\Models\PropertyUnit::where('id', $request->unit_id)->first();
                    }
                    // Extract readable names (fallback to defaults)
                    $propertyName = $property->name ?? 'Unknown Property';
                    $unitName = $unit->name ?? 'Unknown Unit';

                    // ✅ Return event structure
                    return [
                        'id' => $request->id,
                        'title' => $request->types->title ?? 'No Issue',
                        'start' => $request->arrival_time,
                        'extendedProps' => [
                            'property_id' => $request->property_id,
                            'property_name' => $propertyName,
                            'unit_id' => $request->unit_id,
                            'unit_name' => $unitName,
                            'maintainer' => $request->maintainers->name ?? '-',
                            'status' => $request->status,
                            'attachment' => $request->issue_attachment,
                            'people_count' => $request->people_count,
                            'arrival_time' => $request->arrival_time,
                            'show_url' => route('maintenance-request.show', $request->id),
                            'edit_url' => route('maintenance-request.edit', $request->id),
                            'delete_url' => route('maintenance-request.destroy', $request->id),
                            'status_url' => route('maintenance-request.action', $request->id),
                        ]
                    ];
                });

                return view('dashboard.index', compact('result', 'events'));
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
