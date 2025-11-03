@extends('layouts.app')
@section('page-title')
    {{ __('Operator Schedule') }} - {{ $maintainer->name }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.service-assignment.index') }}">{{ __('Service Assignment') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Operator Schedule') }}</li>
@endsection

@section('content')
    <!-- Header Section -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-gradient-info text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="text-white mb-1">{{ __('Operator Schedule') }}</h3>
                            <p class="text-white-50 mb-0">
                                <i class="ti ti-user"></i> {{ __('Weekly schedule for') }} <strong>{{ $maintainer->name }}</strong>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('admin.service-assignment.index') }}" class="btn btn-light">
                                <i class="ti ti-arrow-left"></i> {{ __('Back to Assignment') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Operator Info -->
    <div class="row mt-4">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-1">{{ $maintainer->name }}</h5>
                            <p class="text-muted mb-0">
                                <i class="ti ti-mail"></i> {{ $maintainer->email }} | 
                                <i class="ti ti-phone"></i> {{ $maintainer->phone ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-success">{{ __('Active Operator') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Schedule -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-primary">
                        <i class="ti ti-calendar-week"></i> {{ __('Weekly Schedule') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($scheduleByDay as $dateKey => $dayData)
                            <div class="col-lg-3 col-md-6 mb-4">
                                <div class="card {{ $dayData['count'] > 0 ? 'border-success' : 'border-danger' }}">
                                    <div class="card-header bg-transparent {{ $dayData['count'] > 0 ? 'border-success' : 'border-danger' }}">
                                        <h6 class="mb-0 {{ $dayData['count'] > 0 ? 'text-success' : 'text-danger' }}">
{{ $dayData['date']->isoFormat('dddd D MMMM') }}

                                            
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @if($dayData['count'] > 0)
                                            <div class="text-center mb-3">
                                                <span class="badge bg-success">{{ $dayData['count'] }} {{ __('Services') }}</span>
                                            </div>
                                            @foreach($dayData['services'] as $service)
                                                <div class="card mb-2 border-light">
                                                    <div class="card-body p-2">
                                                        <small class="text-muted">{{ $service->properties->name ?? 'N/A' }}</small>
                                                        <br>
                                                        <small class="text-primary">{{ $service->units->name ?? 'N/A' }}</small>
                                                        <br>
                                                        <span class="badge bg-light-info">{{ $service->types->title ?? 'N/A' }}</span>
                                                        <div class="mt-1">
                                                            <small class="text-muted">
                                                                {{ \Carbon\Carbon::parse($service->request_date)->format('H:i') }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="text-center py-3">
                                                <i class="ti ti-calendar-off" style="font-size: 2rem; color: #dc3545;"></i>
                                                <p class="text-danger mt-2 mb-0">{{ __('No Services') }}</p>
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
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-primary">
                        <i class="ti ti-chart-bar"></i> {{ __('Weekly Summary') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <h4 class="text-primary mb-1">{{ $weeklySchedule->count() }}</h4>
                                    <p class="text-muted mb-0">{{ __('Total Services') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <h4 class="text-success mb-1">{{ $weeklySchedule->where('status', 'completed')->count() }}</h4>
                                    <p class="text-muted mb-0">{{ __('Completed') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <h4 class="text-info mb-1">{{ $weeklySchedule->where('status', 'in_progress')->count() }}</h4>
                                    <p class="text-muted mb-0">{{ __('In Progress') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <h4 class="text-warning mb-1">{{ $weeklySchedule->where('status', 'pending')->count() }}</h4>
                                    <p class="text-muted mb-0">{{ __('Pending') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- All Services List -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-primary">
                        <i class="ti ti-list"></i> {{ __('All Services This Week') }}
                    </h6>
                </div>
                <div class="card-body">
                 
                    @if($weeklySchedule->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Property') }}</th>
                                        <th>{{ __('Unit') }}</th>
                                        <th>{{ __('Service Type') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Hours') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($weeklySchedule as $service)
                                        <tr>
                                            <td>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($service->request_date)->format('M d, Y H:i') }}
                                                </small>
                                            </td>
                                            <td>
                                                <strong>{{ $service->properties->name ?? 'N/A' }}</strong>
                                            </td>
                                            <td>{{ $service->units->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-light-info">
                                                    {{ $service->types->title ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($service->status == 'pending')
                                                    <span class="badge bg-warning">{{ __('Pending') }}</span>
                                                @elseif($service->status == 'in_progress')
                                                    <span class="badge bg-info">{{ __('In Progress') }}</span>
                                                @elseif($service->status == 'completed')
                                                    <span class="badge bg-success">{{ __('Completed') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-success">
                                                    {{ number_format($service->hours_worked ?? 0, 1) }}h
                                                </span>
                                            </td>
                                            <td>
                                                <a href="#" class="btn btn-outline-primary btn-sm customModal" 
                                                   data-size="lg" data-title="{{ __('View Service Details') }}" 
                                                   data-url="{{ route('maintenance-request.show', $service->id) }}">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-calendar-off" style="font-size: 3rem; color: #dee2e6;"></i>
                            <h5 class="mt-3 text-muted">{{ __('No Services This Week') }}</h5>
                            <p class="text-muted">{{ __('No services assigned to this operator for the current week.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection 