@extends('layouts.app')
@section('page-title')
    {{ __('Admin Property Details') }} - {{ $property->name }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.properties.index') }}">{{ __('Admin Properties') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Property Details') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ $property->name }}</h5>
                            <p class="text-muted mb-0">
                                <i class="ti ti-user"></i> <strong>{{ __('Owner') }}:</strong>
                                {{ $property->owner ? $property->owner->name : __('Unknown') }}
                                @if ($property->owner)
                                    <span class="ms-2">
                                        <i class="ti ti-mail"></i> {{ $property->owner->email }}
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.properties.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left"></i> {{ __('Back to Properties') }}
                            </a>
                            {{-- <a href="{{ route('property.show', $property->id) }}" class="btn btn-primary">
                                <i class="ti ti-external-link"></i> {{ __('Manage Property') }}
                            </a> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row property-page mt-3">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <ul class="nav nav-tabs profile-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="profile-tab-1" data-bs-toggle="tab" href="#profile-1"
                                role="tab" aria-selected="true">
                                <i class="material-icons-two-tone me-2">meeting_room</i>
                                {{ __('Property Details') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="profile-tab-2" data-bs-toggle="tab" href="#profile-2" role="tab"
                                aria-selected="false">
                                <i class="material-icons-two-tone me-2">ad_units</i>
                                {{ __('Units') }} ({{ $units->count() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="profile-tab-3" data-bs-toggle="tab" href="#profile-3" role="tab"
                                aria-selected="false">
                                <i class="material-icons-two-tone me-2">vpn_key</i>
                                {{ __('Access & Settings') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="profile-tab-4" data-bs-toggle="tab" href="#profile-4" role="tab"
                                aria-selected="false">
                                <i class="material-icons-two-tone me-2">build</i>
                                {{ __('Services') }} ({{ $maintenanceRequests->count() }})
                                @if ($maintenanceRequests->where('status', 'pending')->count() > 0)
                                    <span
                                        class="badge bg-warning ms-1">{{ $maintenanceRequests->where('status', 'pending')->count() }}</span>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Property Details Tab -->
                        <div class="tab-pane show active" id="profile-1" role="tabpanel" aria-labelledby="profile-tab-1">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="row justify-content-center">
                                        <div class="col-xl-12 col-xxl-12">
                                            <div class="card border">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-5">
                                                            <div class="sticky-md-top product-sticky">
                                                                <div id="carouselExampleCaptions"
                                                                    class="carousel slide carousel-fade"
                                                                    data-bs-ride="carousel">
                                                                    <div class="carousel-inner">
                                                                        @foreach ($property->propertyImages as $key => $image)
                                                                            @php
                                                                                $img = !empty($image->image)
                                                                                    ? $image->image
                                                                                    : 'default.jpg';
                                                                            @endphp
                                                                            <div
                                                                                class="carousel-item {{ $key === 0 ? 'active' : '' }}">
                                                                                <img src="{{ asset('uploads/property/' . $img) }}"
                                                                                    class="d-block w-100 rounded"
                                                                                    alt="Property image" />
                                                                            </div>
                                                                        @endforeach
                                                                    </div>

                                                                    <ol
                                                                        class="carousel-indicators position-relative product-carousel-indicators my-sm-3 mx-0">
                                                                        @foreach ($property->propertyImages as $key => $image)
                                                                            @php
                                                                                $img = !empty($image->image)
                                                                                    ? $image->image
                                                                                    : 'default.jpg';
                                                                            @endphp
                                                                            <li data-bs-target="#carouselExampleCaptions"
                                                                                data-bs-slide-to="{{ $key }}"
                                                                                class="{{ $key === 0 ? 'active' : '' }} w-25 h-auto">
                                                                                <img src="{{ asset('uploads/property/' . $img) }}"
                                                                                    class="d-block wid-50 rounded"
                                                                                    alt="Property image" />
                                                                            </li>
                                                                        @endforeach
                                                                    </ol>

                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <h3 class="">{{ ucfirst($property->name) }}</h3>
                                                            <span class="badge bg-light-primary f-14 mt-1"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-original-title="{{ __('Type') }}">{{ \App\Models\Property::$Type[$property->type] }}</span>

                                                            <!-- Owner Information -->
                                                            <h5 class="mt-4">{{ __('Owner Information') }}</h5>
                                                            <hr class="my-3" />
                                                            @if ($property->owner)
                                                                <div class="mb-1 row">
                                                                    <label
                                                                        class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                                        {{ __('Owner Name') }} :
                                                                    </label>
                                                                    <div
                                                                        class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                                        {{ $property->owner->name }}
                                                                    </div>
                                                                </div>
                                                                <div class="mb-1 row">
                                                                    <label
                                                                        class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                                        {{ __('Owner Email') }} :
                                                                    </label>
                                                                    <div
                                                                        class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                                        {{ $property->owner->email }}
                                                                    </div>
                                                                </div>
                                                                @if ($property->owner->phone_number)
                                                                    <div class="mb-1 row">
                                                                        <label
                                                                            class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                                            {{ __('Owner Phone') }} :
                                                                        </label>
                                                                        <div
                                                                            class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                                            {{ $property->owner->phone_number }}
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            @endif

                                                            <h5 class="mt-4">{{ __('Property Details') }}</h5>
                                                            <hr class="my-3" />
                                                            <p class="text-muted">{{ $property->description }}</p>

                                                            <h5>{{ __('Property Address') }}</h5>
                                                            <hr class="my-3" />
                                                            <div class="mb-1 row">
                                                                <label
                                                                    class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                                    {{ __('Address') }} :
                                                                </label>
                                                                <div
                                                                    class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                                    {{ $property->address }}
                                                                </div>
                                                            </div>
                                                            <div class="mb-1 row">
                                                                <label
                                                                    class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                                    {{ __('Location') }} :
                                                                </label>
                                                                <div
                                                                    class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                                    {{ $property->city . ', ' . $property->state . ', ' . $property->country }}
                                                                </div>
                                                            </div>
                                                            <div class="mb-1 row">
                                                                <label
                                                                    class="col-form-label col-lg-3 col-sm-12 text-lg-end">
                                                                    {{ __('Zip Code') }} :
                                                                </label>
                                                                <div
                                                                    class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-center">
                                                                    {{ $property->zip_code }}
                                                                </div>
                                                            </div>
                                                            <hr class="my-3" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Units Tab -->
                        <div class="tab-pane" id="profile-2" role="tabpanel" aria-labelledby="profile-tab-2">
                            <div class="row">
                                @foreach ($units as $unit)
                                    <div class="col-xxl-3 col-xl-4 col-md-6">
                                        <div class="card follower-card">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-start mb-3">
                                                    <div class="flex-grow-1">
                                                        <h2 class="mb-1 text-truncate">{{ ucfirst($unit->name) }}</h2>
                                                    </div>
                                                </div>
                                                <hr class="my-3" />
                                                <div class="row">
                                                    <p class="mb-1">{{ __('Bedroom') }} :
                                                        <span class="text-muted">{{ $unit->bedroom }}</span>
                                                    </p>
                                                    <p class="mb-1">
                                                        {{ __('Kitchen') }} :
                                                        <span class="text-muted">
                                                            {{ __(\Illuminate\Support\Str::title($unit->kitchen)) }}
                                                        </span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Bath') }} :
                                                        <span class="text-muted">{{ $unit->baths }}</span>
                                                    </p>
                                                </div>
                                                <hr class="my-2" />
                                                <p class="my-3 text-muted text-sm">{{ $unit->notes }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Access & Settings Tab -->
                        <div class="tab-pane" id="profile-3" role="tabpanel" aria-labelledby="profile-tab-3">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="card border">
                                        <div class="card-header">
                                            <h5 class="mb-0">{{ __('Access Information & Property Settings') }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <!-- Access Codes Section -->
                                                <div class="col-md-6">
                                                    <h6 class="text-primary mb-3">{{ __('Access Codes') }}</h6>
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label"><strong>{{ __('Street Opening Code') }}:</strong></label>
                                                        <p class="text-muted">
                                                            {{ $property->street_code ?? __('Not specified') }}</p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label"><strong>{{ __('Door Opening Code') }}:</strong></label>
                                                        <p class="text-muted">
                                                            {{ $property->door_code ?? __('Not specified') }}</p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label"><strong>{{ __('Key Description') }}:</strong></label>
                                                        <p class="text-muted">
                                                            {{ $property->key_description ?? __('Not specified') }}</p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label"><strong>{{ __('Other Access Information') }}:</strong></label>
                                                        <p class="text-muted">
                                                            {{ $property->access_other ?? __('Not specified') }}</p>
                                                    </div>
                                                </div>

                                                <!-- Property Settings Section -->
                                                <div class="col-md-6">
                                                    <h6 class="text-primary mb-3">{{ __('Property Settings') }}</h6>
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label"><strong>{{ __('Piano') }}:</strong></label>
                                                        <p class="text-muted">
                                                            {{ $property->piano ?? __('Not specified') }}</p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label"><strong>{{ __('Staircase') }}:</strong></label>
                                                        <p class="text-muted">
                                                            {{ $property->staircase ?? __('Not specified') }}</p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label"><strong>{{ __('Opening Type') }}:</strong></label>
                                                        <p class="text-muted">
                                                            {{ $property->opening_type ?? __('Not specified') }}</p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label"><strong>{{ __('Sign Details') }}:</strong></label>
                                                        <p class="text-muted">
                                                            {{ $property->sign_detail ?? __('Not specified') }}</p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label
                                                            class="form-label"><strong>{{ __('Sofa Bed') }}:</strong></label>
                                                        <p class="text-muted">
                                                            {{ $property->sofa_bed ?? __('Not specified') }}</p>
                                                    </div>
                                                    @if ($property->bnb_unit_type || $property->bnb_unit_count)
                                                        <div class="mb-3">
                                                            <label
                                                                class="form-label"><strong>{{ __('BnB Unit Information') }}:</strong></label>
                                                            <p class="text-muted">
                                                                {{ __('Type') }}:
                                                                {{ $property->bnb_unit_type ?? __('N/A') }}<br>
                                                                {{ __('Count') }}:
                                                                {{ $property->bnb_unit_count ?? __('N/A') }}
                                                            </p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Services & Requests Tab -->
                        <div class="tab-pane" id="profile-4" role="tabpanel" aria-labelledby="profile-tab-4">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="card border">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">{{ __('Services & Maintenance Requests') }}</h5>
                                        </div>
                                        <div class="card-body">
                                            @if ($maintenanceRequests->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>{{ __('Date') }}</th>
                                                                <th>{{ __('Service Type') }}</th>
                                                                <th>{{ __('Unit') }}</th>
                                                                <th>{{ __('Maintainer') }}</th>
                                                                <th>{{ __('Status') }}</th>
                                                                <th>{{ __('Amount') }}</th>
                                                                <th>{{ __('Actions') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($maintenanceRequests as $request)
                                                                <tr>
                                                                    <td>{{ \Carbon\Carbon::parse($request->request_date)->format('M d, Y') }}
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-light-info">
                                                                            {{ $request->types->title ?? __('N/A') }}
                                                                        </span>
                                                                    </td>
                                                                    <td>{{ $request->units->name ?? __('N/A') }}</td>
                                                                    <td>{{ $request->maintainers->name ?? __('Unassigned') }}
                                                                    </td>
                                                                    <td>
                                                                        @if ($request->status == 'pending')
                                                                            <span
                                                                                class="badge bg-light-warning">{{ __('Pending') }}</span>
                                                                        @elseif($request->status == 'in_progress')
                                                                            <span
                                                                                class="badge bg-light-info">{{ __('In Progress') }}</span>
                                                                        @elseif($request->status == 'completed')
                                                                            <span
                                                                                class="badge bg-light-success">{{ __('Completed') }}</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if ($request->amount)
                                                                            {{ priceFormat($request->amount) }}
                                                                        @else
                                                                            <span
                                                                                class="text-muted">{{ __('N/A') }}</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        <a class="btn btn-sm btn-light customModal"
                                                                            data-size="lg" data-bs-toggle="tooltip"
                                                                            data-bs-original-title="{{ __('View Details') }}"
                                                                            href="#"
                                                                            data-url="{{ route('maintenance-request.show', $request->id) }}"
                                                                            data-title="{{ __('Maintenance Request Details') }}">
                                                                            <i class="ti ti-eye"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                                {{-- @if ($request->notes)
                                                                <tr>
                                                                    <td colspan="7" class="bg-light">
                                                                        <small><strong>{{ __('Notes') }}:</strong> {{ $request->notes }}</small>
                                                                    </td>
                                                                </tr>
                                                                @endif --}}
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <!-- Summary Statistics -->
                                                <div class="row mt-4">
                                                    <div class="col-md-3">
                                                        <div class="card bg-light-primary">
                                                            <div class="card-body text-center">
                                                                <h4 class="text-primary">
                                                                    {{ $maintenanceRequests->count() }}</h4>
                                                                <p class="mb-0">{{ __('Total Requests') }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="card bg-light-warning">
                                                            <div class="card-body text-center">
                                                                <h4 class="text-warning">
                                                                    {{ $maintenanceRequests->where('status', 'pending')->count() }}
                                                                </h4>
                                                                <p class="mb-0">{{ __('Pending') }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="card bg-light-info">
                                                            <div class="card-body text-center">
                                                                <h4 class="text-info">
                                                                    {{ $maintenanceRequests->where('status', 'in_progress')->count() }}
                                                                </h4>
                                                                <p class="mb-0">{{ __('In Progress') }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="card bg-light-success">
                                                            <div class="card-body text-center">
                                                                <h4 class="text-success">
                                                                    {{ $maintenanceRequests->where('status', 'completed')->count() }}
                                                                </h4>
                                                                <p class="mb-0">{{ __('Completed') }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-center py-5">
                                                    <i class="ti ti-building-warehouse"
                                                        style="font-size: 3rem; color: #dee2e6;"></i>
                                                    <h5 class="mt-3 text-muted">{{ __('No Service Requests') }}</h5>
                                                    <p class="text-muted">
                                                        {{ __('No maintenance requests have been made for this property yet.') }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Request Details Modal -->
    <div class="modal fade" id="maintenanceRequestModal" tabindex="-1" role="dialog"
        aria-labelledby="maintenanceRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="maintenanceRequestModalLabel">{{ __('Maintenance Request Details') }}a
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="maintenanceRequestModalBody">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(document).ready(function() {
                // Handle custom modal for maintenance request details
                $('.customModal').on('click', function(e) {
                    e.preventDefault();

                    var url = $(this).data('url');
                    var title = $(this).data('title');
                    var size = $(this).data('size') || 'lg';

                    // Update modal title
                    $('#maintenanceRequestModalLabel').text(title);

                    // Show loading in modal body
                    $('#maintenanceRequestModalBody').html(
                        '<div class="text-center"><i class="ti ti-loader ti-spin" style="font-size: 2rem;"></i><p class="mt-2">{{ __('Loading...') }}</p></div>'
                        );

                    // Show modal
                    $('#maintenanceRequestModal').modal('show');

                    // Load content via AJAX
                    $.ajax({
                        url: url,
                        type: 'GET',
                        success: function(response) {
                            $('#maintenanceRequestModalBody').html(response);
                        },
                        error: function(xhr, status, error) {
                            $('#maintenanceRequestModalBody').html(
                                '<div class="text-center text-danger"><i class="ti ti-alert-circle" style="font-size: 2rem;"></i><p class="mt-2">{{ __('Error loading content') }}</p></div>'
                                );
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
