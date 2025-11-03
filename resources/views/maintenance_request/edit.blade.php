@php
    $user = \Auth::user();
    $tenant = $user->tenants;
@endphp
{{ Form::model($maintenanceRequest, ['route' => ['maintenance-request.update', $maintenanceRequest->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
<div class="modal-body">
    <div class="row">
        @if ($user->type == 'tenant')
            {{ Form::hidden('property_id', !empty($tenant) ? $tenant->property : null, ['class' => 'form-control']) }}
            {{ Form::hidden('unit_id', !empty($tenant) ? $tenant->unit : null, ['class' => 'form-control']) }}
        @else
            <div class="form-group col-md-6 col-lg-6">
                {{ Form::label('property_id', __('Property'), ['class' => 'form-label']) }}
                {{ Form::select('property_id', $property, null, ['class' => 'form-control hidesearch', 'id' => 'property_id']) }}
            </div>
            <div class="form-group col-lg-6 col-md-6">
                {{ Form::label('unit_id', __('Unit'), ['class' => 'form-label']) }}
                <div class="unit_div">
                    <select class="form-control hidesearch unit" id="unit_id" name="unit_id">
                        <option value="">{{ __('Select Unit') }}</option>
                    </select>
                </div>
            </div>
        @endif

{{-- 
        <div class="form-group  col-md-6 col-lg-6">
            {{ Form::label('request_date', __('Request Date'), ['class' => 'form-label']) }}
            {{ Form::date('request_date', null, ['class' => 'form-control']) }}
        </div> --}}
        <div class="form-group col-md-6 col-lg-6">
    {{ Form::label('arrival_time', __('Arrival Date & Time'), ['class'=>'form-label']) }}

    {{ Form::datetimeLocal(
        'arrival_time', 
        old('arrival_time', optional($maintenanceRequest->arrival_time) 
            ? \Carbon\Carbon::parse($maintenanceRequest->arrival_time)->format('Y-m-d\TH:i') 
            : null
        ), 
        ['class' => 'form-control']
    ) }}
</div>
        @unless(Auth::user()->type == 'owner')
    <div class="form-group col-md-6 col-lg-6">
        {{ Form::label('maintainer_id', __('Maintainer'), ['class' => 'form-label']) }}
        {{ Form::select('maintainer_id', $maintainers, null, ['class' => 'form-control hidesearch']) }}
    </div>
@endunless

       


        <div class="form-group col-md-6 col-lg-6">
    {{ Form::label('issue_type', __('Issue Type'), ['class' => 'form-label']) }}
    {{ Form::select(
        'issue_type',
        $types,
        old('service_type', $maintenanceRequest->service_type ?? null),
        ['class' => 'form-control hidesearch']
    ) }}
</div>

        {{-- <div class="form-group col-md-6 col-lg-6">
            {{Form::label('status',__('Status'),array('class'=>'form-label'))}}
            {{Form::select('status',$status,null,array('class'=>'form-control hidesearch'))}}
        </div> --}}
        <div class="form-group col-md-6 col-lg-6">
            {{ Form::label('people_count', __('Number of People'), ['class' => 'form-label']) }}
            {{ Form::select(
                'people_count',
                $peopleCount ?? [],
                old('people_count', $maintenanceRequest->people_count ?? null),
                ['class' => 'form-control modern-select'],
            ) }}
        </div>

        {{-- <div class="form-group  col-md-12 col-lg-12">
            {{Form::label('issue_attachment',__('Issue Attachment'),array('class'=>'form-label'))}}
            {{Form::file('issue_attachment',array('class'=>'form-control'))}}
        </div> --}}
        <div class="form-group  col-md-12 col-lg-12">
            {{ Form::label('notes', __('Notes'), ['class' => 'form-label']) }}
            {{ Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 3]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
<script>
    $('#property_id').on('change', function() {
        "use strict";
        var property_id = $(this).val();
        var url = '{{ route('property.unit', ':id') }}';
        url = url.replace(':id', property_id);
        $.ajax({
            url: url,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                property_id: property_id,
            },
            contentType: false,
            processData: false,
            type: 'GET',
            success: function(data) {
                $('.unit').empty();
                var unit =
                    `<select class="form-control hidesearch unit" id="unit_id" name="unit_id"></select>`;
                $('.unit_div').html(unit);

                $.each(data, function(key, value) {
                    var unit_id = $('#edit_unit').val();
                    if (key == unit_id) {
                        $('.unit').append('<option selected value="' + key + '">' + value +
                            '</option>');
                    } else {
                        $('.unit').append('<option   value="' + key + '">' + value +
                            '</option>');
                    }
                });
                $(".hidesearch").each(function() {
                    var basic_select = new Choices(this, {
                        searchEnabled: false,
                        removeItemButton: true,
                    });
                });
            },

        });
    });

    $('#property_id').trigger('change');
</script>
