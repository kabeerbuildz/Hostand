@php
    $user = \Auth::user();
    $tenant = $user->tenants;
@endphp

{{ Form::open(['url' => 'maintenance-request', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        {{-- Property --}}
        @if($user->type == 'tenant')
            {{ Form::hidden('property_id', !empty($tenant) ? $tenant->property : null, ['class' => 'form-control']) }}
            {{ Form::hidden('unit_id', !empty($tenant) ? $tenant->unit : null, ['class' => 'form-control']) }}
        @else
            <div class="form-group col-md-6 col-lg-6">
                {{ Form::label('property_id', __('Property'), ['class' => 'form-label']) }}
                {{ Form::select('property_id', $property, null, ['class' => 'form-control modern-select', 'id' => 'property_id']) }}
            </div>

            <div class="form-group col-md-6 col-lg-6">
                {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}
                <select class="form-control modern-select" id="unit_id_dynamic" name="unit_id">
                    <option value="">{{ __('Select Unit') }}</option>
                </select>
            </div>
        @endif

        {{-- Service --}}
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('service', __('Service Type'), ['class'=>'form-label']) }}
            {{ Form::select('service', $services, null, ['class'=>'form-control modern-select']) }}
        </div>

        {{-- Arrival Date/Time --}}
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('arrival_time', __('Arrival Date & Time'), ['class'=>'form-label']) }}
            {{ Form::datetimeLocal('arrival_time', null, ['class' => 'form-control']) }}
        </div>

        {{-- Number of People --}}
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('people_count', __('Number of People'), ['class'=>'form-label']) }}
            {{ Form::select('people_count', $peopleCount, null, ['class'=>'form-control modern-select']) }}
        </div>

        {{-- Notes --}}
        <div class="form-group col-md-12 col-lg-12">
            {{ Form::label('notes', __('Notes'), ['class'=>'form-label']) }}
            {{ Form::textarea('notes', null, ['class'=>'form-control','rows'=>3]) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    {{ Form::submit(__('Create'), ['class'=>'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}

{{-- CSS for modern dropdowns --}}
<style>
.modern-select {
    display: block;
    width: 100%;
    padding: 10px 12px;
    font-size: 14px;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: 8px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
    appearance: none; /* Remove default arrow */
}

.modern-select:focus {
    border-color: #6c63ff;
    box-shadow: 0 0 0 0.2rem rgba(108, 99, 255, 0.25);
    outline: none;
}

.modern-select option {
    padding: 8px;
}

/* Add a custom arrow using pseudo-element */
.modern-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'%3E%3Cpath fill='none' stroke='%23333' d='M2 0L0 2h4z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 8px 10px;
}
</style>

{{-- Clean AJAX for Property → Unit --}}
<script>
$(document).ready(function() {
    $('#property_id').change(function() {
        var propertyId = $(this).val();
        console.log('Selected Property ID:', propertyId);

        // Clear old options
        var $unitSelect = $('#unit_id_dynamic');
        $unitSelect.html('<option value="">{{ __("Select Unit") }}</option>');

        if(propertyId) {
            $.ajax({
                url: '/property/' + propertyId + '/unit',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log('Units received:', data);

                    if(data && Object.keys(data).length > 0) {
                        $.each(data, function(id, name) {
                            console.log('Adding Unit:', id, name);
                            $unitSelect.append('<option value="'+id+'">'+name+'</option>');
                        });
                    } else {
                        console.log('No units found for this property');
                    }

                    console.log('Unit dropdown updated');
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        } else {
            console.log('No property selected');
        }
    });

    // ✅ Auto-trigger change if a property was pre-selected
    let preSelected = "{{ request()->get('property_id') }}";
    if (preSelected) {
        $('#property_id').val(preSelected).trigger('change');
    }
});

</script>
