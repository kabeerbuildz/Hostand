@php
    $admin_logo = getSettingsValByName('company_logo');
    $settings = settings();
@endphp

@component('mail::message')

{{-- Show Logo --}}
<div style="text-align: center; margin-bottom: 20px;">
   <img src="{{ asset(Storage::url('upload/logo/')) . '/' . (isset($admin_logo) && !empty($admin_logo) ? $admin_logo : 'logo.png') }}"
    class="img-fluid brand-logo" alt="images"  height="80px"/>
</div>

# Fattura #{{ invoicePrefix() . $invoice->invoice_id }}

Ciao {{ !empty($tenant->user) ? $tenant->user->first_name : '' }},

Di seguito sono riportati i dettagli della fattura.

---

**Mese di fatturazione:** {{ date('F Y', strtotime($invoice->invoice_month)) }}  
**Data di scadenza:** {{ dateFormat($invoice->end_date) }}  
**Stato:**  
@switch($invoice->status)
    @case('paid')
        ✅  Pagato
        @break
    @case('partial_paid')
        🟡 Parzialmente pagato
        @break
    @default
        ❌  Non pagato
@endswitch

---

**Da:**  
**{{ $settings['company_name'] }}**  
Telefono: {{ $settings['company_phone'] }}  
Email: {{ $settings['company_email'] }}

---

**A:**  
{{ !empty($tenant->user) ? $tenant->user->first_name . ' ' . $tenant->user->last_name : '' }}  
Telefono: {{ !empty($tenant->user) ? $tenant->user->phone_number : '-' }}  
Indirizzo: {{ !empty($tenant) ? $tenant->address : '' }}

---

### Voci di fattura

@component('mail::table')
| Tipo | Descrizione | Importo |
|:-----|:------------|-------:|
@foreach ($invoice->types as $type)
| {{ !empty($type->types) ? $type->types->title : '-' }} | {{ $type->description }} | {{ priceFormat($type->amount) }} |
@endforeach
@endcomponent

---

**Totale:** {{ priceFormat($invoice->getInvoiceSubTotalAmount()) }}  
**Importo dovuto:** {{ priceFormat($invoice->getInvoiceDueAmount()) }}

@component('mail::button', ['url' => route('invoice.show', $invoice->id)])
Visualizza fattura
@endcomponent

Grazie,
{{ config('app.name') }}

@endcomponent
