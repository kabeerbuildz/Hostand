{{ Form::open(array('url'=>'users','method'=>'post')) }}
<div class="modal-body">
    <div class="row">

        @if(\Auth::user()->type != 'super admin')
            <div class="form-group col-md-6">
                {{ Form::label('role', __('Assign Role'), ['class'=>'form-label']) }}
                {!! Form::select('role', $userRoles, null, ['class' => 'form-control basic-select','required'=>'required']) !!}
            </div>
        @endif

        {{-- Name and Surname --}}
        <div class="form-group col-md-6">
            {{ Form::label('first_name', __('First Name'), ['class'=>'form-label']) }}
            {{ Form::text('first_name', null, ['class'=>'form-control','placeholder'=>__('Enter first name'),'required'=>'required']) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('last_name', __('Last Name'), ['class'=>'form-label']) }}
            {{ Form::text('last_name', null, ['class'=>'form-control','placeholder'=>__('Enter last name'),'required'=>'required']) }}
        </div>

        {{-- Company Info --}}
        <div class="form-group col-md-6">
            {{ Form::label('ragione_sociale', __('Company Name (Ragione Sociale)'), ['class'=>'form-label']) }}
            {{ Form::text('ragione_sociale', null, ['class'=>'form-control','placeholder'=>__('Enter company name')]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('indirizzo', __('Address'), ['class'=>'form-label']) }}
            {{ Form::text('indirizzo', null, ['class'=>'form-control','placeholder'=>__('Enter address')]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('piva', __('VAT Number (P.IVA)'), ['class'=>'form-label']) }}
            {{ Form::text('piva', null, ['class'=>'form-control','placeholder'=>__('Enter VAT number')]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('codice_fiscale', __('Tax Code (Codice Fiscale)'), ['class'=>'form-label']) }}
            {{ Form::text('codice_fiscale', null, ['class'=>'form-control','placeholder'=>__('Enter tax code')]) }}
        </div>

        {{-- Contact Info --}}
        <div class="form-group col-md-6">
            {{ Form::label('email', __('Email'), ['class'=>'form-label']) }}
            {{ Form::email('email', null, ['class'=>'form-control','placeholder'=>__('Enter email'),'required'=>'required']) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('phone_number', __('Phone'), ['class'=>'form-label']) }}
            {{ Form::text('phone_number', null, ['class'=>'form-control','placeholder'=>__('Enter phone number')]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('pec', __('PEC'), ['class'=>'form-label']) }}
            {{ Form::text('pec', null, ['class'=>'form-control','placeholder'=>__('Enter PEC address')]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('codice_sdi', __('SDI Code'), ['class'=>'form-label']) }}
            {{ Form::text('codice_sdi', null, ['class'=>'form-control','placeholder'=>__('Enter SDI code')]) }}
        </div>

        {{-- Password --}}
        <div class="form-group col-md-6">
            {{ Form::label('password', __('Password'), ['class'=>'form-label']) }}
            {{ Form::password('password', ['class'=>'form-control','placeholder'=>__('Enter password'),'required'=>'required','minlength'=>"6"]) }}
        </div>

    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Create'), ['class'=>'btn btn-secondary ml-10']) }}
</div>
{{ Form::close() }}
