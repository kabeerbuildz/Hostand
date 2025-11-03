@extends('layouts.app')
@section('page-title')
    {{ __('Reports') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('operator.dashboard') }}">{{ __('Operator Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Reports') }}</li>
@endsection

@section('content')
    <!-- Header Section -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-gradient-success">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class=" mb-1">{{ __('Performance Reports') }}</h3>
                            <p class="text-dark-50 mb-0">
                                <i class="ti ti-report"></i> {{ __('Work performance and statistics for') }} {{ $operator->first_name }} {{ $operator->last_name }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            {{-- <a href="{{ route('operator.dashboard') }}" class="btn btn-light me-2">
                                <i class="ti ti-arrow-left"></i> {{ __('Back to Dashboard') }}
                            </a> --}}
                            <a href="{{ route('operator.reports') }}" class="btn btn-light">
                                <i class="ti ti-calendar"></i> {{ __('Report') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-primary">
                        <i class="ti ti-filter"></i> {{ __('Filter by Date Range') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('operator.reports') }}" class="row">
                        <div class="col-md-4 mb-3">
                            <label for="start_date" class="form-label">{{ __('Start Date') }}</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="{{ $startDate }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="end_date" class="form-label">{{ __('End Date') }}</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="{{ $endDate }}" required>
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="ti ti-search"></i> {{ __('Filter') }}
                            </button>
                            <a href="{{ route('operator.reports') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-refresh"></i> {{ __('Reset') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Statistics -->
    <div class="row mt-4">
        <div class="col-lg-2 col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-primary bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-list text-primary f-24"></i>
                    </div>
                    <h4 class="text-primary mb-1">{{ $totalAllServices }}</h4>
                    <p class="text-muted mb-0">{{ __('All Services') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-success bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-check-circle text-success f-24"></i>
                    </div>
                    <h4 class="text-success mb-1">{{ $totalServices }}</h4>
                    <p class="text-muted mb-0">{{ __('Completed') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-warning bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-clock text-warning f-24"></i>
                    </div>
                    <h4 class="text-warning mb-1">{{ $pendingServices }}</h4>
                    <p class="text-muted mb-0">{{ __('Pending') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-info bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-tools text-info f-24"></i>
                    </div>
                    <h4 class="text-info mb-1">{{ $inProgressServices }}</h4>
                    <p class="text-muted mb-0">{{ __('In Progress') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-success bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-clock text-success f-24"></i>
                    </div>
                    <h4 class="text-success mb-1">{{ number_format($totalHours, 1) }}</h4>
                    <p class="text-muted mb-0">{{ __('Total Hours') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-info bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-currency-dollar text-info f-24"></i>
                    </div>
                    <h4 class="text-info mb-1">{{ number_format($totalAmount, 2) }}</h4>
                    <p class="text-muted mb-0">{{ __('Total Amount') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- All Services List -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-primary">
                        <i class="ti ti-list"></i> {{ __('All Services') }} ({{ $startDate }} to {{ $endDate }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($allServices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Property') }}</th>
                                        <th>{{ __('Unit') }}</th>
                                        <th>{{ __('Service') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Hours') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allServices as $service)
                                        <tr>
                                            <td>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($service->request_date)->format('M d, Y') }}
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
                                                <span class="text-info">
                                                    ${{ number_format($service->amount ?? 0, 2) }}
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
                            <i class="ti ti-report-off" style="font-size: 3rem; color: #dee2e6;"></i>
                            <h5 class="mt-3 text-muted">{{ __('No Services Found') }}</h5>
                            <p class="text-muted">{{ __('No services found in the selected date range.') }}</p>
                            
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-primary">
                        <i class="ti ti-chart-pie"></i> {{ __('Performance Breakdown') }}
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Services by Property -->
                    <h6 class="text-secondary mb-3">{{ __('Services by Property') }}</h6>
                    @if($servicesByProperty->count() > 0)
                        @foreach($servicesByProperty->take(5) as $propertyId => $services)
                            @php
                                $property = $services->first()->properties ?? null;
                                $propertyHours = $services->sum('hours_worked') ?? 0;
                                $propertyAmount = $services->sum('amount') ?? 0;
                            @endphp
                            @if($property)
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                    <div>
                                        <small class="text-muted">{{ $property->name }}</small>
                                        <br>
                                        <small class="text-success">{{ $services->count() }} {{ __('services') }}</small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-info">{{ number_format($propertyHours, 1) }}h</small>
                                        <br>
                                        <small class="text-warning">${{ number_format($propertyAmount, 2) }}</small>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @else
                        <p class="text-muted small">{{ __('No data available.') }}</p>
                    @endif

                    <hr>

                    <!-- Weekly Performance -->
                    <h6 class="text-secondary mb-3">{{ __('Weekly Performance') }}</h6>
                    @if($servicesByWeek->count() > 0)
                        @foreach($servicesByWeek->take(4) as $weekKey => $services)
                            @php
                                $weekStart = \Carbon\Carbon::parse($weekKey);
                                $weekHours = $services->sum('hours_worked') ?? 0;
                                $weekAmount = $services->sum('amount') ?? 0;
                            @endphp
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                <div>
                                    <small class="text-muted">{{ __('Week of') }} {{ $weekStart->format('M d') }}</small>
                                    <br>
                                    <small class="text-success">{{ $services->count() }} {{ __('services') }}</small>
                                </div>
                                <div class="text-end">
                                    <small class="text-info">{{ number_format($weekHours, 1) }}h</small>
                                    <br>
                                    <small class="text-warning">${{ number_format($weekAmount, 2) }}</small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted small">{{ __('No weekly data available.') }}</p>
                    @endif

                    <hr>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Charts -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-primary">
                        <i class="ti ti-chart-bar"></i> {{ __('Performance Trends') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h6 class="text-secondary mb-3">{{ __('Hours Worked Trend') }}</h6>
                            <div class="p-3 border rounded bg-light">
                                <canvas id="hoursChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h6 class="text-secondary mb-3">{{ __('Services Completed Trend') }}</h6>
                            <div class="p-3 border rounded bg-light">
                                <canvas id="servicesChart" width="400" height="200"></canvas>
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
                            <a href="{{ route('operator.weekly-plan') }}" class="btn btn-outline-warning w-100">
                                <i class="ti ti-calendar-week"></i> {{ __('Weekly Plan') }}
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

@push('script-page')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Only create charts if we have data
    @if($servicesByWeek->count() > 0)
        // Hours Worked Chart
        const hoursCtx = document.getElementById('hoursChart');
        if (hoursCtx) {
            new Chart(hoursCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: @json($servicesByWeek->keys()->take(4)->map(function($week) {
                        return \Carbon\Carbon::parse($week)->format('M d');
                    })),
                    datasets: [{
                        label: '{{ __("Hours Worked") }}',
                        data: @json($servicesByWeek->take(4)->map(function($services) {
                            return $services->sum('hours_worked') ?? 0;
                        })),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Services Completed Chart
        const servicesCtx = document.getElementById('servicesChart');
        if (servicesCtx) {
            new Chart(servicesCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: @json($servicesByWeek->keys()->take(4)->map(function($week) {
                        return \Carbon\Carbon::parse($week)->format('M d');
                    })),
                    datasets: [{
                        label: '{{ __("Services Completed") }}',
                        data: @json($servicesByWeek->take(4)->map(function($services) {
                            return $services->count();
                        })),
                        backgroundColor: '#17a2b8',
                        borderColor: '#17a2b8',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    @endif
});
</script>
@endpush 