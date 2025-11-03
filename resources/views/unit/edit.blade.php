{{-- resources/views/unit/edit.blade.php --}}

{{ Form::model($unit, [
    'route' => ['unit.update', $property_id, $unit->id],
    'method' => 'PUT',
    'enctype' => 'multipart/form-data'
]) }}
{{ Form::token() }}

<div class="modal-body">
    <div class="row">

        <!-- Unit Name -->
        <div class="form-group col-12">
            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter unit name')]) }}
        </div>

        <!-- Bedrooms, Beds, Sofa Beds Count -->
        <div class="form-group col-md-3 col-sm-6">
            {{ Form::label('bedroom', __('Bedroom'), ['class' => 'form-label']) }}
            {{ Form::number('bedroom', null, ['class' => 'form-control', 'placeholder' => __('Enter number of bedrooms')]) }}
        </div>
        <div class="form-group col-md-3 col-sm-6">
            {{ Form::label('double_beds', __('Double Beds'), ['class' => 'form-label']) }}
            {{ Form::number('double_beds', null, ['class' => 'form-control', 'placeholder' => __('Enter number of double beds')]) }}
        </div>
        <div class="form-group col-md-3 col-sm-6">
            {{ Form::label('single_beds', __('Single Beds'), ['class' => 'form-label']) }}
            {{ Form::number('single_beds', null, ['class' => 'form-control', 'placeholder' => __('Enter number of single beds')]) }}
        </div>
        <div class="form-group col-md-3 col-sm-6">
            {{ Form::label('sofa_beds', __('Sofa Beds (Count)'), ['class' => 'form-label']) }}
            {{ Form::number('sofa_beds', null, ['class' => 'form-control', 'placeholder' => __('Enter number of sofa beds')]) }}
        </div>

        <!-- Kitchen & Baths -->
        <div class="form-group col-md-3 col-sm-6">
            {{ Form::label('kitchen', __('Kitchen'), ['class' => 'form-label']) }}
            {{ Form::select('kitchen', ['yes' => __('Yes'), 'no' => __('No')], null, ['class' => 'form-control modern-select']) }}
        </div>
        <div class="form-group col-md-3 col-sm-6">
            {{ Form::label('baths', __('Baths'), ['class' => 'form-label']) }}
            {{ Form::number('baths', null, ['class' => 'form-control', 'placeholder' => __('Enter number of baths')]) }}
        </div>

        <!-- Opening Type, Floor, Staircase -->
        <div class="form-group col-md-4">
            {{ Form::label('opening_type', __('Opening Type'), ['class' => 'form-label']) }}
            {{ Form::select('opening_type', ['key' => __('Key'), 'code' => __('Code')], null, ['class' => 'form-control']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('piano', __('Floor'), ['class' => 'form-label']) }}
            {{ Form::text('piano', null, ['class' => 'form-control', 'placeholder' => __('Enter Floor')]) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('staircase', __('Staircase'), ['class' => 'form-label']) }}
            {{ Form::text('staircase', null, ['class' => 'form-control', 'placeholder' => __('Enter Staircase')]) }}
        </div>

        <!-- Access Description -->
        <div class="form-group col-md-12">
            {{ Form::label('access_description', __('Access Description'), ['class' => 'form-label']) }}
            {{ Form::textarea('access_description', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter Access Description')]) }}
        </div>

        <!-- Sign Detail (from Property table) -->
        <div class="form-group col-md-12">
            {{ Form::label('sign_detail', __('Sign/Identifying Detail'), ['class' => 'form-label']) }}
            {{ Form::text('sign_detail', $property->sign_detail ?? null, ['class' => 'form-control', 'placeholder' => __('Enter sign on door or other identifying detail')]) }}
        </div>

        <!-- Description (from Property table) -->
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
            {{ Form::textarea('description', $property->description ?? null, ['class' => 'form-control', 'rows' => 4, 'placeholder' => __('Enter Property Description')]) }}
        </div>

        <!-- Notes -->
        <div class="form-group col-md-12">
            {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
            {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 2, 'placeholder' => __('Enter notes')]) }}
        </div>

        <!-- Photo Upload -->
        <div class="form-group col-md-12">
            {{ Form::label('arrangement_photos', __('Photo Settings (Bed/Towel Arrangement)'), ['class' => 'form-label']) }}
            {{ Form::file('arrangement_photos[]', ['class' => 'form-control', 'multiple' => true, 'accept' => 'image/jpeg,image/png']) }}
            <small class="text-muted d-block mt-1">Only add JPEG and PNG files.</small>
        </div>

    </div>
</div>

<div class="modal-footer">
    {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>

{{ Form::close() }}
