@extends('layouts.app')
@section('page-title')
    {{ __('Property Details') }}
@endsection
@section('page-class')
    product-detail-page
@endsection
@push('script-page')
@endpush


@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">
        <a href="{{ route('property.index') }}">{{ __('Property') }}</a>
    </li>
    <li class="breadcrumb-item active">
        <a href="#">{{ __('Details') }}</a>
    </li>
@endsection



@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">

                        </div>
                        @can('create property')
                            <div class="col-auto">
                                <a class="btn btn-secondary" href="{{ route('property.create') }}" data-size="md"> <i
                                        class="ti ti-circle-plus align-text-bottom "></i>
                                    {{ __('Add Unit') }}</a>

                                {{-- <a href="#" class="btn btn-secondary btn-sm customModal" data-title="{{ __('Add Unit') }}"
                                    data-url="{{ route('unit.create', $property->id) }}" data-size="lg"> <i
                                        class="ti-plus mr-5"></i>{{ __('Add Unit') }}</a> --}}
                            </div>
                        @endcan
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
                                {{ __('Property Units') }}
                            </a>
                        </li>
                        {{-- <li class="nav-item">
                            <a class="nav-link" id="profile-tab-3" data-bs-toggle="tab" href="#profile-3" role="tab"
                                aria-selected="false">
                                <i class="material-icons-two-tone me-2">vpn_key</i>
                                {{ __('Access & Settings') }}
                            </a>
                        </li> --}}
                        <li class="nav-item">
                            <a class="nav-link" id="profile-tab-4" data-bs-toggle="tab" href="#profile-4" role="tab"
                                aria-selected="false">
                                <i class="material-icons-two-tone me-2">build</i>
                                {{ __('Services & Requests') }}
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
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
                                                                                    alt="Product image" />
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
                                                                                    alt="Product image" />
                                                                            </li>
                                                                        @endforeach
                                                                    </ol>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        <div class="col-md-7">

                                                            <h3 class="">
                                                                {{ ucfirst($property->name) }}

                                                            </h3>
                                                            <span class="badge bg-light-primary f-14 mt-1"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-original-title="{{ __('Type') }}">{{ \App\Models\Property::$Type[$property->type] }}</span>
                                                            <h5 class="mt-4">{{ __('Property Details') }}</h5>
                                                            <hr class="my-3" />
                                                            <p class="text-muted">
                                                                {{ $property->description }}
                                                            </p>

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
                        <div class="tab-pane " id="profile-2" role="tabpanel" aria-labelledby="profile-tab-2">
                            <div class="row">
                                @foreach ($units as $unit)
                                    <div class="col-xxl-3 col-xl-4 col-md-6">
                                        <div class="card follower-card">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-start mb-3">
                                                    <div class="flex-grow-1 ">
                                                        <h2 class="mb-1 text-truncate">{{ ucfirst($unit->name) }}</h2>
                                                    </div>
                                                    <div class="flex-shrink-0">
                                                        <div class="dropdown">
                                                            <a class="dropdown-toggle text-primary opacity-50 arrow-none"
                                                                href="#" data-bs-toggle="dropdown"
                                                                aria-haspopup="true" aria-expanded="false">
                                                                <i class="ti ti-dots f-16"></i>
                                                            </a>
                                                            <div class="dropdown-menu dropdown-menu-end">

                                                                @can('edit unit')
                                                                    <a class="dropdown-item customModal" href="#"
                                                                        data-url="{{ route('unit.edit', [$property->id, $unit->id]) }}"
                                                                        data-title="{{ __('Edit Unit') }}" data-size="lg">
                                                                        <i
                                                                            class="material-icons-two-tone">edit</i>{{ __('Edit Unit') }}</a>
                                                                @endcan

                                                                @can('delete unit')
                                                                    {!! Form::open([
                                                                        'method' => 'DELETE',
                                                                        'route' => ['unit.destroy', $property->id, $unit->id],
                                                                        'id' => 'unit-' . $unit->id,
                                                                    ]) !!}

                                                                    <a class="dropdown-item confirm_dialog" href="#">
                                                                        <i class="material-icons-two-tone">delete</i>
                                                                        {{ __('Delete Unit') }}

                                                                    </a>
                                                                    {!! Form::close() !!}
                                                                @endcan
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr class="my-3" />


                                                <div class="row">
                                                    <p class="mb-1">{{ __('Bedroom') }} :
                                                        <span class="text-muted">{{ $unit->bedroom }}</span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Kitchen') }} :
                                                        <span class="text-muted">{{ $unit->kitchen }}</span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Bath') }} :
                                                        <span class="text-muted">{{ $unit->baths }}</span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Opening Type') }} :
                                                        <span class="text-muted">{{ $unit->opening_type }}</span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Street Code') }} :
                                                        <span class="text-muted">{{ $unit->street_code }}</span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Door code') }} :
                                                        <span class="text-muted">{{ $unit->street_code }}</span>
                                                    </p>
                                                    <p class="mb-1">{{ __('Access Code') }} :
                                                        <span class="text-muted">{{ $unit->access_other }}</span>
                                                    </p>
                                                    <!--<p class="mb-1">{{ __('Rent Type') }} :-->
                                                    <!--    <span class="text-muted">{{ $unit->rent_type }}</span>-->
                                                    <!--</p>-->
                                                    <!--<p class="mb-1">{{ __('Rent') }} :-->
                                                    <!--    <span class="text-muted">{{ priceFormat($unit->rent) }}</span>-->
                                                    <!--</p>-->

                                                    <!--@if ($unit->rent_type == 'custom')
    -->
                                                    <!--    <p class="mb-1">{{ __('Start Date') }} :-->
                                                    <!--        <span class="text-muted">{{ $unit->start_date }}</span>-->
                                                    <!--    </p>-->
                                                    <!--    <p class="mb-1">{{ __('End Date') }} :-->
                                                    <!--        <span class="text-muted">{{ $unit->end_date }}</span>-->
                                                    <!--    </p>-->
                                                    <!--    <p class="mb-1">{{ __('Payment Due Date') }} :-->
                                                    <!--        <span class="text-muted">{{ $unit->payment_due_date }}</span>-->
                                                    <!--    </p>-->
                                                <!--@else-->
                                                    <!--    <p class="mb-1">{{ __('Rent Duration') }} :-->
                                                    <!--        <span class="text-muted">{{ $unit->rent_duration }}</span>-->
                                                    <!--    </p>-->
                                                    <!--
    @endif-->

                                                    <!--<p class="mb-1">{{ __('Deposit Type') }} :-->
                                                    <!--    <span class="text-muted">{{ $unit->deposit_type }}</span>-->
                                                    <!--</p>-->
                                                    <!--<p class="mb-1">{{ __('Deposit Amount') }} :-->
                                                    <!--    <span class="text-muted">-->
                                                    <!--        {{ $unit->deposit_type == 'fixed' ? priceFormat($unit->deposit_amount) : $unit->deposit_amount . '%' }}-->
                                                    <!--    </span>-->
                                                    <!--</p>-->
                                                    <!--<p class="mb-1">{{ __('Late Fee Type') }} :-->
                                                    <!--    <span class="text-muted">{{ $unit->late_fee_type }}</span>-->
                                                    <!--</p>-->
                                                    <!--<p class="mb-1">{{ __('Late Fee Amount') }} :-->
                                                    <!--    <span class="text-muted">-->
                                                    <!--        {{ $unit->late_fee_type == 'fixed' ? priceFormat($unit->late_fee_amount) : $unit->late_fee_amount . '%' }}-->
                                                    <!--    </span>-->
                                                    <!--</p>-->
                                                    <!--<p class="mb-1">{{ __('Incident Receipt Amount') }} :-->
                                                    <!--    <span-->
                                                    <!--        class="text-muted">{{ priceFormat($unit->incident_receipt_amount) }}</span>-->
                                                    <!--</p>-->
                                                </div>

                                                <hr class="my-2" />
                                                <p class="my-3 text-muted text-sm">
                                                    {{ $unit->notes }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Access & Settings Tab -->
                        {{-- <div class="tab-pane" id="profile-3" role="tabpanel" aria-labelledby="profile-tab-3">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="card border">
                                        <div class="card-header">
                                            <h5 class="mb-0">{{ __('Access Information & Property Settings') }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                
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
                        </div> --}}

                        <!-- Services & Requests Tab -->
                        <div class="tab-pane" id="profile-4" role="tabpanel" aria-labelledby="profile-tab-4">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="card border">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">{{ __('Services & Maintenance Requests') }}</h5>
                                            @can('create maintenance request')
    <a href="javascript:void(0);" 
       class="btn btn-light customModal"
       data-size="lg" 
       data-title="{{ __('Create Maintenance Request') }}"
       data-url="{{ route('maintenance-request.create', ['property_id' => $property->id]) }}">
        <i class="ti ti-plus"></i> {{ __('New Request') }}
    </a>
@endcan



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
                                                                        <div class="dropdown">
                                                                            <a class="dropdown-toggle btn btn-sm btn-light"
                                                                                href="#" data-bs-toggle="dropdown">
                                                                                <i class="ti ti-dots"></i>
                                                                            </a>
                                                                            <div class="dropdown-menu">
                                                                                @can('show maintenance request')
                                                                                    <a class="dropdown-item"
                                                                                        href="{{ route('maintenance-request.show', $request->id) }}">
                                                                                        <i class="ti ti-eye"></i>
                                                                                        {{ __('View') }}
                                                                                    </a>
                                                                                @endcan
                                                                                @can('edit maintenance request')
                                                                                    <a class="dropdown-item"
                                                                                        href="{{ route('maintenance-request.edit', $request->id) }}">
                                                                                        <i class="ti ti-edit"></i>
                                                                                        {{ __('Edit') }}
                                                                                    </a>
                                                                                @endcan
                                                                                @if ($request->status !== 'completed')
                                                                                    <a class="dropdown-item"
                                                                                        href="{{ route('maintenance-request.action', $request->id) }}">
                                                                                        <i class="ti ti-settings"></i>
                                                                                        {{ __('Update Status') }}
                                                                                    </a>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                @if ($request->notes)
                                                                    <tr>
                                                                        <td colspan="7" class="bg-light">
                                                                            <small><strong>{{ __('Notes') }}:</strong>
                                                                                {{ $request->notes }}</small>
                                                                        </td>
                                                                    </tr>
                                                                @endif
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
                                                    @can('create maintenance request')
    <a href="javascript:void(0);" 
       class="btn btn-primary customModal"
       data-size="lg"
       data-title="{{ __('Create First Request') }}"
       data-url="{{ route('maintenance-request.create',['property_id' => $property->id]) }}">
        <i class="ti ti-plus"></i> {{ __('Create First Request') }}
    </a>
@endcan

                                                    
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
@endsection
