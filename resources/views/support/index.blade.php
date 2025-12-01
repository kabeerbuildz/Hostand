@extends('layouts.app')
@section('page-title')
    {{__('Tickets')}}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{route('dashboard')}}">{{__('Dashboard')}}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{__('Tickets')}}</a>
        </li>
    </ul>
@endsection
@section('card-action-btn')

@endsection
@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ __('Tickets') }}</h5>
                        </div>
                        @if(\Auth::user()->type == 'owner' || \Auth::user()->type == 'tenant')
                        <div class="col-auto">
                            <a class="btn btn-secondary customModal" href="#" data-size="md"
                               data-url="{{ route('support.create') }}"
                               data-title="{{__('Create New Ticket')}}"> <i class="ti ti-circle-plus align-text-bottom"></i>{{__('Create Ticket')}}</a>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                        <thead>
                        <tr>
                            <th>{{__('Subject')}}</th>
                            <th>{{__('Assigned To')}}</th>
                            <th>{{__('Created Date')}}</th>
                            <th>{{__('Created By')}}</th>
                            <th>{{__('Service')}}</th>
                            <th>{{__('Priority')}}</th>
                            <th>{{__('Status')}}</th>
                            <th class="text-right">{{__('Action')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($supports as $support)
                            <tr role="row">
                                <td>
                                    <a href="{{ route('support.show',$support->id) }}"
                                       class="text-body">{{$support->subject}}</a>
                                </td>
                                <td>
                                    {{ !empty($support->assignUser)?$support->assignUser->name:__('All') }}
                                </td>
                                <td>
                                    {{dateFormat($support->created_at)}}
                                </td>
                                <td>
                                    {{ !empty($support->createdUser)?$support->createdUser->name:'-' }}
                                </td>
                                <td>
                                    @if(!empty($support->maintenanceRequest))
                                        @php
                                            $propertyName = !empty($support->maintenanceRequest->properties) ? $support->maintenanceRequest->properties->name : '-';
                                            $serviceType = !empty($support->maintenanceRequest->types) ? $support->maintenanceRequest->types->title : '-';
                                        @endphp
                                        {{ $propertyName }}
                                        @if($serviceType != '-')
                                            <br><small class="text-muted">{{ $serviceType }}</small>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($support->priority=='low')
                                        <span
                                            class="badge bg-success px-2 py-1 fw-medium">{{\App\Models\Support::$priority[$support->priority]}}</span>
                                    @elseif($support->priority=='medium')
                                        <span
                                            class="badge bg-info px-2 py-1 fw-medium">{{\App\Models\Support::$priority[$support->priority]}}</span>
                                    @elseif($support->priority=='high')
                                        <span
                                            class="badge bg-warning px-2 py-1 fw-medium">{{\App\Models\Support::$priority[$support->priority]}}</span>
                                    @elseif($support->priority=='critical')
                                        <span
                                            class="badge bg-danger px-2 py-1 fw-medium">{{\App\Models\Support::$priority[$support->priority]}}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($support->status=='pending')
                                        <span
                                            class="badge bg-primary px-2 py-1 fw-medium">{{\App\Models\Support::$status[$support->status]}}</span>
                                    @elseif($support->status=='open')
                                        <span
                                            class="badge bg-info px-2 py-1 fw-medium">{{\App\Models\Support::$status[$support->status]}}</span>
                                    @elseif($support->status=='close')
                                        <span
                                            class="badge bg-danger px-2 py-1 fw-medium">{{\App\Models\Support::$status[$support->status]}}</span>
                                    @elseif($support->status=='on_hold')
                                        <span
                                            class="badge bg-warning px-2 py-1 fw-medium">{{\App\Models\Support::$status[$support->status]}}</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <div class="cart-action">
                                        @if(\Auth::user()->type == 'super admin' || \Auth::user()->type == 'admin' || \Auth::user()->type == 'maintainer' || \Auth::user()->type == 'owner')
                                            {{-- <a class="text-secondary view-ticket-modal" data-bs-toggle="tooltip"
                                               data-bs-original-title="{{__('View Details')}}" href="#"
                                               data-ticket-id="{{ $support->id }}"> <i data-feather="eye"></i></a> --}}
                                            <a class="text-secondary" data-bs-toggle="tooltip"
                                               data-bs-original-title="{{__('View Details')}}"
                                               href="{{ route('support.show',$support->id) }}" style="margin-left: 5px;"> <i
                                                    data-feather="eye"></i></a>
                                        @else
                                            <a class="text-secondary" data-bs-toggle="tooltip"
                                               data-bs-original-title="{{__('View Details')}}"
                                               href="{{ route('support.show',$support->id) }}"> <i
                                                    data-feather="eye"></i></a>
                                        @endif

                                        @if(\Auth::user()->type == 'super admin' || \Auth::user()->type == 'admin' || $support->created_id == \Auth::user()->id)
                                            <a class="text-secondary customModal" data-bs-toggle="tooltip"
                                               data-bs-original-title="{{__('Edit')}}" href="#"
                                               data-url="{{ route('support.edit',$support->id) }}"
                                               data-title="{{__('Edit Ticket')}}"> <i data-feather="edit"></i></a>
                                        @endif

                                        @if(\Auth::user()->type == 'super admin' || \Auth::user()->type == 'admin' || $support->created_id == \Auth::user()->id)
                                            {!! Form::open(['method' => 'DELETE', 'route' => ['support.destroy', $support->id], 'style' => 'display: inline;']) !!}
                                            <a class="text-danger confirm_dialog" data-bs-toggle="tooltip"
                                               data-bs-original-title="{{__('Delete')}}" href="#"> <i
                                                    data-feather="trash-2"></i></a>
                                            {!! Form::close() !!}
                                        @endif
                                    </div>
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
    
    @include('support.modal')
@endsection

