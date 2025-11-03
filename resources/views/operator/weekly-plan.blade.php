@extends('layouts.app')
@section('page-title')
    {{ __('Weekly Plan') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}">{{ __('Operator Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Weekly Plan') }}</li>
@endsection

@section('content')
    <!-- Header Section -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-gradient-warning text-dark">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class=" mb-1">{{ __('Weekly Plan') }}</h3>
                            <p class="text-dark-50 mb-0">
                                <i class="ti ti-calendar-week"></i> {{ __('Weekly work schedule for') }} {{ $operator->first_name }} {{ $operator->last_name }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            {{-- <a href="{{ route('operator.dashboard') }}" class="btn btn-light me-2">
                                <i class="ti ti-arrow-left"></i> {{ __('Back to Dashboard') }}
                            </a> --}}
                            <a href="{{ route('operator.weekly-plan') }}" class="btn btn-light">
                                <i class="ti ti-calendar"></i> {{ __('Weekly plain') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Calendar -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-primary">
                        <i class="ti ti-calendar-week"></i> {{ __('This Week\'s Schedule') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($servicesByDay as $date => $dayData)
                            <div class="col-lg-3 col-md-6 mb-4">
                                <div class="card border-0 shadow-sm {{ $dayData['hasServices'] ? 'border-success' : 'border-danger' }}">
                                    <div class="card-header bg-transparent border-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 {{ $dayData['hasServices'] ? 'text-success' : 'text-danger' }}">
                                                {{ $dayData['date']->format('l') }}
                                            </h6>
                                            <small class="text-muted">{{ $dayData['date']->format('M d') }}</small>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @if($dayData['hasServices'])
                                            <div class="text-center mb-3">
                                                <span class="badge bg-success">{{ $dayData['services']->count() }} {{ __('Services') }}</span>
                                            </div>
                                            
                                            @foreach($dayData['services'] as $service)
                                                <div class="card mb-2 border-light">
                                                    <div class="card-body p-2">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <div class="flex-grow-1">
                                                                <h6 class="mb-1 small">{{ $service->properties->name ?? 'N/A' }}</h6>
                                                                <small class="text-muted">{{ $service->units->name ?? 'N/A' }}</small>
                                                                <br>
                                                                <small class="text-muted">{{ $service->types->title ?? 'N/A' }}</small>
                                                            </div>
                                                            <div class="text-end">
                                                                @if($service->status == 'pending')
                                                                    <span class="badge bg-warning small">{{ __('Pending') }}</span>
                                                                @elseif($service->status == 'in_progress')
                                                                    <span class="badge bg-info small">{{ __('In Progress') }}</span>
                                                                @elseif($service->status == 'completed')
                                                                    <span class="badge bg-success small">{{ __('Completed') }}</span>
                                                                @endif
                                                                <br>
                                                                <small class="text-muted">{{ \Carbon\Carbon::parse($service->request_date)->format('H:i') }}</small>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mt-2">
                                                            <a href="#" class="btn btn-outline-primary btn-sm customModal" 
                                                               data-size="lg" data-title="{{ __('View Service Details') }}" 
                                                               data-url="{{ route('maintenance-request.show', $service->id) }}">
                                                                <i class="ti ti-eye"></i> {{ __('View') }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-center py-4">
                                                <i class="ti ti-calendar-off" style="font-size: 2rem; color: #dc3545;"></i>
                                                <p class="text-danger mt-2 mb-0">{{ __('No Services') }}</p>
                                                <small class="text-muted">{{ __('Day marked in red') }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Summary -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-primary">
                        <i class="ti ti-chart-bar"></i> {{ __('Weekly Summary') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        @php
                            $totalServices = collect($servicesByDay)->sum(function($day) { return $day['services']->count(); });
                            $completedServices = collect($servicesByDay)->sum(function($day) { return $day['services']->where('status', 'completed')->count(); });
                            $pendingServices = collect($servicesByDay)->sum(function($day) { return $day['services']->where('status', 'pending')->count(); });
                            $inProgressServices = collect($servicesByDay)->sum(function($day) { return $day['services']->where('status', 'in_progress')->count(); });
                            $daysWithServices = collect($servicesByDay)->filter(function($day) { return $day['hasServices']; })->count();
                        @endphp
                        
                        <div class="col-md-2 mb-3">
                            <div class="p-3 border rounded bg-light-primary">
                                <h4 class="text-primary">{{ $totalServices }}</h4>
                                <small>{{ __('Total Services') }}</small>
                            </div>
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <div class="p-3 border rounded bg-light-success">
                                <h4 class="text-success">{{ $completedServices }}</h4>
                                <small>{{ __('Completed') }}</small>
                            </div>
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <div class="p-3 border rounded bg-light-warning">
                                <h4 class="text-warning">{{ $pendingServices }}</h4>
                                <small>{{ __('Pending') }}</small>
                            </div>
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <div class="p-3 border rounded bg-light-info">
                                <h4 class="text-info">{{ $inProgressServices }}</h4>
                                <small>{{ __('In Progress') }}</small>
                            </div>
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <div class="p-3 border rounded bg-light-success">
                                <h4 class="text-success">{{ $daysWithServices }}</h4>
                                <small>{{ __('Work Days') }}</small>
                            </div>
                        </div>
                        
                        <div class="col-md-2 mb-3">
                            <div class="p-3 border rounded bg-light-danger">
                                <h4 class="text-danger">{{ 7 - $daysWithServices }}</h4>
                                <small>{{ __('Free Days') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-primary">
                        <i class="ti ti-bolt"></i> {{ __('Quick Actions') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('operator.daily-plan') }}" class="btn btn-outline-info w-100">
                                <i class="ti ti-calendar"></i> {{ __('Daily Plan') }}
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('operator.reports') }}" class="btn btn-outline-warning w-100">
                                <i class="ti ti-report"></i> {{ __('Reports') }}
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('maintenance-request.index') }}" class="btn btn-outline-success w-100">
                                <i class="ti ti-list"></i> {{ __('All Services') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 