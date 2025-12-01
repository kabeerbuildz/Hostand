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
            {{ Form::label('name', __('Name'), ['class' => 'form-label', 'id' => 'unit-name-label']) }}
            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter unit name')]) }}
        </div>

        <!-- Bedroom Type Field (for BNB/HOTEL) -->
        <div class="form-group col-md-3 col-sm-6 bnb-hotel-only" style="display:none;">
            {{ Form::label('bedroom_type', __('Bedroom Type'), ['class' => 'form-label']) }}
            {{ Form::select('bedroom_type', ['' => __('Select Type'), 'double' => __('Double'), 'triple' => __('Triple'), 'quadruple' => __('Quadruple'), 'quintuple' => __('Quintuple')], null, ['class' => 'form-control']) }}
        </div>

        <!-- Bedrooms, Beds, Sofa Beds Count -->
        <div class="form-group col-md-3 col-sm-6 bnb-hotel-hide">
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
        <div class="form-group col-md-3 col-sm-6 bnb-hotel-hide">
            {{ Form::label('kitchen', __('Kitchen'), ['class' => 'form-label']) }}
            {{ Form::select('kitchen', ['yes' => __('Yes'), 'no' => __('No')], null, ['class' => 'form-control modern-select']) }}
        </div>
        <div class="form-group col-md-3 col-sm-6">
            {{ Form::label('baths', __('Baths'), ['class' => 'form-label']) }}
            {{ Form::number('baths', null, ['class' => 'form-control', 'placeholder' => __('Enter number of baths')]) }}
        </div>

        <!-- Opening Type, Floor, Staircase -->
        <div class="form-group col-md-4 holiday-home-hide">
            {{ Form::label('opening_type', __('Opening Type'), ['class' => 'form-label']) }}
            {{ Form::select('opening_type', ['' => __('Select Opening Type'), 'key' => __('Key'), 'code' => __('Code')], null, ['class' => 'form-control', 'id' => 'unit_opening_type_select']) }}
        </div>
        <div class="form-group col-md-4 holiday-home-hide">
            {{ Form::label('piano', __('Floor'), ['class' => 'form-label']) }}
            {{ Form::text('piano', null, ['class' => 'form-control', 'placeholder' => __('Enter Floor')]) }}
        </div>
        <div class="form-group col-md-4 holiday-home-hide bnb-hotel-hide">
            {{ Form::label('staircase', __('Staircase'), ['class' => 'form-label']) }}
            {{ Form::text('staircase', null, ['class' => 'form-control', 'placeholder' => __('Enter Staircase')]) }}
        </div>

        <!-- Opening Type Code Fields (initially hidden) -->
        <div id="unit-code-fields" class="d-none col-12">
            <div class="row">
                <div class="form-group col-md-6">
                    {{ Form::label('street_code', __('Street Opening Code'), ['class' => 'form-label']) }}
                    {{ Form::text('street_code', null, [
                        'class' => 'form-control',
                        'placeholder' => __('Enter street opening code')
                    ]) }}
                </div>

                <div class="form-group col-md-6">
                    {{ Form::label('door_code', __('Door Opening Code'), ['class' => 'form-label']) }}
                    {{ Form::text('door_code', null, [
                        'class' => 'form-control',
                        'placeholder' => __('Enter door opening code')
                    ]) }}
                </div>

                <div class="form-group col-md-6">
                    {{ Form::label('key_description', __('Key Description'), ['class' => 'form-label']) }}
                    {{ Form::text('key_description', null, [
                        'class' => 'form-control',
                        'placeholder' => __('Describe the key or entry details')
                    ]) }}
                </div>

                <div class="form-group col-md-6">
                    {{ Form::label('access_other', __('Other Access Information'), ['class' => 'form-label']) }}
                    {{ Form::text('access_other', null, [
                        'class' => 'form-control',
                        'placeholder' => __('Enter any other access instructions')
                    ]) }}
                </div>
            </div>
        </div>

        <!-- Access Description -->
        <div class="form-group col-md-12 holiday-home-hide">
            {{ Form::label('access_description', __('Access Description'), ['class' => 'form-label']) }}
            {{ Form::textarea('access_description', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter Access Description')]) }}
        </div>

        <!-- Sign Detail (from Property table) -->
        <div class="form-group col-md-12 holiday-home-hide bnb-hotel-hide">
            {{ Form::label('sign_detail', __('Sign/Identifying Detail'), ['class' => 'form-label']) }}
            {{ Form::text('sign_detail', $property->sign_detail ?? null, ['class' => 'form-control', 'placeholder' => __('Enter sign on door or other identifying detail')]) }}
        </div>

        <!-- Description (from Property table) -->
        <div class="form-group col-md-12 bnb-hotel-hide">
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

<script>
    (function($) {
        'use strict';
        
        // Wait for DOM and jQuery to be ready
        $(document).ready(function() {
            const nameLabel = $('#unit-name-label');
            const propertyId = parseInt('{{ $property_id }}');
            
            // Fetch property location type on page load
            if (propertyId) {
                $.ajax({
                    url: "{{ url('property') }}/" + propertyId + "/location-type",
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(data) {
                        console.log('Location type response:', data);
                        if (data.status === 'success') {
                            const locationTypeTitle = (data.location_type_title || '').toLowerCase();
                            console.log('Location type title:', locationTypeTitle);
                            handlePropertyTypeChange(locationTypeTitle);
                        } else {
                            console.warn('Failed to get location type:', data.msg);
                            resetFieldsToDefault();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching property location type:', error, xhr);
                        resetFieldsToDefault();
                    }
                });
            }
            
            // Opening type toggle for code fields
            const unitOpeningTypeSelect = $('#unit_opening_type_select');
            const unitCodeFields = $('#unit-code-fields');
            
            function toggleCodeFields() {
                const openingType = unitOpeningTypeSelect.val();
                
                // Only show code fields if opening type is 'code' AND the opening type field is visible (not hidden by Holiday Home)
                if (openingType === 'code' && unitOpeningTypeSelect.closest('.holiday-home-hide').is(':visible')) {
                    unitCodeFields.removeClass('d-none');
                } else {
                    // Hide code fields for 'key' or empty selection, or if opening type field is hidden
                    unitCodeFields.addClass('d-none');
                }
            }
            
            if (unitOpeningTypeSelect.length > 0) {
                unitOpeningTypeSelect.on('change', function() {
                    console.log('Opening type changed:', $(this).val());
                    toggleCodeFields();
                });
                
                // Initialize on page load based on current value
                setTimeout(function() {
                    toggleCodeFields();
                }, 200);
            }
        
            function handlePropertyTypeChange(locationTypeTitle) {
                console.log('Handling property type change:', locationTypeTitle);
                const isHolidayHome = locationTypeTitle.includes('casa vacanze') || locationTypeTitle.includes('holiday home');
                const isBnbHotel = locationTypeTitle.includes('affittacamere') || locationTypeTitle.includes('bnb') || 
                                  locationTypeTitle.includes('guesthouse') || locationTypeTitle.includes('hotel');
                
                console.log('Is Holiday Home:', isHolidayHome, 'Is BNB/HOTEL:', isBnbHotel);
                
                // Handle Holiday Home fields
                $('.holiday-home-hide').each(function() {
                    const $field = $(this);
                    const hasBnbHotelHide = $field.hasClass('bnb-hotel-hide');
                    
                    if (isHolidayHome) {
                        $field.hide();
                        // Remove required attributes
                        $field.find('[required]').removeAttr('required');
                    } else {
                        // Only show if it's not also hidden by BNB/HOTEL
                        if (hasBnbHotelHide && isBnbHotel) {
                            $field.hide();
                        } else {
                            $field.show();
                        }
                    }
                });
                
                // Handle BNB/HOTEL fields
                $('.bnb-hotel-hide').each(function() {
                    const $field = $(this);
                    const hasHolidayHomeHide = $field.hasClass('holiday-home-hide');
                    
                    if (isBnbHotel) {
                        $field.hide();
                        // Remove required attributes
                        $field.find('[required]').removeAttr('required');
                    } else {
                        // Only show if it's not also hidden by Holiday Home
                        if (hasHolidayHomeHide && isHolidayHome) {
                            $field.hide();
                        } else {
                            $field.show();
                        }
                    }
                });
                
                // Show Bedroom Type field for BNB/HOTEL
                $('.bnb-hotel-only').each(function() {
                    if (isBnbHotel) {
                        $(this).show();
                    } else {
                        $(this).hide();
                        // Remove required when hidden
                        $(this).find('[required]').removeAttr('required');
                    }
                });
                
                // Make Floor and Opening Type required for BNB/HOTEL
                if (isBnbHotel) {
                    $('[name="piano"]').attr('required', 'required');
                    $('[name="opening_type"]').attr('required', 'required');
                    $('[name="access_description"]').attr('required', 'required');
                    $('[name="bedroom_type"]').attr('required', 'required');
                } else {
                    $('[name="piano"]').removeAttr('required');
                    $('[name="opening_type"]').removeAttr('required');
                    if (!isHolidayHome) {
                        // Keep required for regular properties
                    } else {
                        $('[name="access_description"]').removeAttr('required');
                    }
                    $('[name="bedroom_type"]').removeAttr('required');
                }
                
                // Make bedroom and kitchen required for regular properties (not Holiday Home, not BNB/HOTEL)
                if (!isHolidayHome && !isBnbHotel) {
                    $('[name="bedroom"]').attr('required', 'required');
                    $('[name="kitchen"]').attr('required', 'required');
                } else {
                    $('[name="bedroom"]').removeAttr('required');
                    $('[name="kitchen"]').removeAttr('required');
                }
                
                // Update Name label
                if (nameLabel.length > 0) {
                    if (isHolidayHome) {
                        nameLabel.text('Structure Name');
                    } else if (isBnbHotel) {
                        nameLabel.text('Room Name');
                    } else {
                        nameLabel.text('Name');
                    }
                }
                
                // After fields are shown/hidden, check if code fields should be visible
                setTimeout(function() {
                    toggleCodeFields();
                }, 100);
            }
            
            function resetFieldsToDefault() {
                // Show all fields
                $('.holiday-home-hide, .bnb-hotel-hide').show();
                
                // Hide BNB-only fields
                $('.bnb-hotel-only').hide();
                
                // Reset name label
                if (nameLabel.length > 0) {
                    nameLabel.text('Name');
                }
            }
        });
    })(jQuery);
</script>
