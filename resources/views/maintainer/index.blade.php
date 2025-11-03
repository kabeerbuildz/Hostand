{{-- @extends('layouts.app')
@section('page-title')
    {{ __('Maintainer') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Maintainer') }}</li>
@endsection


@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ __('Maintainer List') }}</h5>
                        </div>
                        @can('create maintainer')
                            <div class="col-auto">
                                <a class="btn btn-secondary customModal" href="#" data-url="{{ route('maintainer.create') }}"
                                    data-title="{{ __('Create Maintainer') }}" data-size="md"> <i
                                        class="ti ti-circle-plus align-text-bottom "></i>
                                    {{ __('Create Maintainer') }}</a>
                            </div>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        @foreach ($maintainers as $maintainer)
                            <div class="col-xxl-3 col-xl-4 col-md-6">
                                <div class="card follower-card">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="flex-grow-1 ms-3 mx-2">
                                                <img class="img-fluid wid-70"
                                                    src="{{ !empty($maintainer->user) && !empty($maintainer->user->profile) ? asset(Storage::url('upload/profile/' . $maintainer->user->profile)) : asset(Storage::url('upload/profile/avatar.png')) }}"
                                                    alt="">
                                            </div>
                                            @if (Gate::check('edit maintainer') || Gate::check('delete maintainer') || Gate::check('show maintainer'))
                                                <div class="flex-shrink-0">
                                                    <div class="dropdown">
                                                        <a class="dropdown-toggle text-primary opacity-50 arrow-none" href="#"
                                                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="ti ti-dots f-16"></i>
                                                        </a>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            @can('edit maintainer')
                                                                <a class="dropdown-item customModal" href="#"
                                                                    data-url="{{ route('maintainer.edit', $maintainer->id) }}"
                                                                    data-title="{{ __('Edit Maintainer') }}">
                                                                    <i class="material-icons-two-tone">edit</i>
                                                                    {{ __('Edit Maintainer') }}
                                                                </a>
                                                            @endcan
                                                            @can('delete maintainer')
                                                                                                    {!! Form::open([
                                                                    'method' => 'DELETE',
                                                                    'route' => ['maintainer.destroy', $maintainer->id],
                                                                    'id' => 'maintainer-' . $maintainer->id,
                                                                ]) !!}
                                                                                                    <a class="dropdown-item confirm_dialog" href="#">
                                                                                                        <i class="material-icons-two-tone">delete</i>
                                                                                                        {{ __('Delete Maintainer') }}
                                                                                                    </a>
                                                                                                    {!! Form::close() !!}
                                                            @endcan
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <a class="customModal" href="#"
                                            data-url="{{ route('maintainer.edit', $maintainer->id) }}"
                                            data-title="{{ __('Edit Maintainer') }}">
                                            <h4>{{ ucfirst(!empty($maintainer->user) ? $maintainer->user->first_name : '') . ' ' . ucfirst(!empty($maintainer->user) ? $maintainer->user->last_name : '') }}
                                            </h4>

                                        </a>
                                        <h6 class="text-truncate text-muted d-flex align-items-center mb-2">
                                            {{ !empty($maintainer->user) ? $maintainer->user->email : '-' }}
                                        </h6>

                                        <div class="row">
                                          
                                            @if (!empty($maintainer->user) && !empty($maintainer->user->phone_number))
                                                <div class="col-sm-6 mb-3">
                                                    <p class="mb-0 text-muted text-sm">{{ __('Phone') }} :</p>
                                                    <h6 class="mb-0">
                                                        {{ $maintainer->user->phone_number }}
                                                    </h6>
                                                </div>
                                            @else
                                                <div class="col-sm-6 mb-3">
                                                    <p class="mb-0 text-muted text-sm">{{ __('Phone') }} :</p>
                                                    <h6 class="mb-0">-</h6>
                                                </div>
                                            @endif

                                            <div class="col-sm-6 mb-3">
                                                <p class="mb-0 text-muted text-sm">{{ __('Type') }} :</p>
                                                <h6 class="mb-0">
                                                    {{ !empty($maintainer->types) ? $maintainer->types->title : '-' }}
                                                </h6>
                                            </div>
                                            <div class="col-sm-6 mb-3">
                                                <p class="mb-0 text-muted text-sm">{{ __('Created Date') }} :</p>
                                                <h6 class="mb-0">
                                                    {{ dateFormat($maintainer->created_at) }}
                                                </h6>
                                            </div>
                                            <div class="col-sm-12 mb-3">
                                                <span>{{ __('Property') }} :</span>

                                                @if (($maintainer->properties()))
                                                        <ul>

                                                            @foreach ($maintainer->properties() as $property)
                                                                @if($property)
                                                                    <li>{{ $property->name }}</li>
                                                                @endif
                                                            @endforeach

                                                        </ul>
                                                    </div>
                                                @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection --}}

