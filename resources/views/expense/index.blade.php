@extends('layouts.app')
@section('page-title')
    {{ __('Expense') }}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Expense') }}</a>
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
                            <h5>{{ __('Expense List') }}</h5>
                        </div>
                        @if (auth()->user()->type == 'super admin')
                            @if (Gate::check('create expense'))
                                <div class="col-auto">
                                    <a class="btn btn-secondary customModal" href="#" data-size="lg"
                                        data-url="{{ route('expense.create') }}" data-title="{{ __('Create Expense') }}"> <i
                                            class="ti ti-circle-plus align-text-bottom"></i>{{ __('Create Expense') }}</a>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover advance-datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('Expense') }}</th>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Property') }}</th>
                                    <th>{{ __('Unit') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Receipt') }}</th>
                                    @if (Gate::check('edit expense') || Gate::check('delete expense') || Gate::check('show expense'))
                                        <th class="text-right">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($expenses as $expense)
                                    <tr role="row">
                                        <td>{{ expensePrefix() . $expense->expense_id }} </td>
                                        <td> {{ $expense->title }} </td>
                                        <td> {{ !empty($expense->properties) ? $expense->properties->name : '-' }} </td>
                                        <td> {{ !empty($expense->units) ? $expense->units->name : '-' }} </td>
                                        <td> {{ !empty($expense->types) ? $expense->types->title : '-' }} </td>
                                        <td> {{ dateFormat($expense->date) }} </td>
                                        <td> {{ priceFormat($expense->amount) }} </td>
                                        <td>
                                            @if (!empty($expense->receipt))
                                                <a href="{{ asset(Storage::url('upload/receipt')) . '/' . $expense->receipt }}"
                                                    download="download"><i data-feather="download"></i></a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        @if (Gate::check('edit expense') || Gate::check('delete expense') || Gate::check('show expense'))
                                            <td class="text-right">
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['expense.destroy', $expense->id]]) !!}
                                                    @can('show expense')
                                                        <a class="text-warning customModal" data-size="lg"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('View') }}" href="#"
                                                            data-url="{{ route('expense.show', $expense->id) }}"
                                                            data-title="{{ __('Expense Details') }}"> <i
                                                                data-feather="eye"></i></a>
                                                    @endcan
                                                    @can('edit expense')
                                                        <a class="text-secondary customModal" data-size="lg"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Edit') }}" href="#"
                                                            data-url="{{ route('expense.edit', $expense->id) }}"
                                                            data-title="{{ __('Edit Expense') }}"> <i
                                                                data-feather="edit"></i></a>
                                                    @endcan
                                                    @can('delete expense')
                                                        <a class=" text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                            data-bs-original-title="{{ __('Detete') }}" href="#"> <i
                                                                data-feather="trash-2"></i></a>
                                                    @endcan
                                                    {!! Form::close() !!}
                                                </div>

                                            </td>
                                        @endif
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
