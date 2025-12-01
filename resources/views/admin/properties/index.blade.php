@extends('layouts.app')
@section('page-title')
    {{ __('Admin Properties Overview') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Admin Properties') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ __('All Properties Overview') }}</h5>
                            <p class="text-muted mb-0">
                                {{ __('Comprehensive view of all properties across all organizations') }}</p>
                        </div>
                        <div class="col-auto">
                            <a class="btn btn-primary" href="{{ route('admin.properties.analytics') }}">
                                <i class="ti ti-chart-bar"></i> {{ __('Analytics') }}
                            </a>
                            <a class="btn btn-info" href="{{ route('admin.properties.services') }}">
                                <i class="ti ti-build"></i> {{ __('All Services') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistics Summary -->
                <div class="row mt-3 mb-4">
                    <div class="col-md-3">
                        <div class="card bg-light-primary">
                            <div class="card-body text-center">
                                <h4 class="text-primary">{{ $properties->count() }}</h4>
                                <p class="mb-0">{{ __('Total Properties') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-warning">
                            <div class="card-body text-center">
                                <h4 class="text-warning">{{ $properties->sum(function ($p) {return $p->totalUnit();}) }}
                                </h4>
                                <p class="mb-0">{{ __('Total Units') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-info">
                            <div class="card-body text-center">
                                <h4 class="text-info">
                                    {{ $properties->sum(function ($p) {return $p->maintenanceRequests->count();}) }}</h4>
                                <p class="mb-0">{{ __('Total Services') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-light-success">
                            <div class="card-body text-center">
                                <h4 class="text-success">{{ $properties->groupBy('parent_id')->count() }}</h4>
                                <p class="mb-0">{{ __('Property Owners') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    @foreach ($properties as $property)
                        @if (!empty($property->thumbnail) && !empty($property->thumbnail->image))
                            @php $thumbnail= asset('uploads/thumbnail/' . $property->thumbnail->image); @endphp
                        @else
                            @php $thumbnail= asset('uploads/whiteImage/download1.jpeg'); @endphp
                        @endif
                        <div class="col-sm-6 col-md-4 col-xxl-3">
                            <div class="card product-card">
                                <div class="card-img-top">
                                    <img src="{{ $thumbnail }}" alt="{{ $property->name }}" class="img-prod" />
                                </div>

                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <a href="{{ route('admin.properties.show', $property->id) }}"
                                            class="fw-semibold mb-0 text-truncate">
                                            <h4>{{ $property->name }}</h4>
                                        </a>
                                        <div class="dropdown">
                                            <a class="dropdown-toggle text-primary opacity-50 arrow-none" href="#"
                                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="ti ti-dots f-16"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item"
                                                    href="{{ route('admin.properties.show', $property->id) }}">
                                                    <i class="material-icons-two-tone">remove_red_eye</i>
                                                    {{ __('View Details') }}
                                                </a>
                                                {{-- <a class="dropdown-item"
                                                    href="{{ route('property.show', $property->id) }}">
                                                    <i class="material-icons-two-tone">edit</i>
                                                    {{ __('Manage Property') }}
                                                </a> --}}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Owner Information -->
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="ti ti-user"></i>
                                            <strong>{{ __('Owner') }}:</strong>
                                            {{ $property->owner ? $property->owner->name : __('Unknown') }}
                                        </small>
                                    </div>

                                    <!-- Property Stats -->
                                    <div class="col-md-12">
                                        <button type="button" class="btn btn-sm my-1 btn-light-secondary">
                                            <i class="material-icons-two-tone">ad_units</i> {{ $property->totalUnit() }}
                                            {{ __('Units') }}
                                        </button>
                                        <button type="button" class="btn btn-sm my-1 btn-light-secondary">
                                            <i class="material-icons-two-tone">meeting_room</i>
                                            {{ $property->totalRoom() }}
                                            {{ __('Rooms') }}
                                        </button>
                                        @php
                                            $maintenanceCount = $property->maintenanceRequests->count();
                                            $pendingCount = $property->maintenanceRequests
                                                ->where('status', 'pending')
                                                ->count();
                                        @endphp
                                        @if ($maintenanceCount > 0)
                                            <button type="button" class="btn btn-sm my-1 btn-light-info">
                                                <i class="material-icons-two-tone">build</i> {{ $maintenanceCount }}
                                                {{ __('Services') }}
                                                @if ($pendingCount > 0)
                                                    <span class="badge bg-warning ms-1">{{ $pendingCount }}</span>
                                                @endif
                                            </button>
                                        @endif
                                    </div>

                                    <!-- Address -->
                                    <div class="mt-2 mb-2">
                                        <small class="text-muted">
                                            <i class="ti ti-map-pin"></i>
                                            {{ $property->address ?? __('No address') }}
                                            @if ($property->city)
                                                <br>{{ $property->city }}, {{ $property->state }},
                                                {{ $property->country }}
                                            @endif
                                        </small>
                                    </div>

                                    <!-- Access Codes Alert -->
                                    @if ($property->street_code || $property->door_code)
                                        <div class="alert alert-info py-2 px-2 mb-2">
                                            <small>
                                                <i class="ti ti-key"></i>
                                                <strong>{{ __('Access Codes Available') }}</strong>
                                            </small>
                                        </div>
                                    @endif

                                    <p class="prod-content my-2 text-muted small">
                                        {{ substr($property->description, 0, 100) }}{{ strlen($property->description) > 100 ? '...' : '' }}
                                    </p>

                                    <div class="d-flex align-items-center justify-content-between mt-3">
                                        <span class="badge bg-light-secondary" data-bs-toggle="tooltip"
                                            data-bs-original-title="{{ __('Type') }}">
                                            {{ $property->locationType->title ?? 'Unknown' }}
                                        </span>


                                        @if ($pendingCount > 0)
                                            <span class="badge bg-warning">{{ $pendingCount }} {{ __('Pending') }}</span>
                                        @endif
                                    </div>

                                    <div class="mt-3">
                                        <a href="{{ route('admin.properties.show', $property->id) }}"
                                            class="btn btn-primary btn-sm w-100">
                                            <i class="ti ti-eye"></i> {{ __('View Full Details') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
