<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Type;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminServiceAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!in_array(Auth::user()->type, ['super admin', 'admin'])) {
                return redirect()->back()->with('error', __('Access Denied! Admin access required.'));
            }
            return $next($request);
        });
    }

    public function index()
    {
        $today = Carbon::today();
        
        // Get all maintenance requests (all statuses)
        $allRequests = MaintenanceRequest::with(['properties', 'units', 'types', 'maintainers'])
            ->orderBy('request_date', 'desc')
            ->get();

        // Filter pending requests (unassigned or maintainer_id = 0)
        $pendingRequests = $allRequests->filter(function($request) {
            return $request->status === 'pending' && 
                   ($request->maintainer_id == 0 || $request->maintainer_id == null);
        });

        // Get all maintainers (operators) with their types
        $maintainers = User::where('type', 'maintainer')
            ->with(['maintainer.types'])
            ->orderBy('first_name', 'asc')
            ->get();

        // Get service types
        $serviceTypes = Type::where('type', 'issue')
            ->orderBy('title', 'asc')
            ->get();

        // Group requests by date
        $requestsByDate = $pendingRequests->groupBy(function ($request) {
            return Carbon::parse($request->request_date)->format('Y-m-d');
        });

        // Get today's assigned services
        $todayAssigned = MaintenanceRequest::where('status', 'in_progress')
            ->whereDate('request_date', $today)
            ->with(['properties', 'units', 'types', 'maintainers'])
            ->get();

        // Get tomorrow's assigned services
        $tomorrowAssigned = MaintenanceRequest::where('status', 'in_progress')
            ->whereDate('request_date', $today->copy()->addDay())
            ->with(['properties', 'units', 'types', 'maintainers'])
            ->get();

        return view('admin.service-assignment.index', compact(
            'allRequests',
            'pendingRequests',
            'maintainers',
            'serviceTypes',
            'requestsByDate',
            'todayAssigned',
            'tomorrowAssigned',
            'today'
        ));
    }

    public function assignService(Request $request)
    {
        $request->validate([
            'maintenance_request_id' => 'required|exists:maintenance_requests,id',
            'maintainer_id' => 'required|exists:users,id',
            'assigned_date' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        $maintenanceRequest = MaintenanceRequest::findOrFail($request->maintenance_request_id);
        $maintainer = User::findOrFail($request->maintainer_id);

        // Check if maintainer is available on the assigned date
        $existingAssignments = MaintenanceRequest::where('maintainer_id', $request->maintainer_id)
            ->whereDate('request_date', $request->assigned_date)
            ->where('status', 'in_progress')
            ->count();

        if ($existingAssignments >= 5) { // Limit to 5 services per day per maintainer
            return redirect()->back()->with('error', __('Maintainer already has maximum assignments for this date.'));
        }

        $maintenanceRequest->update([
            'maintainer_id' => $request->maintainer_id,
            'request_date' => $request->assigned_date,
            'status' => 'in_progress',
            'admin_notes' => $request->notes,
            'assigned_at' => now(),
            'assigned_by' => Auth::id()
        ]);

        return redirect()->back()->with('success', __('Service assigned successfully to') . ' ' . $maintainer->name);
    }

    public function reassignService(Request $request)
    {
        $request->validate([
            'maintenance_request_id' => 'required|exists:maintenance_requests,id',
            'new_maintainer_id' => 'required|exists:users,id',
            'new_date' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        $maintenanceRequest = MaintenanceRequest::findOrFail($request->maintenance_request_id);
        $newMaintainer = User::findOrFail($request->new_maintainer_id);

        // Check if new maintainer is available
        $existingAssignments = MaintenanceRequest::where('maintainer_id', $request->new_maintainer_id)
            ->whereDate('request_date', $request->new_date)
            ->where('status', 'in_progress')
            ->count();

        if ($existingAssignments >= 5) {
            return redirect()->back()->with('error', __('New maintainer already has maximum assignments for this date.'));
        }

        $maintenanceRequest->update([
            'maintainer_id' => $request->new_maintainer_id,
            'request_date' => $request->new_date,
            'admin_notes' => $request->notes,
            'reassigned_at' => now(),
            'reassigned_by' => Auth::id()
        ]);

        return redirect()->back()->with('success', __('Service reassigned successfully to') . ' ' . $newMaintainer->name);
    }

    public function unassignService(Request $request)
    {
        $request->validate([
            'maintenance_request_id' => 'required|exists:maintenance_requests,id'
        ]);

        $maintenanceRequest = MaintenanceRequest::findOrFail($request->maintenance_request_id);

        $maintenanceRequest->update([
            'maintainer_id' => 0,
            'status' => 'pending',
            'admin_notes' => 'Unassigned by admin',
            'unassigned_at' => now(),
            'unassigned_by' => Auth::id()
        ]);

        return redirect()->back()->with('success', __('Service unassigned successfully.'));
    }

public function maintainerSchedule($maintainerId = null)
{
    if ($maintainerId) {
        // Individual maintainer schedule view
        $maintainer = User::where('type', 'maintainer')
            ->where('id', $maintainerId)
            ->firstOrFail();

        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();

        // Get maintainer's schedule for the week
        $weeklySchedule = MaintenanceRequest::where('maintainer_id', $maintainerId)
            ->whereIn('status', ['pending', 'in_progress', 'completed'])
            ->with(['properties', 'units', 'types'])
            ->orderBy('request_date', 'asc')
            ->get();

        // ✅ Normalize locale (fixes "italian" vs "it")
        $locale = app()->getLocale();
        if ($locale === 'italian') {
            $locale = 'it';
        }

        // ✅ Apply Carbon and system locale
        \Carbon\Carbon::setLocale($locale);
        setlocale(LC_TIME, $locale . '_' . strtoupper($locale) . '.UTF-8');

        // Group by day
        $scheduleByDay = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $dayServices = $weeklySchedule->filter(function ($service) use ($date) {
                return Carbon::parse($service->request_date)->isSameDay($date);
            });

            $scheduleByDay[$date->format('Y-m-d')] = [
                'date' => $date->locale($locale),
                'services' => $dayServices,
                'count' => $dayServices->count(),
            ];
        }

        return view('admin.service-assignment.maintainer-schedule', compact(
            'maintainer',
            'scheduleByDay',
            'weeklySchedule'
        ));
    }

    // ----------------------------
    // Overview of all maintainers
    // ----------------------------
    else {
        $maintainers = User::where('type', 'maintainer')
            ->orderBy('first_name', 'asc')
            ->get();

        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();

        // Get all assignments for the week (including pending, in_progress, and completed)
        $weeklyAssignments = MaintenanceRequest::whereIn('status', ['pending', 'in_progress', 'completed'])
            ->whereNotNull('maintainer_id')
            ->where('maintainer_id', '!=', 0)
            ->with(['properties', 'units', 'types', 'maintainers'])
            ->get();

        $maintainerSchedules = [];
        foreach ($maintainers as $maintainer) {
            $maintainerServices = $weeklyAssignments->where('maintainer_id', $maintainer->id);

            $scheduleByDay = [];
            for ($i = 0; $i < 7; $i++) {
                $date = $weekStart->copy()->addDays($i);
                $dayServices = $maintainerServices->filter(function ($service) use ($date) {
                    return Carbon::parse($service->request_date)->isSameDay($date);
                });

                $scheduleByDay[$date->format('Y-m-d')] = [
                    'date' => $date,
                    'services' => $dayServices,
                    'count' => $dayServices->count(),
                ];
            }

            $maintainerSchedules[$maintainer->id] = [
                'maintainer' => $maintainer,
                'scheduleByDay' => $scheduleByDay,
                'totalServices' => $maintainerServices->count(),
            ];
        }

        return view('admin.service-assignment.operator-schedules', compact(
            'maintainers',
            'maintainerSchedules',
            'weekStart',
            'weekEnd'
        ));
    }
}



    public function bulkAssign(Request $request)
    {
        $request->validate([
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:maintenance_requests,id',
            'maintainer_id' => 'required|exists:users,id',
            'assigned_date' => 'required|date',
            'notes' => 'nullable|string|max:500'
        ]);

        $maintainer = User::findOrFail($request->maintainer_id);
        $assignedCount = 0;

        foreach ($request->service_ids as $serviceId) {
            $maintenanceRequest = MaintenanceRequest::find($serviceId);
            
            if ($maintenanceRequest && $maintenanceRequest->status === 'pending' && 
                ($maintenanceRequest->maintainer_id == 0 || $maintenanceRequest->maintainer_id == null)) {
                // Check maintainer availability
                $existingAssignments = MaintenanceRequest::where('maintainer_id', $request->maintainer_id)
                    ->whereDate('request_date', $request->assigned_date)
                    ->where('status', 'in_progress')
                    ->count();

                if ($existingAssignments < 5) {
                    $maintenanceRequest->update([
                        'maintainer_id' => $request->maintainer_id,
                        'request_date' => $request->assigned_date,
                        'status' => 'in_progress',
                        'admin_notes' => $request->notes,
                        'assigned_at' => now(),
                        'assigned_by' => Auth::id()
                    ]);
                    $assignedCount++;
                }
            }
        }

        return redirect()->back()->with('success', __('Successfully assigned') . ' ' . $assignedCount . ' ' . __('services to') . ' ' . $maintainer->name);
    }

    public function getMaintainerAvailability(Request $request)
    {
        $request->validate([
            'maintainer_id' => 'required|exists:users,id',
            'date' => 'required|date'
        ]);

        $assignments = MaintenanceRequest::where('maintainer_id', $request->maintainer_id)
            ->whereDate('request_date', $request->date)
            ->where('status', 'in_progress')
            ->with(['properties', 'units', 'types'])
            ->get();

        return response()->json([
            'assignments' => $assignments,
            'count' => $assignments->count(),
            'available' => $assignments->count() < 5
        ]);
    }

    public function getCompatibleMaintainers(Request $request)
    {
        $request->validate([
            'service_type_id' => 'required|exists:types,id'
        ]);

        // For now, get all maintainers since the type matching might not be set up correctly
        // TODO: Implement proper service type to maintainer type mapping
        $compatibleMaintainers = User::where('type', 'maintainer')
            ->with(['maintainer.types'])
            ->orderBy('first_name', 'asc')
            ->get();

        return response()->json([
            'maintainers' => $compatibleMaintainers->map(function($maintainer) {
                return [
                    'id' => $maintainer->id,
                    'name' => $maintainer->name,
                    'type' => $maintainer->maintainer->types->title ?? 'General'
                ];
            })
        ]);
    }
} 