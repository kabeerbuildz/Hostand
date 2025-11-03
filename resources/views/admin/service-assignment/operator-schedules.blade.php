@extends('layouts.app')
@section('page-title')
    {{ __('Operator Schedules Overview') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.service-assignment.index') }}">{{ __('Service Assignment') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Operator Schedules') }}</li>
@endsection

@section('content')
    <!-- Header Section -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-gradient-info text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="text-white mb-1">{{ __('Operator Schedules Overview') }}</h3>
                            <p class="text-white-50 mb-0">
                                <i class="ti ti-calendar-week"></i> {{ __('Weekly schedules for all operators') }} ({{ $weekStart->format('M d') }} - {{ $weekEnd->format('M d, Y') }})
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

    <!-- Statistics Cards -->
    <div class="row mt-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-primary bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-users text-primary f-24"></i>
                    </div>
                    <h4 class="text-primary mb-1">{{ $maintainers->count() }}</h4>
                    <p class="text-muted mb-0">{{ __('Total Operators') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-success bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-calendar-check text-success f-24"></i>
                    </div>
                    <h4 class="text-success mb-1">{{ $maintainers->where('id', '>', 0)->count() }}</h4>
                    <p class="text-muted mb-0">{{ __('Active Schedules') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-warning bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-tools text-warning f-24"></i>
                    </div>
                    <h4 class="text-warning mb-1">{{ collect($maintainerSchedules)->sum('totalServices') }}</h4>
                    <p class="text-muted mb-0">{{ __('Total Assignments') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-info bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-calendar text-info f-24"></i>
                    </div>
                    <h4 class="text-info mb-1">{{ $weekStart->format('M d') }}</h4>
                    <p class="text-muted mb-0">{{ __('Week Starting') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Operator Schedules -->
    <div class="row">
        @foreach($maintainers as $maintainer)
            @php
                $schedule = $maintainerSchedules[$maintainer->id] ?? null;
            @endphp
            <div class="col-lg-6 col-xl-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-primary">
                                <i class="ti ti-user"></i> {{ $maintainer->name }}
                            </h6>
                            <span class="badge bg-primary">{{ $schedule['totalServices'] ?? 0 }} {{ __('Services') }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($schedule)
                            <div class="row">
                                {{-- @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $index => $dayName) --}}
                                @foreach([
    __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat'), __('Sun')
] as $index => $dayName)

                                    @php
                                        $date = $weekStart->copy()->addDays($index);
                                        $dayKey = $date->format('Y-m-d');
                                        $dayData = $schedule['scheduleByDay'][$dayKey] ?? null;
                                        $serviceCount = $dayData['count'] ?? 0;
                                    @endphp
                                    <div class="col-12 mb-2">
                                        <div class="d-flex justify-content-between align-items-center p-2 {{ $serviceCount > 0 ? 'bg-light-success' : 'bg-light-secondary' }} rounded">
                                            <div>
                                                <small class="text-muted">{{ $dayName }}</small>
                                                <br>
                                                <small class="text-muted">{{ $date->format('M d') }}</small>
                                            </div>
                                            <div class="text-end">
                                                @if($serviceCount > 0)
                                                    <span class="badge bg-success">{{ $serviceCount }} {{ __('Services') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('No Services') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            <!-- Debug: Show all services for this maintainer -->
                            @if($schedule['totalServices'] > 0)
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <strong>All Services:</strong><br>
                                        @foreach($schedule['scheduleByDay'] as $dayKey => $dayData)
                                            @if($dayData['count'] > 0)
                                                {{ $dayKey }}: {{ $dayData['count'] }} services<br>
                                            @endif
                                        @endforeach
                                    </small>
                                </div>
                            @endif
                        @else
                            <div class="text-center py-3">
                                <i class="ti ti-calendar-off" style="font-size: 2rem; color: #dee2e6;"></i>
                                <p class="text-muted mt-2 mb-0">{{ __('No assignments this week') }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer bg-transparent border-0">
                                                 <a href="{{ route('admin.service-assignment.maintainer-schedule-detail', $maintainer->id) }}" 
                            class="btn btn-outline-primary btn-sm w-100">
                            <i class="ti ti-eye"></i> {{ __('View Detailed Schedule') }}
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($maintainers->count() == 0)
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="ti ti-users-off" style="font-size: 3rem; color: #6c757d;"></i>
                        <h5 class="mt-3 text-muted">{{ __('No Operators Found') }}</h5>
                        <p class="text-muted">{{ __('No maintainers are available in the system.') }}</p>
                        <a href="{{ route('users.index') }}" class="btn btn-primary">
                            <i class="ti ti-user-plus"></i> {{ __('Add Operators') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection 