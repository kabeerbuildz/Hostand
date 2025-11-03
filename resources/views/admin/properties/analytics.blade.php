@extends('layouts.app')
@section('page-title')
    {{ __('Properties & Services Analytics') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.properties.index') }}">{{ __('Admin Properties') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Analytics') }}</li>
@endsection

@push('script-page')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Service Status Chart
    const serviceCtx = document.getElementById('serviceStatusChart').getContext('2d');
    new Chart(serviceCtx, {
        type: 'doughnut',
        data: {
            labels: ['{{ __('Completed') }}', '{{ __('In Progress') }}', '{{ __('Pending') }}'],
            datasets: [{
                data: [{{ $completedRequests }}, {{ $inProgressRequests }}, {{ $pendingRequests }}],
                backgroundColor: ['#28a745', '#17a2b8', '#ffc107'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Services by Month Chart
    const monthlyCtx = document.getElementById('monthlyServicesChart').getContext('2d');
    const monthlyData = @json($servicesByMonth);
    const labels = monthlyData.map(item => {
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return monthNames[item.month - 1] + ' ' + item.year;
    }).reverse();
    const data = monthlyData.map(item => item.count).reverse();

    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: '{{ __('Services') }}',
                data: data,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
@endpush

@section('content')
    <!-- Header Section -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="text-white mb-1">{{ __('Properties & Services Analytics') }}</h3>
                            <p class="text-white-50 mb-0">
                                <i class="ti ti-chart-bar"></i> {{ __('Comprehensive insights across all properties and services') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('admin.properties.index') }}" class="btn btn-light">
                                <i class="ti ti-arrow-left"></i> {{ __('Back to Properties') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row mt-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar bg-primary bg-opacity-10">
                                <i class="ti ti-building text-primary f-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">{{ __('Total Properties') }}</h6>
                            <h3 class="mb-0 text-primary">{{ number_format($totalProperties) }}</h3>
                            <small class="text-success">
                                <i class="ti ti-trending-up"></i> {{ __('Active Properties') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar bg-warning bg-opacity-10">
                                <i class="ti ti-3d-cube-sphere text-warning f-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">{{ __('Total Units') }}</h6>
                            <h3 class="mb-0 text-warning">{{ number_format($totalUnits) }}</h3>
                            <small class="text-info">
                                <i class="ti ti-chart-line"></i> {{ __('Across All Properties') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar bg-info bg-opacity-10">
                                <i class="ti ti-tools text-info f-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">{{ __('Total Services') }}</h6>
                            <h3 class="mb-0 text-info">{{ number_format($totalMaintenanceRequests) }}</h3>
                            <small class="text-primary">
                                <i class="ti ti-clock"></i> {{ __('Maintenance Requests') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar bg-success bg-opacity-10">
                                <i class="ti ti-users text-success f-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">{{ __('Property Owners') }}</h6>
                            <h3 class="mb-0 text-success">{{ number_format($propertiesByOwner->count()) }}</h3>
                            <small class="text-secondary">
                                <i class="ti ti-user-check"></i> {{ __('Active Owners') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Status Overview -->
    <div class="row mb-4">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-primary">
                        <i class="ti ti-check-circle"></i> {{ __('Completed Services') }}
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-success mb-0">{{ number_format($completedRequests) }}</h2>
                    <p class="text-muted mb-0">{{ __('Successfully completed') }}</p>
                    @if($totalMaintenanceRequests > 0)
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ ($completedRequests / $totalMaintenanceRequests) * 100 }}%"></div>
                        </div>
                        <small class="text-muted">{{ round(($completedRequests / $totalMaintenanceRequests) * 100, 1) }}% {{ __('completion rate') }}</small>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-info">
                        <i class="ti ti-clock"></i> {{ __('In Progress') }}
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-info mb-0">{{ number_format($inProgressRequests) }}</h2>
                    <p class="text-muted mb-0">{{ __('Currently being worked on') }}</p>
                    @if($totalMaintenanceRequests > 0)
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: {{ ($inProgressRequests / $totalMaintenanceRequests) * 100 }}%"></div>
                        </div>
                        <small class="text-muted">{{ round(($inProgressRequests / $totalMaintenanceRequests) * 100, 1) }}% {{ __('of total services') }}</small>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-warning">
                        <i class="ti ti-alert-circle"></i> {{ __('Pending Services') }}
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-warning mb-0">{{ number_format($pendingRequests) }}</h2>
                    <p class="text-muted mb-0">{{ __('Awaiting attention') }}</p>
                    @if($totalMaintenanceRequests > 0)
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: {{ ($pendingRequests / $totalMaintenanceRequests) * 100 }}%"></div>
                        </div>
                        <small class="text-muted">{{ round(($pendingRequests / $totalMaintenanceRequests) * 100, 1) }}% {{ __('require attention') }}</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-primary">
                        <i class="ti ti-chart-line"></i> {{ __('Services Trend (Last 12 Months)') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="monthlyServicesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-primary">
                        <i class="ti ti-chart-pie"></i> {{ __('Service Status Distribution') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="serviceStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Properties by Owner -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-primary">
                        <i class="ti ti-users"></i> {{ __('Properties by Owner') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Owner') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Properties') }}</th>
                                    <th>{{ __('Total Units') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($propertiesByOwner as $ownerId => $data)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avtar bg-primary bg-opacity-10 me-3">
                                                    <i class="ti ti-user text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $data['owner']->name ?? __('Unknown') }}</h6>
                                                    @if($data['owner'] && $data['owner']->phone_number)
                                                        <small class="text-muted">{{ $data['owner']->phone_number }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $data['owner']->email ?? __('N/A') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $data['properties_count'] }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $totalUnits = $data['properties']->sum(function($p) { return $p->totalUnit(); });
                                            @endphp
                                            <span class="badge bg-info">{{ $totalUnits }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" id="property-list">
    @foreach($data['properties']->take(3) as $property)
        <a href="{{ route('admin.properties.show', $property->id) }}" 
           class="btn btn-outline-primary btn-sm" 
           title="{{ $property->name }}">
            {{ substr($property->name, 0, 10) }}{{ strlen($property->name) > 10 ? '...' : '' }}
        </a>
    @endforeach

    {{-- Hidden extra properties --}}
    @foreach($data['properties']->skip(3) as $property)
        <a href="{{ route('admin.properties.show', $property->id) }}" 
           class="btn btn-outline-primary btn-sm d-none extra-property" 
           title="{{ $property->name }}">
            {{ substr($property->name, 0, 10) }}{{ strlen($property->name) > 10 ? '...' : '' }}
        </a>
    @endforeach

    @if($data['properties_count'] > 3)
        <button type="button" class="btn btn-light btn-sm" id="show-more">
            +{{ $data['properties_count'] - 3 }}
        </button>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('show-more');
    if (btn) {
        btn.addEventListener('click', function() {
            // Show all hidden properties
            document.querySelectorAll('.extra-property').forEach(el => el.classList.remove('d-none'));
            // Hide the "+X" button
            btn.style.display = 'none';
        });
    }
});
</script>

                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                            <a href="{{ route('admin.properties.index') }}" class="btn btn-outline-primary w-100">
                                <i class="ti ti-buildings"></i> {{ __('View All Properties') }}
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.properties.services') }}" class="btn btn-outline-info w-100">
                                <i class="ti ti-tools"></i> {{ __('View All Services') }}
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('maintenance-request.index') }}" class="btn btn-outline-warning w-100">
                                <i class="ti ti-calendar"></i> {{ __('Services Calendar') }}
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('users.index') }}" class="btn btn-outline-success w-100">
                                <i class="ti ti-users"></i> {{ __('Manage Customers') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection