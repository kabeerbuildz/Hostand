@extends('layouts.app')
@section('page-title')
    {{__('Dashboard')}}
@endsection
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{route('dashboard')}}">{{__('Dashboard')}}</a>
        </li>

    </ul>
@endsection
@push('script-page')

@endpush
@php
$settings=settings();
@endphp
@section('content')
    <div class="row">
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4>{{__('Property')}}</h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class="">{{!empty($tenant->properties)?$tenant->properties->name:'-'}}</span>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4>{{__('Unit')}}</h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class="">{{!empty($tenant->units)?$tenant->units->name:'-'}}</span>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4>{{__('Rent')}}</h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class="">{{$settings['CURRENCY_SYMBOL']}}{{!empty($result['unit'])?$result['unit']->rent:'-'}}</span>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4>{{__('Total Invoice')}}</h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class="count">{{$result['totalInvoice']}}</span>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4>{{__('Total Contact')}}</h4>
                </div>
                <div class="card-body progressCounter">
                    <h2>
                        <span class="count">{{$result['totalContact']}}</span>
                    </h2>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-sm-6 cdx-xxl-50">
            <div class="card sale-revenue">
                <div class="card-header">
                    <h4>{{__('Total Notes')}}</h4>
                </div>
                <div class="card-body progressCounter">
                    <h2><span class="count">{{$result['totalNote']}}</span> </h2>
                </div>
            </div>
        </div>
    </div>

@endsection
