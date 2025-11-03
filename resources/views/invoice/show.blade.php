@extends('layouts.app')
@section('page-title')
    {{ __('Invoice') }}
@endsection
@php
    $admin_logo = getSettingsValByName('company_logo');
    $settings = settings();

@endphp


@push('script-page')
    <script src="{{ asset('assets/js/vendors/ckeditor/ckeditor.js') }}"></script>
    <script>
        setTimeout(() => {
            feather.replace();
        }, 500);
    </script>
@endpush


@push('script-page')
    <script>
        $(document).on('click', '.print', function() {
            var printContents = document.getElementById('invoice-print').innerHTML;
            var originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;

            window.print();

            document.body.innerHTML = originalContents;

        });
    </script>
    <script src="https://js.stripe.com/v3/"></script>

    <script type="text/javascript">
        @if (
            $invoicePaymentSettings['STRIPE_PAYMENT'] == 'on' &&
                !empty($invoicePaymentSettings['STRIPE_KEY']) &&
                !empty($invoicePaymentSettings['STRIPE_SECRET']))
            var stripe_key = Stripe('{{ $invoicePaymentSettings['STRIPE_KEY'] }}');
            var stripe_elements = stripe_key.elements();
            var strip_css = {
                base: {
                    fontSize: '14px',
                    color: '#32325d',
                },
            };
            var stripe_card = stripe_elements.create('card', {
                style: strip_css
            });
            stripe_card.mount('#card-element');

            var stripe_form = document.getElementById('stripe-payment');
            stripe_form.addEventListener('submit', function(event) {
                event.preventDefault();

                stripe_key.createToken(stripe_card).then(function(result) {
                    if (result.error) {
                        $("#stripe_card_errors").html(result.error.message);
                        $.NotificationApp.send("Error", result.error.message, "top-right",
                            "rgba(0,0,0,0.2)", "error");
                    } else {
                        var token = result.token;
                        var stripeForm = document.getElementById('stripe-payment');
                        var stripeHiddenData = document.createElement('input');
                        stripeHiddenData.setAttribute('type', 'hidden');
                        stripeHiddenData.setAttribute('name', 'stripeToken');
                        stripeHiddenData.setAttribute('value', token.id);
                        stripeForm.appendChild(stripeHiddenData);
                        stripeForm.submit();
                    }
                });
            });
        @endif
    </script>

    <script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
    <script>
        @if (
            $invoicePaymentSettings['flutterwave_payment'] == 'on' &&
                !empty($invoicePaymentSettings['flutterwave_public_key']) &&
                !empty($invoicePaymentSettings['flutterwave_secret_key']))

            $(document).on("click", "#flutterwavePaymentBtn", function() {
                var amount = $('.amount').val();
                var flutterwaveCallbackURL = "{{ url('invoice/flutterwave') }}";
                var tx_ref = "RX1_" + Math.floor((Math.random() * 1000000000) + 1);
                var customer_email = '{{ \Auth::user()->email }}';
                var flutterwave_public_key = '{{ $invoicePaymentSettings['flutterwave_public_key'] }}';
                var nowTim = "{{ date('d-m-Y-h-i-a') }}";
                var currency = '{{ $invoicePaymentSettings['CURRENCY'] }}';

                if (amount) {
                    var flutterwavePayment = getpaidSetup({
                        txref: tx_ref,
                        PBFPubKey: flutterwave_public_key,
                        amount: amount,
                        currency: currency,
                        customer_email: customer_email,
                        meta: [{
                            metaname: "payment_id",
                            metavalue: "id"
                        }],
                        onclose: function() {},
                        callback: function(result) {
                            var txRef = result.tx.txRef;
                            var redirectUrl = flutterwaveCallbackURL + '/' +
                                '{{ \Illuminate\Support\Facades\Crypt::encrypt($invoice->id) }}' +
                                '/' + txRef;
                            if (result.tx.chargeResponseCode == "00" || result.tx.chargeResponseCode ==
                                "0") {
                                window.location.href = redirectUrl;
                            } else {
                                alert('Payment failed');
                            }
                            flutterwavePayment.close();
                        }
                    });
                } else {
                    alert('Please enter a valid amount');
                }
            });
        @endif
    </script>
