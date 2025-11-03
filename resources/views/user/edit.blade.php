{{ Form::model($user, ['route' => ['users.update', $user->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        {{-- Role (only for non–super admin) --}}
        @if(\Auth::user()->type != 'super admin')
            <div class="form-group col-md-6">
                {{ Form::label('role', __('Assign Role'), ['class' => 'form-label']) }}
                {!! Form::select('role', $userRoles, !empty($user->roles) ? $user->roles[0]->id : null, [
                    'class' => 'form-control hidesearch',
                    'required' => 'required'
                ]) !!}
            </div>
        @endif

        {{-- Name fields --}}
        @if(\Auth::user()->type == 'super admin')
            <div class="form-group col-md-6">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                {{ Form::text('name', trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')), [
                    'class' => 'form-control',
                    'placeholder' => __('Enter Name'),
                    'required' => 'required'
                ]) }}
            </div>
        @else
            <div class="form-group col-md-6">
                {{ Form::label('first_name', __('First Name'), ['class' => 'form-label']) }}
                {{ Form::text('first_name', $user->first_name ?? null, [
                    'class' => 'form-control',
                    'placeholder' => __('Enter First Name'),
                    'required' => 'required'
                ]) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('last_name', __('Last Name'), ['class' => 'form-label']) }}
                {{ Form::text('last_name', $user->last_name ?? null, [
                    'class' => 'form-control',
                    'placeholder' => __('Enter Last Name'),
                    'required' => 'required'
                ]) }}
            </div>
        @endif

        {{-- Email --}}
        <div class="form-group col-md-6">
            {{ Form::label('email', __('User Email'), ['class' => 'form-label']) }}
            {{ Form::text('email', null, [
                'class' => 'form-control',
                'placeholder' => __('Enter User Email'),
                'required' => 'required'
            ]) }}
        </div>

        {{-- Phone --}}
        <div class="form-group col-md-6">
            {{ Form::label('phone_number', __('User Phone Number'), ['class' => 'form-label']) }}
            {{ Form::text('phone_number', null, [
                'class' => 'form-control',
                'placeholder' => __('Enter Phone Number')
            ]) }}
        </div>

        {{-- ✅ New fields --}}
        <div class="form-group col-md-6">
            {{ Form::label('telephone_number', __('Telephone Number'), ['class' => 'form-label']) }}
            {{ Form::text('telephone_number', $user->telephone_number ?? null, [
                'class' => 'form-control',
                'placeholder' => __('Enter Telephone Number')
            ]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('company_name', __('Company Name'), ['class' => 'form-label']) }}
            {{ Form::text('company_name', $user->company_name ?? null, [
                'class' => 'form-control',
                'placeholder' => __('Enter Company Name')
            ]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('vat_tax_code', __('VAT / Tax Code'), ['class' => 'form-label']) }}
            {{ Form::text('vat_tax_code', $user->vat_tax_code ?? null, [
                'class' => 'form-control',
                'placeholder' => __('Enter VAT or Tax Code')
            ]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('residential_address', __('Residential Address'), ['class' => 'form-label']) }}
            {{ Form::text('residential_address', $user->residential_address ?? null, [
                'class' => 'form-control',
                'placeholder' => __('Enter Residential Address')
            ]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('certified_email', __('Certified Email'), ['class' => 'form-label']) }}
            {{ Form::email('certified_email', $user->certified_email ?? null, [
                'class' => 'form-control',
                'placeholder' => __('Enter Certified Email')
            ]) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('sdi', __('SDI Code'), ['class' => 'form-label']) }}
            {{ Form::text('sdi', $user->sdi ?? null, [
                'class' => 'form-control',
                'placeholder' => __('Enter SDI Code')
            ]) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
