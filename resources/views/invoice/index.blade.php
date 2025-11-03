@extends('layouts.app')

@section('page-title')
    {{ __('Invoice') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Invoice') }}</li>
@endsection

@section('content')
    <!-- Modal -->
    <div class="modal fade" id="sendInvoiceModal" tabindex="-1" aria-labelledby="sendInvoiceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{route('invoice.sendMail')}}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sendInvoiceModalLabel">{{__('send_invoice')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Invoice form fields here -->

                        <!-- Select User Dropdown -->
                        <div class="mb-3 position-relative">
                            <label for="email_search" class="form-label">{{__('search_user_by_email')}}</label>
                            <input type="text" id="email_search" class="form-control" autocomplete="off"
                                placeholder="{{__('type_email')}}">
                            <input type="hidden" name="user_id" id="user_id">
                            <input type="hidden" name="invoice_id" id="modal_invoice_id">
                            <div id="emailSuggestions" class="list-group position-absolute w-100 z-3"
                                style="max-height: 150px; overflow-y: auto;"></div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">{{__('send_invoice')}}</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('cancel')}}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ __('Invoice List') }}</h5>
                        </div>
                        @if (auth()->user()->type == 'super admin')
                        @if (Gate::check('create invoice'))
                            <div class="col-auto">
                                <a href="{{ route('invoice.create') }}" class="btn btn-secondary"> <i
                                        class="ti ti-circle-plus align-text-bottom"></i> {{ __('Create Invoice') }}</a>
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
                                    <th>{{ __('Invoice') }}</th>
                                    <th>{{ __('Property') }}</th>
                                    <th>{{ __('Unit') }}</th>
                                    <th>{{ __('Invoice Month') }}</th>
                                    <th>{{ __('End Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    @if (Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                        <th class="text-right">{{ __('Action') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoices as $invoice)
                                    <tr>
                                        <td>{{ invoicePrefix() . $invoice->invoice_id }} </td>
                                        <td>{{ !empty($invoice->properties) ? $invoice->properties->name : '-' }} </td>
                                        <td>{{ !empty($invoice->units) ? $invoice->units->name : '-' }} </td>
                                        <td>{{ date('F Y', strtotime($invoice->invoice_month)) }} </td>
                                        <td>{{ dateFormat($invoice->end_date) }} </td>
                                        <td>{{ priceFormat($invoice->getInvoiceSubTotalAmount()) }}</td>
                                        <td>
                                            @if ($invoice->status == 'open')
                                                <span
                                                    class="badge bg-light-info">{{ \App\Models\Invoice::$status[$invoice->status] }}</span>
                                            @elseif($invoice->status == 'paid')
                                                <span
                                                    class="badge bg-light-success">{{ \App\Models\Invoice::$status[$invoice->status] }}</span>
                                            @elseif($invoice->status == 'partial_paid')
                                                <span
                                                    class="badge bg-light-warning">{{ \App\Models\Invoice::$status[$invoice->status] }}</span>
                                            @endif
                                        </td>
                                        @if (Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
                                            <td>
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.destroy', $invoice->id]]) !!}

                                                    <!-- Font Awesome Envelope Icon that triggers the modal -->
                                                    <i class="fas fa-envelope text-primary" style="cursor: pointer;"
                                                        data-bs-toggle="modal" data-bs-target="#sendInvoiceModal"  data-invoice-id="{{ $invoice->id }}"
                                                        title="Send Invoice"></i>


                                                    @can('show invoice')
                                                        <a class="text-warning" href="{{ route('invoice.show', $invoice->id) }}"
                                                            data-bs-toggle="tooltip" data-bs-original-title="{{ __('View') }}"> <i
                                                                data-feather="eye"></i></a>
                                                    @endcan
                                                    @can('edit invoice')
                                                        <a class="text-secondary" data-bs-original-title="{{ __('Edit') }}"
                                                            href="{{ route('invoice.edit', $invoice->id) }}"> <i
                                                                data-feather="edit"></i></a>
                                                    @endcan
                                                    @can('delete invoice')
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
    <script>
    const users = @json($users);

    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('email_search');
        const suggestions = document.getElementById('emailSuggestions');
        const userIdField = document.getElementById('user_id');
        const invoiceModal = document.getElementById('sendInvoiceModal');
        const invoiceIdInput = document.getElementById('modal_invoice_id');

    // Listen for when the modal is shown
    invoiceModal.addEventListener('show.bs.modal', function (event) {
        const triggerIcon = event.relatedTarget; // The <i> element
        const invoiceId = triggerIcon.getAttribute('data-invoice-id');
        invoiceIdInput.value = invoiceId;
    });
        input.addEventListener('input', () => {
            const query = input.value.trim().toLowerCase();
            suggestions.innerHTML = '';

            if (query.length < 2) return;

            users
                .filter(u => u.email.toLowerCase().includes(query))
                .forEach(u => {
                    const el = document.createElement('a');
                    el.href = '#';
                    el.className = 'list-group-item list-group-item-action';
                    el.textContent = u.email;
                    el.dataset.id = u.id;
                    el.dataset.email = u.email;

                    el.addEventListener('click', e => {
                        e.preventDefault();
                        input.value = u.email;
                        userIdField.value = u.id;
                        suggestions.innerHTML = '';
                    });

                    suggestions.appendChild(el);
                });
        });

        document.addEventListener('click', e => {
            if (!suggestions.contains(e.target) && e.target !== input) {
                suggestions.innerHTML = '';
            }
        });
    });
</script>


@endsection