@endpush
@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">
                {{ __('Dashboard') }}
            </a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('invoice.index') }}">{{ __('Invoice') }}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Details') }}</a>
        </li>
    </ul>
@endsection
@section('content')




    <div class="row">


        <div class="{{ $invoice->getInvoiceDueAmount() > 0 ? 'col-lg-8 col-md-12' : 'col-lg-12 col-md-12' }}">
            <div>

                <div class="card">

                    <div class="card-header">
                        <ul class="list-inline ms-auto mb-0 d-flex justify-content-end flex-wrap">

                            @if (\Auth::user()->type == 'owner')
                                @if ($invoice->getInvoiceDueAmount() > 0)
                                    <li class="list-inline-item align-bottom me-2">
                                        <a href="#" class="avtar avtar-s btn-link-secondary customModal"
                                            data-size="lg" data-url="{{ route('invoice.reminder', $invoice->id) }}"
                                            data-bs-toggle="tooltip" data-bs-original-title="{{ __('Payment Reminder') }}"
                                            data-title="{{ __('Payment Reminder') }}">
                                            <i class="ph-duotone ph-repeat f-22"></i>
                                        </a>
                                    </li>
                                @endif
                            @endif
                            <li class="list-inline-item align-bottom me-2">
                                <a href="#" class="avtar avtar-s btn-link-secondary print" data-bs-toggle="tooltip"
                                    data-bs-original-title="{{ __('Download') }}">
                                    <i class="ph-duotone ph-printer f-22"></i>
                                </a>
                            </li>

                        </ul>
                    </div>

                    <div class="card-body" id="invoice-print">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="row align-items-center g-3">
                                    <div class="col-sm-6">
                                        <div class="d-flex align-items-center mb-2 navbar-brand img-fluid invoice-logo">
                                            <img src="{{ asset(Storage::url('upload/logo/')) . '/' . (isset($admin_logo) && !empty($admin_logo) ? $admin_logo : 'logo.png') }}"
                                                class="img-fluid brand-logo" alt="images" />
                                        </div>
                                        <p class="mb-0">{{ invoicePrefix() . $invoice->invoice_id }}</p>
                                    </div>
                                    <div class="col-sm-6 text-sm-end">
                                        <h6>
                                            {{ __('Invoice Month') }}
                                            <span
                                                class="text-muted f-w-400">{{ date('F Y', strtotime($invoice->invoice_month)) }}</span>
                                        </h6>
                                        <h6>
                                            {{ __('Due Date') }}
                                            <span class="text-muted f-w-400">{{ dateFormat($invoice->end_date) }}</span>
                                        </h6>
                                        <h6>
                                            {{ __('Status') }}
                                            <span class="text-muted f-w-400">
                                                @if ($invoice->status == 'unpaid')
                                                    <span
                                                        class="badge text-bg-danger">{{ \App\Models\Invoice::$status[$invoice->status] }}</span>
                                                @elseif($invoice->status == 'paid')
                                                    <span
                                                        class="badge text-bg-success">{{ \App\Models\Invoice::$status[$invoice->status] }}</span>
                                                @elseif($invoice->status == 'partial_paid')
                                                    <span
                                                        class="badge text-bg-warning">{{ \App\Models\Invoice::$status[$invoice->status] }}</span>
                                                @endif
                                            </span>
                                        </h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded p-3">
                                    <h6 class="mb-0">From:</h6>
                                    <h5>{{ $settings['company_name'] }}</h5>
                                    <p class="mb-0">{{ $settings['company_phone'] }}</p>
                                    <p class="mb-0">{{ $settings['company_email'] }}</p>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="border rounded p-3">
                                    <h6 class="mb-0">To:</h6>
                                    <h5>{{ !empty($tenant) && !empty($tenant->user) ? $tenant->user->first_name . ' ' . $tenant->user->last_name : '' }}
                                    </h5>
                                    <p class="mb-0">
                                        {{ !empty($tenant) && !empty($tenant->user) ? $tenant->user->phone_number : '-' }}
                                    </p>
                                    <p class="mb-0">
                                        {{ !empty($tenant) ? $tenant->address : '' }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th width="46%">{{ __('Type') }}</th>
                                                <th width="46%">{{ __('Description') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($invoice->types as $k => $type)
                                                <tr>
                                                    <td>{{ !empty($type->types) ? $type->types->title : '-' }}</td>
                                                    <td>{{ $type->description }}</td>
                                                    <td>{{ priceFormat($type->amount) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-start">
                                    <hr class="mb-2 mt-1 border-secondary border-opacity-50" />
                                </div>
                            </div>
                            <div class="col-12 ">
                                <div class="invoice-total ms-auto">
                                    <div class="row">

                                        <div class="col-4">
                                            <p class="f-w-600 mb-1 text-start">{{ __('Total') }} :</p>
                                        </div>
                                        <div class="col-7">
                                            <p class="f-w-600 mb-1 text-end">
                                                {{ priceFormat($invoice->getInvoiceSubTotalAmount()) }}
                                            </p>
                                        </div>
                                        <div class="col-4">
                                            <p class="f-w-600 mb-1 text-start">{{ __('Due Amount') }} :</p>
                                        </div>
                                        <div class="col-7">
                                            <p class="f-w-600 mb-1 text-end">
                                                {{ priceFormat($invoice->getInvoiceDueAmount()) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>

                </div>
            </div>
        </div>

        @if ($invoice->getInvoiceDueAmount() > 0)
            @if (\Auth::user()->type == 'tenant')
                <div class="mt-25 col-lg-4 col-md-12" id="paymentModal" style="">
                    <div class="card">

                        <div class="col-xxl-12 cdx-xxl-100">
                            <div class="payment-method">
                                <div class="card-header">
                                    <h5> Add Payment </h5>
                                </div>
                                <div class="card-body">
                                    <ul class="nav nav-tabs profile-tabs border-bottom mb-3 d-print-none" id="myTab"
                                        role="tablist">
                                        @if ($settings['bank_transfer_payment'] == 'on')
                                            <li class="nav-item">
                                                <a class="nav-link active" id="profile-tab-1" data-bs-toggle="tab"
                                                    href="#bank_transfer" role="tab"
                                                    aria-selected="true">{{ __('Bank Transfer') }} </a>

                                            </li>
                                        @endif

                                        @if ($settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_KEY']) && !empty($settings['STRIPE_SECRET']))
                                            <li class="nav-item">

                                                <a class="nav-link" id="profile-tab-2" data-bs-toggle="tab"
                                                    href="#stripe_payment" role="tab"
                                                    aria-selected="true">{{ __('Stripe') }}</a>
                                            </li>
                                        @endif


                                        @if (
                                            $settings['paypal_payment'] == 'on' &&
                                                !empty($settings['paypal_client_id']) &&
                                                !empty($settings['paypal_secret_key']))
                                            <li class="nav-item">
                                                <a class="nav-link" id="profile-tab-3" data-bs-toggle="tab"
                                                    href="#paypal_payment" role="tab" aria-selected="true">
                                                    {{ __('Paypal') }} </a>
                                            </li>
                                        @endif

                                        @if (
                                            $settings['flutterwave_payment'] == 'on' &&
                                                !empty($settings['flutterwave_public_key']) &&
                                                !empty($settings['flutterwave_secret_key']))
                                            <li class="nav-item">
                                                <a class="nav-link" id="profile-tab-3" data-bs-toggle="tab"
                                                    href="#flutterwave_payment" role="tab" aria-selected="true">
                                                    {{ __('Flutterwave') }}
                                                </a>
                                            </li>
                                        @endif

                                    </ul>

                                    <div class="tab-content">
                                        @if ($settings['bank_transfer_payment'] == 'on')
                                            <div class="tab-pane fade active show" id="bank_transfer">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class=" profile-user-box">
                                                            <form
                                                                action="{{ route('invoice.banktransfer.payment', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)) }}"
                                                                method="post" class="require-validation"
                                                                id="bank-payment" enctype="multipart/form-data">
                                                                @csrf
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="card-name-on"
                                                                                class="f-w-600 mb-1 text-start">{{ __('Bank Name') }}</label>
                                                                            <p>{{ $settings['bank_name'] }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="card-name-on"
                                                                                class="f-w-600 mb-1 text-start">{{ __('Bank Holder Name') }}</label>
                                                                            <p>{{ $settings['bank_holder_name'] }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="card-name-on"
                                                                                class="f-w-600 mb-1 text-start">{{ __('Bank Account Number') }}</label>
                                                                            <p>{{ $settings['bank_account_number'] }}</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="card-name-on"
                                                                                class="f-w-600 mb-1 text-start">{{ __('Bank IFSC Code') }}</label>
                                                                            <p>{{ $settings['bank_ifsc_code'] }}</p>
                                                                        </div>
                                                                    </div>
                                                                    @if (!empty($settings['bank_other_details']))
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <label for="card-name-on"
                                                                                    class="f-w-600 mb-1 text-start">{{ __('Bank Other Details') }}</label>
                                                                                <p>{{ $settings['bank_other_details'] }}
                                                                                </p>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <label for="amount"
                                                                                class="form-label text-dark">{{ __('Amount') }}</label>
                                                                            <input type="number" name="amount"
                                                                                class="form-control required"
                                                                                value="{{ $invoice->getInvoiceDueAmount() }}"
                                                                                placeholder="{{ __('Enter Amount') }}"
                                                                                required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <label for="card-name-on"
                                                                                class="form-label text-dark">{{ __('Attachment') }}</label>
                                                                            <input type="file" name="receipt"
                                                                                id="receipt" class="form-control"
                                                                                required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <label for="notes"
                                                                                class="form-label text-dark">{{ __('Notes') }}</label>
                                                                            <input type="text" name="notes"
                                                                                class="form-control " value=""
                                                                                placeholder="{{ __('Enter notes') }}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-12 ">
                                                                        <input type="submit" value="{{ __('Pay') }}"
                                                                            class="btn btn-primary">
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($settings['STRIPE_PAYMENT'] == 'on' && !empty($settings['STRIPE_KEY']) && !empty($settings['STRIPE_SECRET']))
                                            <div class="tab-pane fade " id="stripe_payment">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class=" profile-user-box">
                                                            <form
                                                                action="{{ route('invoice.stripe.payment', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)) }}"
                                                                method="post" class="require-validation"
                                                                id="stripe-payment">
                                                                @csrf
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <label for="amount"
                                                                                class="form-label text-dark">{{ __('Amount') }}</label>
                                                                            <input type="number" name="amount"
                                                                                class="form-control required"
                                                                                value="{{ $invoice->getInvoiceDueAmount() }}"
                                                                                placeholder="{{ __('Enter Amount') }}"
                                                                                required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <label for="card-name-on"
                                                                                class="form-label text-dark">{{ __('Card Name') }}</label>
                                                                            <input type="text" name="name"
                                                                                id="card-name-on"
                                                                                class="form-control required"
                                                                                placeholder="{{ __('Card Holder Name') }}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12">
                                                                        <label for="card-name-on"
                                                                            class="form-label text-dark">{{ __('Card Details') }}</label>
                                                                        <div id="card-element">
                                                                        </div>
                                                                        <div id="card-errors" role="alert"></div>
                                                                    </div>
                                                                    <div class="col-sm-12 mt-3">

                                                                        <input type="submit" value="{{ __('Pay Now') }}"
                                                                            class="btn btn-primary">
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        @if (
                                            $settings['paypal_payment'] == 'on' &&
                                                !empty($settings['paypal_client_id']) &&
                                                !empty($settings['paypal_secret_key']))
                                            <div class="tab-pane fade" id="paypal_payment">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class=" profile-user-box">
                                                            <form
                                                                action="{{ route('invoice.paypal', \Illuminate\Support\Facades\Crypt::encrypt($invoice->id)) }}"
                                                                method="post" class="require-validation">
                                                                @csrf
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <label for="amount"
                                                                                class="form-label text-dark">{{ __('Amount') }}</label>
                                                                            <input type="number" name="amount"
                                                                                class="form-control required"
                                                                                value="{{ $invoice->getInvoiceDueAmount() }}"
                                                                                placeholder="{{ __('Enter Amount') }}"
                                                                                required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-sm-12 ">
                                                                        <input type="submit" value="{{ __('Pay Now') }}"
                                                                            class="btn btn-primary">
                                                                    </div>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        @if (
                                            $settings['flutterwave_payment'] == 'on' &&
                                                !empty($settings['flutterwave_public_key']) &&
                                                !empty($settings['flutterwave_secret_key']))
                                            <div class="tab-pane fade" id="flutterwave_payment">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class=" profile-user-box">
                                                            <form action="#" method="post"
                                                                class="require-validation" id="flutterwavePaymentForm">
                                                                @csrf
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">

                                                                            <label for="amount"
                                                                                class="form-label text-dark">{{ __('Amount') }}</label>
                                                                            <input type="number" name="amount"
                                                                                class="form-control amount required"
                                                                                value="{{ $invoice->getInvoiceDueAmount() }}"
                                                                                placeholder="{{ __('Enter Amount') }}"
                                                                                required>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-sm-12 ">
                                                                        <input type="button" value="{{ __('Pay Now') }}"
                                                                            class="btn btn-primary"
                                                                            id="flutterwavePaymentBtn">
                                                                    </div>

                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="mt-25 col-lg-4 col-md-12" id="paymentModal" style="">
                    <div class="card">

                        <div class="col-xxl-12 cdx-xxl-100">
                            <div class="payment-method">
                                <div class="card-header">
                                    <h5> Add Payment </h5>
                                </div>
                                <div class="card-body">

                                    {{ Form::open(['route' => ['invoice.payment.store', $invoice->id], 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="form-group  col-md-12">
                                                {{ Form::label('payment_date', __('Payment Date'), ['class' => 'form-label']) }}
                                                {{ Form::date('payment_date', date('Y-m-d'), ['class' => 'form-control']) }}
                                            </div>
                                            <div class="form-group  col-md-12">
                                                {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
                                                {{ Form::number('amount', $invoice->getInvoiceDueAmount(), ['class' => 'form-control']) }}
                                            </div>
                                            <div class="form-group  col-md-12">
                                                {{ Form::label('receipt', __('Receipt'), ['class' => 'form-label']) }}
                                                {{ Form::file('receipt', ['class' => 'form-control']) }}
                                            </div>
                                            <div class="form-group ">
                                                {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
                                                {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter Payment Notes')]) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">

                                        {{ Form::submit(__('Add'), ['class' => 'btn btn-secondary btn-rounded']) }}
                                    </div>
                                    {{ Form::close() }}


                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif


    </div>

    <div class="row">
        <div class="col-12">
            <div class="card" id="invoice-print">
                <div class="card-header">
                    <h5>{{ __('Payment History') }}</h5>
                </div>
                <div class="card-body pt-0">
                    <div class="dt-responsive table-responsive">
                        <table class="table table-hover ">
                            <thead>
                                <tr>
                                    <th>{{ __('Transaction Id') }}</th>
                                    <th>{{ __('Payment Date') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Notes') }}</th>
                                    <th>{{ __('Receipt') }}</th>
                                    @can('delete invoice payment')
                                        <th class="text-right">{{ __('Action') }}</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoice->payments as $payment)
                                    <tr role="row">
                                        <td>{{ $payment->transaction_id }} </td>
                                        <td>{{ dateFormat($payment->payment_date) }} </td>
                                        <td>{{ priceFormat($payment->amount) }} </td>
                                        <td>{{ $payment->notes }} </td>
                                        <td>
                                            @if (!empty($payment->receipt))
                                                <a href="{{ asset(Storage::url('upload/receipt')) . '/' . $payment->receipt }}"
                                                    download="download"><i data-feather="download"></i></a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        @can('delete invoice payment')
                                            <td class="text-right">
                                                <div class="cart-action">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.payment.destroy', $invoice->id, $payment->id]]) !!}
                                                    <a class=" text-danger confirm_dialog" data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('Detete') }}" href="#"> <i
                                                            data-feather="trash-2"></i></a>
                                                    {!! Form::close() !!}
                                                </div>
                                            </td>
                                        @endcan
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

