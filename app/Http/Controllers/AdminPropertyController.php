<?php

namespace App\Http\Controllers;

use App\Models\Type;
use App\Models\User;
use App\Models\Property;
use App\Models\PropertyUnit;
use Illuminate\Http\Request;
use App\Models\PropertyImage;
use App\Models\MaintenanceRequest;

class AdminPropertyController extends Controller
{
    public function filterServices(Request $request)
    {
        $serviceTypeId = $request->get('service_type');
        $query = MaintenanceRequest::with(['properties', 'units', 'types', 'maintainers']);
        if ($serviceTypeId) {
            $query->where('issue_type', $serviceTypeId);
        }
        $maintenanceRequests = $query->orderBy('request_date', 'desc')->get();
        // Add owner info
        $maintenanceRequests->each(function ($request) {
            if ($request->properties) {
                $request->properties->owner = User::find($request->properties->parent_id);
            }
        });
        return view('admin.properties.partials.services_table_rows', compact('maintenanceRequests'))->render();
    }
    public function index()
    {
        if (\Auth::user()->type !== 'super admin') {
            return redirect()->back()->with('error', __('Permission Denied! Admin access required.'));
        }

        // Get all properties across all organizations with owner information
        $properties = Property::with(['thumbnail', 'propertyImages', 'totalUnits', 'maintenanceRequests'])
            ->where('is_active', 1)
            ->get();

        // Add owner information to each property
        $properties->each(function ($property) {
            $property->owner = User::find($property->parent_id);
        });

        return view('admin.properties.index', compact('properties'));
    }

    public function show(Property $property)
    {
        if (\Auth::user()->type !== 'super admin') {
            return redirect()->back()->with('error', __('Permission Denied! Admin access required.'));
        }

        // Load all relationships and owner information
        $property->load(['thumbnail', 'propertyImages', 'totalUnits', 'maintenanceRequests.units', 'maintenanceRequests.types', 'maintenanceRequests.maintainers']);
        $property->owner = User::find($property->parent_id);
        
        // Get units for this property
        $units = PropertyUnit::where('property_id', $property->id)->orderBy('id', 'desc')->get();
        
        // Get maintenance requests/services for this property
        $maintenanceRequests = MaintenanceRequest::where('property_id', $property->id)
            ->with(['units', 'types', 'maintainers'])
            ->orderBy('request_date', 'desc')
            ->get();
        
        return view('admin.properties.show', compact('property', 'units', 'maintenanceRequests'));
    }

    public function allServices()
    {
        if (\Auth::user()->type !== 'super admin') {
            return redirect()->back()->with('error', __('Permission Denied! Admin access required.'));
        }

        // Get all maintenance requests across all properties
        $maintenanceRequests = MaintenanceRequest::with(['properties', 'units', 'types', 'maintainers'])
            ->orderBy('request_date', 'desc')
            ->get();

        // Add owner information to each request
        $maintenanceRequests->each(function ($request) {
            if ($request->properties) {
                $request->properties->owner = User::find($request->properties->parent_id);
            }
        });

        // Get all service types for filter dropdown
        $serviceTypes = Type::where('type','issue')->get();

        return view('admin.properties.services', compact('maintenanceRequests', 'serviceTypes'));
    }

    public function analytics()
    {
        if (\Auth::user()->type !== 'super admin') {
            return redirect()->back()->with('error', __('Permission Denied! Admin access required.'));
        }

        // Calculate comprehensive analytics
        $totalProperties = Property::where('is_active', 1)->count();
        $totalUnits = PropertyUnit::count();
        $totalMaintenanceRequests = MaintenanceRequest::count();
        $pendingRequests = MaintenanceRequest::where('status', 'pending')->count();
        $inProgressRequests = MaintenanceRequest::where('status', 'in_progress')->count();
        $completedRequests = MaintenanceRequest::where('status', 'completed')->count();

        // Properties by owner
        $propertiesByOwner = Property::where('is_active', 1)
            ->get()
            ->groupBy('parent_id')
            ->map(function ($properties, $ownerId) {
                $owner = User::find($ownerId);
                return [
                    'owner' => $owner,
                    'properties_count' => $properties->count(),
                    'properties' => $properties
                ];
            });

        // Services by month
        $servicesByMonth = MaintenanceRequest::selectRaw('COUNT(*) as count, MONTH(request_date) as month, YEAR(request_date) as year')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        return view('admin.properties.analytics', compact(
            'totalProperties', 
            'totalUnits', 
            'totalMaintenanceRequests',
            'pendingRequests',
            'inProgressRequests', 
            'completedRequests',
            'propertiesByOwner',
            'servicesByMonth'
        ));
    }

public function maintenanceCalendar()
{
    if (\Auth::user()->type !== 'super admin') {
        return redirect()->back()->with('error', __('Permission Denied! Admin access required.'));
    }

    // Get all maintenance requests across all properties with relationships
    $maintenanceRequests = MaintenanceRequest::with([
        'properties' => function($query) {
            $query->with(['propertyImages', 'totalUnits']);
        },
        'units',
        'types',
        'maintainers'
    ])->orderBy('request_date', 'desc')->get();

    // Add owner information to each request
    $maintenanceRequests->each(function ($request) {
        if ($request->properties) {
            $request->properties->owner = User::find($request->properties->parent_id);
        }
    });

    // Format events for FullCalendar
    $events = $maintenanceRequests->map(function ($request) {
        $statusColors = [
            'pending'     => '#ffc107',
            'in_progress' => '#17a2b8',
            'completed'   => '#28a745',
        ];

        return [
            'id'             => $request->id,
            // 'title'          => __($request->types->title ?? 'No Issue'), // translate title
            'title' => __($request->types->title ?? 'No Issue') . ' (' . \Carbon\Carbon::parse($request->arrival_time)->format('h:i A') . ')',
            'start'          => $request->arrival_time,
            'className' => 'status-' . $request->status,
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
    });

    // 🔍 Debug all events before sending to view
    // dd($events->toArray());

    return view('admin.properties.maintenance-calendar', compact('events', 'maintenanceRequests'));
}


}