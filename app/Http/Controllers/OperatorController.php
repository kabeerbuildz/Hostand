<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\Type;
use App\Models\Maintainer;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class OperatorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->type !== 'maintainer') {
                return redirect()->back()->with('error', __('Access Denied! Maintainer access required.'));
            }
            return $next($request);
        });
    }

    public function dashboard()
    {
        $operator = Auth::user();
        $today = Carbon::today();
        
        // Get today's assigned services
        $todayServices = MaintenanceRequest::where('maintainer_id', $operator->id)
            ->whereDate('request_date', $today)
            ->with(['properties', 'units', 'types'])
            ->orderBy('request_date', 'asc')
            ->get();

        // Get this week's services
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        
        $weeklyServices = MaintenanceRequest::where('maintainer_id', $operator->id)
            ->whereBetween('request_date', [$weekStart, $weekEnd])
            ->with(['properties', 'units', 'types'])
            ->orderBy('request_date', 'asc')
            ->get();

        // Get completed services this month
        $monthStart = $today->copy()->startOfMonth();
        $completedServices = MaintenanceRequest::where('maintainer_id', $operator->id)
            ->where('status', 'completed')
            ->whereBetween('request_date', [$monthStart, $today])
            ->with(['properties', 'units', 'types'])
            ->get();

        // Calculate statistics
        $totalCompleted = $completedServices->count();
        $totalHours = $completedServices->sum('hours_worked') ?? 0;
        $pendingToday = $todayServices->where('status', 'pending')->count();
        $inProgressToday = $todayServices->where('status', 'in_progress')->count();

        return view('operator.dashboard', compact(
            'todayServices',
            'weeklyServices', 
            'completedServices',
            'totalCompleted',
            'totalHours',
            'pendingToday',
            'inProgressToday',
            'operator'
        ));
    }

    public function dailyPlan()
    {
        $operator = Auth::user();
        $today = Carbon::today();
        
        // Get today's assigned services
        $dailyServices = MaintenanceRequest::where('maintainer_id', $operator->id)
            ->whereDate('request_date', $today)
            ->with(['properties', 'units', 'types'])
            ->orderBy('request_date', 'asc')
            ->get();

        // Group by status
        $pendingServices = $dailyServices->where('status', 'pending');
        $inProgressServices = $dailyServices->where('status', 'in_progress');
        $completedServices = $dailyServices->where('status', 'completed');

        return view('operator.daily-plan', compact(
            'dailyServices',
            'pendingServices',
            'inProgressServices', 
            'completedServices',
            'operator'
        ));
    }

    public function weeklyPlan()
    {
        $operator = Auth::user();
        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        
        // Get this week's services
        $weeklyServices = MaintenanceRequest::where('maintainer_id', $operator->id)
            ->whereBetween('request_date', [$weekStart, $weekEnd])
            ->with(['properties', 'units', 'types'])
            ->orderBy('request_date', 'asc')
            ->get();

        // Group services by day
        $servicesByDay = [];
        for ($i = 0; $i < 7; $i++) {
            $date = $weekStart->copy()->addDays($i);
            $dayServices = $weeklyServices->filter(function ($service) use ($date) {
                return Carbon::parse($service->request_date)->isSameDay($date);
            });
            
            $servicesByDay[$date->format('Y-m-d')] = [
                'date' => $date,
                'services' => $dayServices,
                'hasServices' => $dayServices->count() > 0
            ];
        }

        return view('operator.weekly-plan', compact('servicesByDay', 'operator'));
    }

    public function reports()
    {
        $operator = Auth::user();
        // dd($operator->first_name);
        $today = Carbon::today();
        
        // Get date range from request or default to current month
        $startDate = request('start_date', $today->copy()->startOfMonth()->format('Y-m-d'));
        $endDate = request('end_date', $today->format('Y-m-d'));
        
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Get all services in date range (not just completed ones for better visibility)
        $allServices = MaintenanceRequest::where('maintainer_id', $operator->id)
            ->whereBetween('request_date', [$start, $end])
            ->with(['properties', 'units', 'types'])
            ->orderBy('request_date', 'desc')
            ->get();



        // Get completed services specifically
        $completedServices = $allServices->where('status', 'completed');

        // Calculate statistics
        $totalServices = $completedServices->count();
        $totalHours = $completedServices->sum('hours_worked') ?? 0;
        $totalAmount = $completedServices->sum('amount') ?? 0;
        
        // Also calculate overall statistics for better visibility
        $totalAllServices = $allServices->count();
        $pendingServices = $allServices->where('status', 'pending')->count();
        $inProgressServices = $allServices->where('status', 'in_progress')->count();
        
        // Group by property (all services)
        $servicesByProperty = $allServices->groupBy('property_id');
        
        // Group by week (all services) - using start of week as key
        $servicesByWeek = $allServices->groupBy(function ($service) {
            return Carbon::parse($service->request_date)->startOfWeek()->format('Y-m-d');
        });

        return view('operator.reports', compact(
            'completedServices',
            'allServices',
            'totalServices',
            'totalHours',
            'totalAmount',
            'totalAllServices',
            'pendingServices',
            'inProgressServices',
            'servicesByProperty',
            'servicesByWeek',
            'startDate',
            'endDate',
            'operator'
        ));
    }

    public function updateServiceStatus(Request $request, $serviceId)
    {
        $operator = Auth::user();
        $service = MaintenanceRequest::where('id', $serviceId)
            ->where('maintainer_id', $operator->id)
            ->firstOrFail();

        

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
            'hours_worked' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        $service->update([
            'status' => $request->status,
            'hours_worked' => $request->filled('hours_worked') 
                    ? $request->hours_worked 
                    : 0,
            'operator_notes' => $request->notes,
            'completed_at' => $request->status === 'completed' ? now() : null
        ]);
        // if ($service->status !== 'in_progress') {
            $this->startTimer($service->id);
        // }


        if($service->started_at != null)
        {
            $this->stopTimer($service->id);
        }

        return redirect()->back()->with('success', __('Service status updated successfully!'));
    }

public function startTimer($serviceId)
{
    $operator = Auth::user();
    $service = MaintenanceRequest::where('id', $serviceId)
        ->where('maintainer_id', $operator->id)
        ->firstOrFail();

    // if ($service->status !== 'in_progress') {
    //     return redirect()->back()->with('error', __('Service must be in progress to start timer.'));
    // }

    // Only set started_at if not already set
    if (!$service->started_at) {
        $service->started_at = now();
        $service->save();
    }

    return redirect()->back()->with('success', __('Service started successfully!'));
}

public function stopTimer($serviceId)
{
    $operator = Auth::user();
    $service = MaintenanceRequest::where('id', $serviceId)
        ->where('maintainer_id', $operator->id)
        ->firstOrFail();

    if (!$service->started_at) {
        return redirect()->back()->with('error', __('Service has not been started.'));
    }

    $startTime = Carbon::parse($service->started_at);
    $endTime = now();

    // Use fractional hours
    $hoursWorked = $startTime->floatDiffInHours($endTime);

    // Update fields
    $service->ended_at = $endTime;
    $service->hours_worked = ($service->hours_worked ?? 0) + $hoursWorked;

    // Debug: Uncomment if needed
    // dd($service);

    $service->save();

    return redirect()->back()->with('success', __('Service ended. Hours recorded: ' . number_format($hoursWorked, 2)));
}


} 