@extends('layouts.app')
@section('page-title')
    {{ __('Daily Plan') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}">{{ __('Operator Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Daily Plan') }}</li>
@endsection

@section('content')
    <!-- Header Section -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-gradient-info text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="text-white mb-1">{{ __('Daily Plan') }}</h3>
                            <p class="text-white-50 mb-0">
                                <i class="ti ti-calendar"></i> {{ __('Today\'s assigned services for') }} {{ $operator->first_name }} {{ $operator->last_name }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                                {{-- <a href="{{ route('operator.dashboard') }}" class="btn btn-light me-2">
                                    <i class="ti ti-arrow-left"></i> {{ __('Back to Dashboard')  }} --}}
                            </a>
                            <a href="{{ route('operator.daily-plan') }}" class="btn btn-light">
                                <i class="ti ti-calendar"></i> {{ __('Daily Plan') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Summary -->
    <div class="row mt-4">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-warning">
                        <i class="ti ti-clock"></i> {{ __('Pending Services') }}
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-warning mb-0">{{ $pendingServices->count() }}</h2>
                    <p class="text-muted mb-0">{{ __('Awaiting to start') }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-info">
                        <i class="ti ti-tools"></i> {{ __('In Progress') }}
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-info mb-0">{{ $inProgressServices->count() }}</h2>
                    <p class="text-muted mb-0">{{ __('Currently working') }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-success">
                        <i class="ti ti-check-circle"></i> {{ __('Completed') }}
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-success mb-0">{{ $completedServices->count() }}</h2>
                    <p class="text-muted mb-0">{{ __('Finished today') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Services by Status -->
    <div class="row">
        <!-- Pending Services -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-warning">
                        <i class="ti ti-clock"></i> {{ __('Pending Services') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($pendingServices->count() > 0)
                        @foreach($pendingServices as $service)
                            <div class="card mb-3 border-warning">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">{{ $service->properties->name ?? 'N/A' }}</h6>
                                            <small class="text-muted">{{ $service->units->name ?? 'N/A' }}</small>
                                        </div>
                                        <span class="badge bg-warning">{{ __('Pending') }}</span>
                                    </div>
                                    
                                    <p class="mb-2">
                                        <strong>{{ __('Service:') }}</strong> {{ $service->types->title ?? 'N/A' }}
                                    </p>
                                    
                                    <p class="mb-2">
                                        <strong>{{ __('Time:') }}</strong> 
                                        {{ \Carbon\Carbon::parse($service->request_date)->format('H:i') }}
                                    </p>
                                    
                                    @if($service->description)
                                        <p class="mb-2 small text-muted">{{ $service->description }}</p>
                                    @endif
                                    
                                    <div class="d-flex gap-2">
                                        <form method="POST" action="{{ route('operator.update-service-status', $service->id) }}" class="flex-grow-1">
                                            @csrf
                                            <input type="hidden" name="status" value="in_progress">
                                            <button type="submit" class="btn btn-warning btn-sm w-100">
                                                <i class="ti ti-play"></i> {{ __('Start Work') }}
                                            </button>
                                        </form>
                                        
                                        <a href="#" class="btn btn-outline-primary btn-sm customModal" 
                                           data-size="lg" data-title="{{ __('View Details') }}" 
                                           data-url="{{ route('maintenance-request.show', $service->id) }}">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-check-circle" style="font-size: 2rem; color: #28a745;"></i>
                            <p class="text-muted mt-2">{{ __('No pending services!') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- In Progress Services -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-info">
                        <i class="ti ti-tools"></i> {{ __('In Progress') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($inProgressServices->count() > 0)
                        @foreach($inProgressServices as $service)
                            <div class="card mb-3 border-info">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">{{ $service->properties->name ?? 'N/A' }}</h6>
                                            <small class="text-muted">{{ $service->units->name ?? 'N/A' }}</small>
                                        </div>
                                        <span class="badge bg-info">{{ __('In Progress') }}</span>
                                    </div>
                                    
                                    <p class="mb-2">
                                        <strong>{{ __('Service:') }}</strong> {{ $service->types->title ?? 'N/A' }}
                                    </p>
                                    
                                    <p class="mb-2">
                                        <strong>{{ __('Started:') }}</strong> 
                                        {{ \Carbon\Carbon::parse($service->request_date)->format('H:i') }}
                                    </p>
                                    
                                    @if($service->hours_worked)
                                        <p class="mb-2">
                                            <strong>{{ __('Hours Worked:') }}</strong> {{ number_format($service->hours_worked, 1) }}h
                                        </p>
                                    @endif
                                    
                                    <div class="d-flex gap-2">
                                        <form method="POST" action="{{ route('operator.update-service-status', $service->id) }}" class="flex-grow-1">
                                            @csrf
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="btn btn-success btn-sm w-100">
                                                <i class="ti ti-check"></i> {{ __('Mark Complete') }}
                                            </button>
                                        </form>
                                        
                                        <a href="#" class="btn btn-outline-primary btn-sm customModal" 
                                           data-size="lg" data-title="{{ __('View Details') }}" 
                                           data-url="{{ route('maintenance-request.show', $service->id) }}">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                    </div>
                                    
                                    <!-- Timer Section -->
                                    <div class="mt-3">
                                        @if($service->timer_started_at)
                                            <div class="alert alert-info py-2">
                                                <small>
                                                    <i class="ti ti-clock"></i> 
                                                    {{ __('Timer running since') }} {{ \Carbon\Carbon::parse($service->timer_started_at)->format('H:i') }}
                                                </small>
                                            </div>
                                            <form method="POST" action="{{ route('operator.stop-timer', $service->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-warning btn-sm">
                                                    <i class="ti ti-square"></i> {{ __('Stop Timer') }}
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('operator.start-timer', $service->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-info btn-sm">
                                                    <i class="ti ti-play"></i> {{ __('Start Timer') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-tools" style="font-size: 2rem; color: #17a2b8;"></i>
                            <p class="text-muted mt-2">{{ __('No services in progress.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Completed Services -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-success">
                        <i class="ti ti-check-circle"></i> {{ __('Completed Today') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if($completedServices->count() > 0)
                        @foreach($completedServices as $service)
                            <div class="card mb-3 border-success">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">{{ $service->properties->name ?? 'N/A' }}</h6>
                                            <small class="text-muted">{{ $service->units->name ?? 'N/A' }}</small>
                                        </div>
                                        <span class="badge bg-success">{{ __('Completed') }}</span>
                                    </div>
                                    
                                    <p class="mb-2">
                                        <strong>{{ __('Service:') }}</strong> {{ $service->types->title ?? 'N/A' }}
                                    </p>
                                    
                                    @if($service->hours_worked)
                                        <p class="mb-2">
                                            <strong>{{ __('Hours Worked:') }}</strong> 
                                            @if($service->hours_worked < 1)
                                                @php
                                                    $minutes = round($service->hours_worked * 60);
                                                @endphp
                                                {{ $minutes }} {{ __('min') }}
                                            @else
                                                {{ number_format($service->hours_worked, 1) }}h
                                            @endif
                                        </p>
                                    @endif
                                    
                                    @if($service->completed_at)
                                        <p class="mb-2">
                                            <strong>{{ __('Completed at:') }}</strong> 
                                            {{ \Carbon\Carbon::parse($service->completed_at)->format('H:i') }}
                                        </p>
                                    @endif
                                    
                                    <a href="#" class="btn btn-outline-primary btn-sm customModal" 
                                       data-size="lg" data-title="{{ __('View Details') }}" 
                                       data-url="{{ route('maintenance-request.show', $service->id) }}">
                                        <i class="ti ti-eye"></i> {{ __('View Details') }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-check-circle" style="font-size: 2rem; color: #28a745;"></i>
                            <p class="text-muted mt-2">{{ __('No completed services today.') }}</p>
                        </div>
                    @endif
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
                            <a href="{{ route('operator.weekly-plan') }}" class="btn btn-outline-info w-100">
                                <i class="ti ti-calendar-week"></i> {{ __('Weekly Plan') }}
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