@extends('layouts.app')
@section('page-title')
    {{ __('Maintainer') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Maintainer') }}</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center m-4">
    <h4>{{ __('Maintainer List') }}</h4>
    @can('create maintainer')
        <a class="btn btn-secondary customModal" href="#" 
            data-url="{{ route('maintainer.create') }}"
            data-title="{{ __('Create Maintainer') }}" data-size="md">
            <i class="ti ti-circle-plus align-text-bottom"></i> {{ __('Create Maintainer') }}
        </a>
    @endcan
</div>

<div class="row" style="display:flex; flex-wrap:wrap; gap:1.5rem;">
    @foreach ($maintainers as $maintainer)
        <div style="flex:0 0 23%; max-width:23%;">
            <div class="card follower-card" style="
                height:420px;
                border-radius:12px;
                box-shadow:0 4px 15px rgba(0,0,0,0.08);
                transition: transform 0.3s, box-shadow 0.3s;
                display:flex;
                flex-direction:column;
                cursor:pointer;
            " onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(0,0,0,0.08)';">
                
                <div class="card-body p-3" style="flex:1 1 auto; overflow:auto;">
                    <div class="d-flex align-items-start mb-3">
                        <div class="flex-grow-1 mx-2">
                            <img class="img-fluid" 
                                src="{{ !empty($maintainer->user) && !empty($maintainer->user->profile) ? asset(Storage::url('upload/profile/' . $maintainer->user->profile)) : asset(Storage::url('upload/profile/avatar.png')) }}" 
                                alt="" 
                                style="width:70px; height:70px; border-radius:50%; object-fit:cover; border:2px solid #e0e0e0;">
                        </div>

                        @if (Gate::check('edit maintainer') || Gate::check('delete maintainer') || Gate::check('show maintainer'))
                        <div class="flex-shrink-0">
                            <div class="dropdown">
                                <a class="dropdown-toggle text-primary opacity-50 arrow-none" href="#" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots f-16"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    @can('edit maintainer')
                                    <a class="dropdown-item customModal" href="#" data-url="{{ route('maintainer.edit', $maintainer->id) }}" data-title="{{ __('Edit Maintainer') }}">
                                        <i class="material-icons-two-tone">edit</i> {{ __('Edit Maintainer') }}
                                    </a>
                                    @endcan
                                    @can('delete maintainer')
                                    {!! Form::open(['method' => 'DELETE', 'route' => ['maintainer.destroy', $maintainer->id], 'id' => 'maintainer-' . $maintainer->id]) !!}
                                    <a class="dropdown-item confirm_dialog" href="#">
                                        <i class="material-icons-two-tone">delete</i> {{ __('Delete Maintainer') }}
                                    </a>
                                    {!! Form::close() !!}
                                    @endcan
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <a class="customModal" href="#" data-url="{{ route('maintainer.edit', $maintainer->id) }}" data-title="{{ __('Edit Maintainer') }}">
                        <h5 style="margin:0 0 5px 0; font-weight:600;">
                            {{ ucfirst(!empty($maintainer->user) ? $maintainer->user->first_name : '') . ' ' . ucfirst(!empty($maintainer->user) ? $maintainer->user->last_name : '') }}
                        </h5>
                    </a>
                    <p style="margin:0 0 10px 0; color:#6c757d; font-size:0.9rem;">{{ !empty($maintainer->user) ? $maintainer->user->email : '-' }}</p>

                    <div class="row" style="font-size:0.9rem; gap:0.5rem;">
                        <div class="col-sm-6 mb-2">
                            <p class="mb-0 text-muted">{{ __('Phone') }} :</p>
                            <h6 class="mb-0">{{ !empty($maintainer->user) && !empty($maintainer->user->phone_number) ? $maintainer->user->phone_number : '-' }}</h6>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <p class="mb-0 text-muted">{{ __('Type') }} :</p>
                            <h6 class="mb-0">{{ !empty($maintainer->types) ? $maintainer->types->title : '-' }}</h6>
                        </div>
                        <div class="col-sm-6 mb-2">
                            <p class="mb-0 text-muted">{{ __('Created Date') }} :</p>
                            <h6 class="mb-0">{{ dateFormat($maintainer->created_at) }}</h6>
                        </div>
                        @if ($maintainer->properties())
                        <div class="col-sm-12 mb-2" style="max-height:70px; overflow-y:auto;">
                            <span>{{ __('Property') }} :</span>
                            <ul style="padding-left:1rem; margin:0; list-style-type:disc;">
                                @foreach ($maintainer->properties() as $property)
                                    @if($property)
                                        <li>{{ $property->name }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
