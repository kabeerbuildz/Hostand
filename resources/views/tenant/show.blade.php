@extends('layouts.app')
@section('page-title')
    {{ __('Tenant Details') }}
@endsection
@section('page-class')
    cdxuser-profile
@endsection
@push('script-page')
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
    </li>
    <li class="breadcrumb-item" aria-current="page"><a href="{{ route('tenant.index') }}"> {{ __('Tenant') }}</a></li>
    <li class="breadcrumb-item active">
        <a href="#">{{ __('Details') }}</a>
    </li>
@endsection



@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 col-xxl-3">
                            <div class="card border">
                                <div class="card-header">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <img class="img-radius img-fluid wid-80"
                                                src="{{ !empty($tenant->user) && !empty($tenant->user->profile) ? asset(Storage::url('upload/profile/' . $tenant->user->profile)) : asset(Storage::url('upload/profile/avatar.png')) }}"
                                                alt="User image" />
                                        </div>
                                        <div class="flex-grow-1 mx-3">
                                            <h5 class="mb-1">
                                                {{ ucfirst(!empty($tenant->user) ? $tenant->user->first_name : '') . ' ' . ucfirst(!empty($tenant->user) ? $tenant->user->last_name : '') }}
                                            </h5>
                                            <h6 class="mb-0 text-secondary">{!! $tenant->LeaseLeftDay() !!}</h6>
                                        </div>

                                    </div>
                                </div>
                                <div class="card-body px-2 pb-0">
                                    <div class="list-group list-group-flush">
                                        <a href="#" class="list-group-item list-group-item-action">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="material-icons-two-tone f-20">email</i>
                                                </div>
                                                <div class="flex-grow-1 mx-3">
                                                    <h5 class="m-0">{{ __('Email') }}</h5>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <small>{{ !empty($tenant->user) ? $tenant->user->email : '-' }}</small>
                                                </div>
                                            </div>
                                        </a>
                                        <a href="#" class="list-group-item list-group-item-action">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="material-icons-two-tone f-20">phonelink_ring</i>
                                                </div>
                                                <div class="flex-grow-1 mx-3">
                                                    <h5 class="m-0">{{ __('Phone') }}</h5>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <small>{{ !empty($tenant->user) ? $tenant->user->phone_number : '-' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </a>


                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="col-lg-8 col-xxl-9">
                            <div class="card border">
                                <div class="card-header">
                                    <h5>{{ __('Additional Information') }}</h5>
                                </div>
                                <div class="card-body">
                                    {{-- <p class="mb-4">
                                        Hello,I’m Anshan Handgun Creative Graphic Designer & User Experience
                                        Designer based in Website, I create
                                        digital Products a more Beautiful and usable place. Morbid accusant ipsum.
                                        Nam nec tellus at.
                                    </p> --}}
                                    {{-- <h5>Personal Details</h5>
                                    <hr class="my-3" /> --}}
                                    <div class="table-responsive">
                                        <table class="table table-borderless">
                                            <tbody>
                                                <tr>
                                                    <td><b class="text-header">{{ __('Total Family Member') }}</b></td>
                                                    <td>:</td>
                                                    <td>{{ !empty($tenant->family_member) ? $tenant->family_member : '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><b class="text-header">{{ __('Country') }}</b></td>
                                                    <td>:</td>
                                                    <td>{{ !empty($tenant->country) ? $tenant->country : '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><b class="text-header">{{ __('State') }}</b></td>
                                                    <td>:</td>
                                                    <td>{{ !empty($tenant->state) ? $tenant->state : '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><b class="text-header">{{ __('City') }}</b></td>
                                                    <td>:</td>
                                                    <td>{{ !empty($tenant->city) ? $tenant->city : '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><b class="text-header">{{ __('Zip Code') }}</b></td>
                                                    <td>:</td>
                                                    <td>{{ !empty($tenant->zip_code) ? $tenant->zip_code :'-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><b class="text-header">{{ __('Property') }}</b></td>
                                                    <td>:</td>
                                                    <td>{{ !empty($tenant->properties) ? $tenant->properties->name : '-' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><b class="text-header">{{ __('Unit') }}</b></td>
                                                    <td>:</td>
                                                    <td>{{ !empty($tenant->units) ? $tenant->units->name : '-' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><b class="text-header">{{ __('Lease Start Date') }}</b></td>
                                                    <td>:</td>
                                                    <td>{{ dateFormat($tenant->lease_start_date) }}</td>
                                                </tr>
                                                <tr>
                                                    <td><b class="text-header">{{ __('Lease End Date') }}</b></td>
                                                    <td>:</td>
                                                    <td>{{ dateFormat($tenant->lease_end_date) }}</td>
                                                </tr>
                                                @if (!empty($tenant->documents))
                                                    <tr>
                                                        <td><b class="text-header">{{ __('Documents') }}</b></td>
                                                        <td>:</td>
                                                        <td>
                                                            @foreach ($tenant->documents as $doc)
                                                                <a href="{{ asset(Storage::url('upload/tenant')) . '/' . $doc->document }}"
                                                                    target="_blank"><i data-feather="download"></i></a>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td><b class="text-header">{{ __('Address') }}</b></td>
                                                    <td>:</td>
                                                    <td>{{ !empty($tenant->address) ? $tenant->address : '-' }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
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
