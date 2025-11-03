@extends('layouts.app')

@section('page-title')
    {{ __('Maintenance Request') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Maintenance Request') }}</li>
@endsection

@section('content')
    <style>
        .table-bordered td,
        .table-bordered th {
            border: 2px solid #1E90FF;
            /* blue border */
        }

        .table-bordered {
            border-collapse: collapse;
        }
    </style>

    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ __('Maintenance Request List') }}</h5>
                        </div>
                        {{-- @if (Gate::check('create user'))
                            <div class="col-auto">
                                <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                    data-url="{{ route('users.create') }}" data-title="{{ __('Create User') }}"> <i
                                        class="ti ti-circle-plus align-text-bottom"></i> {{ __('Create User') }}</a>
                            </div>
                        @endif --}}
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Property') }}</th>
                                    <th>{{ __('Unit') }}</th>
                                    <th>{{ __('Issue') }}</th>
                                    <th>{{ __('Maintainer') }}</th>
                                    <th>{{ __('Request Date') }}</th>
                                    <th>{{ __('Attachment') }}</th>
                                    @if (Gate::check('edit maintenance request') ||
                                            Gate::check('delete maintenance request') ||
                                            Gate::check('show maintenance request'))
                                        <th class="text-right">{{ __('Action') }}</th>
                                    @endif
                                </tr>

                            </thead>
                            <tbody>
                                @foreach ($maintenanceRequests as $request)
                                    <tr>
                                        <td>
                                            <a href="#" class="text-primary property-details-link"
                                                data-bs-toggle="modal" data-bs-target="#propertyModal{{ $request->id }}"
                                                data-property-id="{{ $request->property_id }}" data-bs-toggle="tooltip"
                                                data-bs-original-title="{{ __('View Property Details') }}">
                                                {{ !empty($request->properties) ? $request->properties->name : '-' }}
                                            </a>
                                        </td>
                                        <td> {{ !empty($request->units) ? $request->units->name : '-' }} </td>
                                        <td> {{ !empty($request->types) ? $request->types->title : '-' }} </td>
                                        <td> {{ !empty($request->maintainers) ? $request->maintainers->name : '-' }} </td>
                                        <td> {{ dateFormat($request->request_date) }} </td>
                                        <td>
                                            @if (!empty($request->issue_attachment))
                                                <a href="{{ asset(Storage::url('upload/issue_attachment')) . '/' . $request->issue_attachment }}"
                                                    download="download"><i data-feather="download"></i></a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        @if (Gate::check('edit maintenance request') ||
                                                Gate::check('delete maintenance request') ||
                                                Gate::check('show maintenance request'))
                                            <td>
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['maintenance-request.destroy', $request->id]]) !!}
                                                    @can('show maintenance request')
                                                        <a class="text-warning" data-bs-toggle="modal"
                                                            data-bs-target="#propertyModal{{ $request->id }}"
                                                            title="{{ __('View Property Details') }}"> <i
                                                                data-feather="eye"></i></a>
                                                    @endcan
                                                    @can('edit maintenance request')
                                                        <a class="text-secondary customModal" data-bs-toggle="tooltip"
                                                            data-size="lg" data-bs-original-title="{{ __('Edit') }}"
                                                            href="#"
                                                            data-url="{{ route('maintenance-request.edit', $request->id) }}"
                                                            data-title="{{ __('Edit Maintenance Request') }}"> <i
                                                                data-feather="edit"></i></a>
                                                    @endcan
                                                    @can('delete maintenance request')
                                                        <a class=" text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Detete') }}" href="#"> <i
                                                                data-feather="trash-2"></i></a>
                                                    @endcan
                                                    @if (\Auth::user()->type == 'maintainer')
                                                        <a class="text-secondary customModal" data-bs-toggle="tooltip"
                                                            data-size="lg"
                                                            data-bs-original-title="{{ __('Status Update') }}"
                                                            href="#"
                                                            data-url="{{ route('maintenance-request.action', $request->id) }}"
                                                            data-title="{{ __('Maintenance Request Status') }}"> <i
                                                                data-feather="check-square"></i></a>
                                                    @endif

                                                    {!! Form::close() !!}
                                                </div>

                                            </td>
                                        @endif
                                    </tr>

                                    <!-- Property Details Modal -->
                                    <div class="modal fade" id="propertyModal{{ $request->id }}" tabindex="-1"
                                        aria-labelledby="propertyModalLabel{{ $request->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="propertyModalLabel{{ $request->id }}">
                                                        {{ __('Property Details') }} -
                                                        {{ !empty($request->properties) ? $request->properties->name : 'N/A' }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    @if (!empty($request->properties) && $request->properties->exists)
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6 class="text-primary mb-3">{{ __('Basic Information') }}
                                                                </h6>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('Property Name') }}:</strong>
                                                                    <span
                                                                        class="ms-2">{{ $request->properties->name }}</span>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('Property Type') }}:</strong>
                                                                    <span
                                                                        class="ms-2">{{ !empty($request->properties->type) ? \App\Models\Property::$Type[$request->properties->type] : 'N/A' }}</span>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('Description') }}:</strong>
                                                                    <span
                                                                        class="ms-2">{{ $request->properties->description ?? 'N/A' }}</span>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('Status') }}:</strong>
                                                                    <span class="ms-2">
                                                                        @if ($request->properties->is_active == 1)
                                                                            <span
                                                                                class="badge bg-success">{{ __('Active') }}</span>
                                                                        @else
                                                                            <span
                                                                                class="badge bg-danger">{{ __('Inactive') }}</span>
                                                                        @endif
                                                                    </span>
                                                                </div>

                                                                <h6 class="text-primary mb-3 mt-4">
                                                                    {{ __('Location Details') }}</h6>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('Address') }}:</strong>
                                                                    <span
                                                                        class="ms-2">{{ $request->properties->address ?? 'N/A' }}</span>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('City') }}:</strong>
                                                                    <span
                                                                        class="ms-2">{{ $request->properties->city ?? 'N/A' }}</span>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('State') }}:</strong>
                                                                    <span
                                                                        class="ms-2">{{ $request->properties->state ?? 'N/A' }}</span>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('Country') }}:</strong>
                                                                    <span
                                                                        class="ms-2">{{ $request->properties->country ?? 'N/A' }}</span>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('Zip Code') }}:</strong>
                                                                    <span
                                                                        class="ms-2">{{ $request->properties->zip_code ?? 'N/A' }}</span>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <h6 class="text-primary mb-3">
                                                                    {{ __('Access Information') }}</h6>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('Piano') }}:</strong>
                                                                    <span class="ms-2">
                                                                        @if ($request->properties->piano)
                                                                            {{ $request->properties->piano }}
                                                                        @else
                                                                            {{ __('N/A') }}
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('Staircase') }}:</strong>
                                                                    <span class="ms-2">
                                                                        @if ($request->properties->staircase)
                                                                            {{ $request->properties->staircase }}
                                                                        @else
                                                                            {{ __('N/A') }}
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('Other Access') }}:</strong>
                                                                    <span class="ms-2">
                                                                        @if ($request->properties->access_other)
                                                                            {{ $request->properties->access_other }}
                                                                        @else
                                                                            {{ __('N/A') }}
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('Sign Detail') }}:</strong>
                                                                    <span class="ms-2">
                                                                        @if ($request->properties->sign_detail)
                                                                            {{ $request->properties->sign_detail }}
                                                                        @else
                                                                            {{ __('N/A') }}
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('Opening Type') }}:</strong>
                                                                    <span class="ms-2">
                                                                        @if ($request->properties->opening_type)
                                                                            {{ ucfirst($request->properties->opening_type) }}
                                                                        @else
                                                                            {{ __('N/A') }}
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('Street Code') }}:</strong>
                                                                    <span class="ms-2">
                                                                        @if ($request->properties->street_code)
                                                                            {{ $request->properties->street_code }}
                                                                        @else
                                                                            {{ __('N/A') }}
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('Door Code') }}:</strong>
                                                                    <span class="ms-2">
                                                                        @if ($request->properties->door_code)
                                                                            {{ $request->properties->door_code }}
                                                                        @else
                                                                            {{ __('N/A') }}
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('Key Description') }}:</strong>
                                                                    <span class="ms-2">
                                                                        @if ($request->properties->key_description)
                                                                            {{ $request->properties->key_description }}
                                                                        @else
                                                                            {{ __('N/A') }}
                                                                        @endif
                                                                    </span>
                                                                </div>

                                                                <h6 class="text-primary mb-3 mt-4">
                                                                    {{ __('Unit Information') }}</h6>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('Sofa Bed') }}:</strong>
                                                                    <span class="ms-2">
                                                                        @if ($request->properties->sofa_bed)
                                                                            {{ ucfirst($request->properties->sofa_bed) }}
                                                                        @else
                                                                            {{ __('N/A') }}
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('B&B Unit Type') }}:</strong>
                                                                    <span class="ms-2">
                                                                        @if ($request->properties->bnb_unit_type)
                                                                            {{ ucfirst($request->properties->bnb_unit_type) }}
                                                                        @else
                                                                            {{ __('N/A') }}
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('B&B Unit Count') }}:</strong>
                                                                    <span class="ms-2">
                                                                        @if ($request->properties->bnb_unit_count)
                                                                            {{ $request->properties->bnb_unit_count }}
                                                                        @else
                                                                            {{ __('N/A') }}
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('Total Units') }}:</strong>
                                                                    <span
                                                                        class="ms-2">{{ $request->properties->totalUnit() }}</span>
                                                                </div>
                                                                <div class="mb-2">
                                                                    <strong>{{ __('Total Rooms') }}:</strong>
                                                                    <span
                                                                        class="ms-2">{{ $request->properties->totalRoom() }}</span>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        @if ($request->properties->propertyImages && $request->properties->propertyImages->count() > 0)
                                                            <div class="row mt-4">
                                                                <div class="col-12">
                                                                    <h6 class="text-primary mb-3">
                                                                        {{ __('Property Images') }}</h6>
                                                                    <div class="row">
                                                                        @foreach ($request->properties->propertyImages as $image)
                                                                            <div class="col-md-3 mb-3">
                                                                                <img src="{{ asset('uploads/property/' . $image->image) }}"
                                                                                    class="img-fluid rounded"
                                                                                    alt="Property Image">
                                                                            </div>
                                                                        @endforeach

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if (!empty($request->units))
                                                            <div class="row mt-4">
                                                                <div class="col-12">
                                                                    <h6 class="text-primary mb-3">{{ __('Unit Details') }}
                                                                    </h6>
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="mb-2">
                                                                                <strong>{{ __('Unit Name') }}:</strong>
                                                                                <span
                                                                                    class="ms-2">{{ $request->units->name }}</span>
                                                                            </div>
                                                                            <div class="mb-2">
                                                                                <strong>{{ __('Bedrooms') }}:</strong>
                                                                                <span
                                                                                    class="ms-2">{{ $request->units->bedroom }}</span>
                                                                            </div>
                                                                            <div class="mb-2">
                                                                                <strong>{{ __('Bathrooms') }}:</strong>
                                                                                <span
                                                                                    class="ms-2">{{ $request->units->baths }}</span>
                                                                            </div>
                                                                            <div class="mb-2">
                                                                                <strong>{{ __('Kitchen') }}:</strong>
                                                                                <span
                                                                                    class="ms-2">{{ $request->units->kitchen }}</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="mb-2">
                                                                                <strong>{{ __('Double Beds') }}:</strong>
                                                                                <span
                                                                                    class="ms-2">{{ $request->units->double_beds }}</span>
                                                                            </div>
                                                                            <div class="mb-2">
                                                                                <strong>{{ __('Single Beds') }}:</strong>
                                                                                <span
                                                                                    class="ms-2">{{ $request->units->single_beds }}</span>
                                                                            </div>
                                                                            <div class="mb-2">
                                                                                <strong>{{ __('Sofa Beds') }}:</strong>
                                                                                <span
                                                                                    class="ms-2">{{ $request->units->sofa_beds }}</span>
                                                                            </div>
                                                                            <div class="mb-2">
                                                                                <strong>{{ __('Notes') }}:</strong>
                                                                                <span
                                                                                    class="ms-2">{{ $request->units->notes ?? 'N/A' }}</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @else
                                                        <div class="alert alert-warning">
                                                            {{ __('No property information available.') }}
                                                            @if (config('app.debug'))
                                                                <br><strong>Debug:</strong> Property ID:
                                                                {{ $request->property_id }},
                                                                Properties Object:
                                                                {{ $request->properties ? 'Exists' : 'Null' }}
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">{{ __('Close') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
