@extends('layouts.app')
@section('page-title')
{{ __('Property Create') }}
@endsection
@push('script-page')
<script src="{{ asset('assets/js/vendors/dropzone/dropzone.js') }}"></script>
<script>
    var dropzone = new Dropzone('#demo-upload', {
        previewTemplate: document.querySelector('.preview-dropzon').innerHTML,
        parallelUploads: 10,
        thumbnailHeight: 120,
        thumbnailWidth: 120,
        maxFilesize: 10,
        filesizeBase: 1000,
        autoProcessQueue: false,
        thumbnail: function(file, dataUrl) {
            if (file.previewElement) {
                file.previewElement.classList.remove("dz-file-preview");
                var images = file.previewElement.querySelectorAll("[data-dz-thumbnail]");
                for (var i = 0; i < images.length; i++) {
                    var thumbnailElement = images[i];
                    thumbnailElement.alt = file.name;
                    thumbnailElement.src = dataUrl;
                }
                setTimeout(function() {
                    file.previewElement.classList.add("dz-image-preview");
                }, 1);
            }
        }

    });

    // AJAX submit — builds FormData including dynamic unit files
    $('#property-submit').on('click', function() {
        "use strict";
        $('#property-submit').attr('disabled', true);
        var fd = new FormData();

        // thumbnail (single)
        var thumbInput = document.getElementById('thumbnail');
        if (thumbInput && thumbInput.files.length) {
            fd.append('thumbnail', thumbInput.files[0]);
        }

        // Dropzone property images
        try {
            var dzFiles = $('#demo-upload').get(0).dropzone.getAcceptedFiles();
            $.each(dzFiles, function(key, file) {
                fd.append('property_images[' + key + ']', file);
            });
        } catch (e) {
            // ignore if dropzone not ready
        }

        // serialize normal fields (this includes array-style names like units[0][...])
        var other_data = $('#property_form').serializeArray();
        $.each(other_data, function(key, input) {
            // Note: serializeArray does not include file inputs — we will append file inputs separately
            fd.append(input.name, input.value);
        });

        // Append all file inputs (including units' arrangement_photos[])
        $('#property_form').find('input[type="file"]').each(function() {
            var inputName = $(this).attr('name');
            // skip thumbnail since already appended
            if (!inputName) return;
            if (this.files.length) {
                for (var i = 0; i < this.files.length; i++) {
                    fd.append(inputName, this.files[i]);
                }
            }
        });

        // DEBUG: list FormData entries (so you can confirm units[x][street_opening_code] exist)
        console.group('FormData entries');
        for (var pair of fd.entries()) {
            var value = pair[1];
            if (value instanceof File) {
                console.log(pair[0] + ': File(' + value.name + ')');
            } else {
                console.log(pair[0] + ':', value);
            }
        }
        console.groupEnd();

        $.ajax({
            url: "{{ route('property.store') }}",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: fd,
            contentType: false,
            processData: false,
            type: 'POST',
            success: function(data) {
                if (data.status === "success") {
                    // Show notification using your notifier.js
                    notifier.show('Success', data.msg, 'success', '', 1000);

                    // Redirect to the show page
                    var url = '{{ route("property.show", ":id") }}'.replace(':id', data.id);
                    setTimeout(function() {
                        window.location.href = url;
                    }, 1000);
                } else {
                    notifier.show('Error', data.msg, 'error', '', 3000);
                }
            },
            error: function(xhr) {
                $('#property-submit').attr('disabled', false);
                console.log('AJAX error response (xhr):', xhr); // debug

                var message = 'An error occurred';

                if (xhr.responseJSON && xhr.responseJSON.msg) {
                    message = xhr.responseJSON.msg;
                } else if (xhr.responseJSON && typeof xhr.responseJSON === 'string') {
                    message = xhr.responseJSON;
                } else if (xhr.responseText) {
                    try {
                        var parsed = JSON.parse(xhr.responseText);
                        message = parsed.msg || JSON.stringify(parsed);
                    } catch (e) {
                        message = xhr.responseText;
                    }
                }

                if (typeof toastrs === 'function') {
                    toastrs('Error', message, 'error');
                } else {
                    alert(message);
                }
            }
        });

    });
</script>

<script>
    $('#rent_type').on('change', function() {
        "use strict";
        var type = this.value;
        $('.rent_type').addClass('d-none')
        $('.' + type).removeClass('d-none')

        var input1 = $('.rent_type').find('input');
        input1.prop('disabled', true);
        var input2 = $('.' + type).find('input');
        input2.prop('disabled', false);
    });
</script>

<script>
    $(document).ready(function() {
        $('.nextButton').on('click', function() {
            let $activeTab = $('.tab-content .tab-pane.active'); // Current active tab
            let $nextTab = $activeTab.next('.tab-pane'); // Next tab

            if ($nextTab.length > 0) {
                let nextTabId = $nextTab.attr('id');
                $('a[href="#' + nextTabId + '"]').tab('show'); // Move to next tab

                // If the next tab is the last, change the button text to "Submit"
                if ($nextTab.is(':last-child')) {
                    $(this).text('Next').addClass('submit-button');
                }
            } else if ($(this).hasClass('submit-button')) {
                // Handle form submission
                $('form').submit();
            }
        });

        // Update button text on manual tab switch
        $('a[data-toggle="tab"]').on('shown.bs.tab', function() {
            let $activeTab = $('.tab-content .tab-pane.active');
            let isLastTab = $activeTab.is(':last-child');

            if (!isLastTab) {
                $('.nextButton').text('Next').removeClass('submit-button');
            }
        });
    });
</script>


<script>
    $(document).ready(function() {

        // Reindex unit inputs to units[index][field] based on data-field attribute
        function reindexAllUnits() {
            $('.unit_list').each(function(index) {
                $(this).attr('data-index', index);
                $(this).find('[data-field]').each(function() {
                    var field = $(this).attr('data-field');
                    if (!field) return;
                    var isFile = $(this).attr('type') === 'file';
                    if (isFile && $(this).prop('multiple')) {
                        $(this).attr('name', 'units[' + index + '][' + field + '][]');
                    } else {
                        $(this).attr('name', 'units[' + index + '][' + field + ']');
                    }
                });

                // Optional: update label 'for' attributes if they follow the units[x][name] pattern
                $(this).find('label').each(function() {
                    var forAttr = $(this).attr('for');
                    if (!forAttr) return;
                    var newFor = forAttr.replace(/units\[\d+\]/, 'units[' + index + ']');
                    $(this).attr('for', newFor);
                });
            });
            toggleRemoveServiceButton();
        }

        function toggleRemoveServiceButton() {
            let serviceCount = $('.unit_list').length;
            $('.remove-service').toggle(serviceCount > 1);
        }

        // Function to handle opening type change for a specific unit
        function handleOpeningTypeChange(selectElement) {
            const unitWrapper = $(selectElement).closest('.unit_list');
            const accessFields = unitWrapper.find('.access-code-fields');

            if ($(selectElement).val() === 'code') {
                accessFields.slideDown(200);
            } else {
                accessFields.slideUp(200);
            }
        }

        // Initialize opening type for all existing units on page load (delegated call)
        $(document).on('change', '.opening-type', function() {
            handleOpeningTypeChange(this);
        });

        $('.opening-type').each(function() {
            handleOpeningTypeChange(this);
        });

        // Add unit: clone first unit, clear values, replace file inputs, then reindex
        $(document).on('click', '.add-unit', function() {
            let originalRow = $('.unit_list:first');
            let clonedRow = originalRow.clone();

            // Clear basic inputs/selects/textareas
            clonedRow.find('input[type="text"], input[type="number"], textarea').val('');
            clonedRow.find('select').each(function() {
                $(this).prop('selectedIndex', 0);
            });

            // Replace file inputs with fresh ones (can't clone file values)
            clonedRow.find('input[type="file"]').each(function() {
                var field = $(this).attr('data-field') || 'arrangement_photos';
                var multiple = $(this).prop('multiple') ? ' multiple' : '';
                var accept = $(this).attr('accept') ? ' accept="' + $(this).attr('accept') + '"' : '';
                var newFile = $('<input type="file" class="form-control" ' + multiple + accept + ' data-field="' + field + '">');
                $(this).replaceWith(newFile);
            });

            // Hide access code fields for the new unit (since opening type will be reset)
            clonedRow.find('.access-code-fields').hide();

            // Append clone to results area
            $('.unit_list_results').append(clonedRow).append('<hr class="mt-2 mb-4 border border-secondary">');

            // Reindex names for all units
            reindexAllUnits();

            // Trigger opening-type change on the new clone to ensure access block visibility is correct
            var $lastUnit = $('.unit_list').last();
            $lastUnit.find('.opening-type').trigger('change');

            // If you use select2 on selects, re-init here (safe to call conditionally)
            if ($.fn.select2) {
                $('.select2').select2();
            }
        });

        // Remove unit: remove the last appended unit (keeps initial one if only one left)
        $(document).on('click', '.remove-service', function() {
            if ($('.unit_list').length > 1) {
                // Prefer to remove the last .unit_list inside unit_list_results if present
                var $lists = $('.unit_list');
                var $last = $lists.last();
                // If last is the original first and results empty, do nothing
                if ($last.index() !== $('.unit_list:first').index() || $('.unit_list_results').children().length > 0) {
                    $last.next('hr').remove();
                    $last.remove();
                }
                reindexAllUnits();
            }
        });

        // Ensure initial reindex on page load
        reindexAllUnits();
        toggleRemoveServiceButton();
    });
</script>

@endpush

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item">
    <a href="{{ route('property.index') }}">{{ __('Property') }}</a>
</li>
<li class="breadcrumb-item active"><a href="#">{{ __('Create') }}</a>
</li>
@endsection

@section('content')
{{ Form::open(['url' => 'property', 'method' => 'post', 'enctype' => 'multipart/form-data', 'id' => 'property_form']) }}
<div class="row mt-4">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header pb-0">
                <ul class="nav nav-tabs profile-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="profile-tab-1" data-bs-toggle="tab" href="#profile-1"
                            role="tab" aria-selected="true">
                            <i class="material-icons-two-tone me-2">info</i>
                            {{ __('Property Details') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="profile-tab-2" data-bs-toggle="tab" href="#profile-2" role="tab"
                            aria-selected="true">
                            <i class="material-icons-two-tone me-2">image</i>
                            {{ __('Property Images') }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="profile-tab-3" data-bs-toggle="tab" href="#profile-3" role="tab"
                            aria-selected="true">
                            <i class="material-icons-two-tone me-2">layers</i>
                            {{ __('Unit') }}
                        </a>
                    </li>

                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane active show" id="profile-1" role="tabpanel" aria-labelledby="profile-tab-1">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card border">
                                    <div class="card-header">
                                        <h5> {{ __('Add Property Details') }}</h5>
                                    </div>
                                    <div class="card-body propertyDetails">
                                        <div class="row">
                                        <div class="col-sm-4">
                                                <div class="mb-3">
                                                    <div class="form-group">
                                                        {{ Form::label('property_type', __('Tipo locazione'), ['class' => 'form-label']) }}
                                                        {{ Form::select('property_type', $propertyTypes, null, ['class' => 'form-control basic-select', 'required' => 'required', 'id' => 'property_type_select']) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-sm-4">
                                                <div class="mb-3">
                                                    <div class="form-group">
                                                        {{ Form::label('name', __('Property Name'), ['class' => 'form-label']) }}
                                                        {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Property Name'), 'required' => 'required']) }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="mb-3">
                                                    <div class="form-group">
                                                        {{ Form::label('thumbnail', __('Thumbnail Image'), ['class' => 'form-label']) }}
                                                        {{ Form::file('thumbnail', ['class' => 'form-control', 'accept' => 'image/jpeg,image/png', 'id' => 'thumbnail']) }}
                                                        <small class="text-muted d-block mt-1">Only add JPEG and PNG files.</small>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-sm-3">
                                                <div class="mb-3">
                                                    <div class="form-group">
                                                        {{ Form::label('country', __('Country'), ['class' => 'form-label']) }}
                                                        {{ Form::text('country', null, ['class' => 'form-control', 'placeholder' => __('Enter Property Country'), 'required' => 'required']) }}
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="mb-3">
                                                    <div class="form-group">
                                                        {{ Form::label('state', __('State'), ['class' => 'form-label']) }}
                                                        {{ Form::text('state', null, ['class' => 'form-control', 'placeholder' => __('Enter Property State'), 'required' => 'required']) }}
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="mb-3">
                                                    <div class="form-group">
                                                        {{ Form::label('city', __('City'), ['class' => 'form-label']) }}
                                                        {{ Form::text('city', null, ['class' => 'form-control', 'placeholder' => __('Enter Property City'), 'required' => 'required']) }}
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="mb-3">
                                                    <div class="form-group">
                                                        {{ Form::label('zip_code', __('Zip Code'), ['class' => 'form-label']) }}
                                                        {{ Form::text('zip_code', null, ['class' => 'form-control', 'placeholder' => __('Enter Property Zip Code'), 'required' => 'required']) }}
                                                    </div>

                                                </div>
                                            </div>
                                            
                                            <div class="col-sm-12">
                                                {{-- <div class="mb-3">
                                                <div class="form-group">
                                                    {{ Form::label('description', __('Property Description'), ['class' => 'form-label']) }}
                                                {{ Form::textarea('description', null, [
                                                        'class' => 'form-control', 
                                                        'rows' => 4, 
                                                        'placeholder' => __('Enter Property Description'),
                                                        'required' => 'required'
                                                    ]) }}
                                            </div>
                                        </div> --}}
                                    </div>

                                    {{-- Address Field --}}
                                    <div class="col-sm-12">
                                        <div class="mb-3">
                                            <div class="form-group">
                                                {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}
                                                {{ Form::textarea('address', null, [
                                                    'class' => 'form-control',
                                                    'rows' => 3,
                                                    'placeholder' => __('Enter Property Address'),
                                                    'required' => 'required'
                                                ]) }}
                                            </div>
                                        </div>
                                    </div>

                                

                                    {{-- Floor Field --}}
                                    <div class="col-sm-4">
                                        <div class="mb-3">
                                            <div class="form-group">
                                                {{ Form::label('piano', __('Floor'), ['class' => 'form-label']) }}
                                                {{ Form::text('piano', null, ['class' => 'form-control conditional-field', 'placeholder' => __('Enter Floor')]) }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Staircase Field --}}
                                    <div class="col-sm-4">
                                        <div class="mb-3">
                                            <div class="form-group">
                                                {{ Form::label('staircase', __('Staircase'), ['class' => 'form-label']) }}
                                                {{ Form::text('staircase', null, ['class' => 'form-control conditional-field', 'placeholder' => __('Enter Staircase')]) }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Opening Type Field --}}
                                    <div class="col-sm-4">
                                        <div class="mb-3">
                                            <div class="form-group">
                                                {{ Form::label('opening_type', __('Opening Type'), ['class' => 'form-label']) }}
                                                {{ Form::select('opening_type', ['' => __('Select Opening Type'), 'key' => __('Key'), 'code' => __('Code')], null, ['class' => 'form-control conditional-field', 'id' => 'property_opening_type_select']) }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Name on Doorbell Field --}}
                                    <div class="col-sm-4">
                                        <div class="mb-3">
                                            <div class="form-group">
                                                {{ Form::label('sign_detail', __('Name on the Doorbell'), ['class' => 'form-label']) }}
                                                {{ Form::text('sign_detail', null, ['class' => 'form-control conditional-field', 'placeholder' => __('Enter Name on the Doorbell')]) }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Internal Access Description Field --}}
                                    <div class="col-sm-12">
                                        <div class="mb-3">
                                            <div class="form-group">
                                                {{ Form::label('access_other', __('Internal Access Description'), ['class' => 'form-label']) }}
                                                {{ Form::textarea('access_other', null, [
                                                    'class' => 'form-control conditional-field',
                                                    'rows' => 3,
                                                    'placeholder' => __('Enter Internal Access Description')
                                                ]) }}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Opening Type Code Fields (initially hidden) --}}
                                    <div id="property-code-fields" class="d-none">
                                        <div class="row">
                                            <div class="col-sm-6 mb-3">
                                                <div class="form-group">
                                                    {{ Form::label('street_code', __('Street Opening Code'), ['class' => 'form-label']) }}
                                                    {{ Form::text('street_code', null, [
                                                        'class' => 'form-control',
                                                        'placeholder' => __('Enter street opening code')
                                                    ]) }}
                                                </div>
                                            </div>

                                            <div class="col-sm-6 mb-3">
                                                <div class="form-group">
                                                    {{ Form::label('door_code', __('Door Opening Code'), ['class' => 'form-label']) }}
                                                    {{ Form::text('door_code', null, [
                                                        'class' => 'form-control',
                                                        'placeholder' => __('Enter door opening code')
                                                    ]) }}
                                                </div>
                                            </div>

                                            <div class="col-sm-6 mb-3">
                                                <div class="form-group">
                                                    {{ Form::label('key_description', __('Key Description'), ['class' => 'form-label']) }}
                                                    {{ Form::text('key_description', null, [
                                                        'class' => 'form-control',
                                                        'placeholder' => __('Describe the key or entry details')
                                                    ]) }}
                                                </div>
                                            </div>

                                            <div class="col-sm-6 mb-3">
                                                <div class="form-group">
                                                    {{ Form::label('access_other', __('Other Access Information'), ['class' => 'form-label']) }}
                                                    {{ Form::text('access_other', null, [
                                                        'class' => 'form-control',
                                                        'placeholder' => __('Enter any other access instructions')
                                                    ]) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-end mt-3">
                    <button type="button" class="btn btn-secondary btn-rounded nextButton"
                        data-next-tab="#profile-2" id="firstNextButton">
                        {{ __('Next') }}
                    </button>


                </div>
            </div>


            
            <div class="tab-pane" id="profile-2" role="tabpanel" aria-labelledby="profile-tab-2">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card border">
                            <div class="card-header">
                                {{ Form::label('demo-upload', __('Add Property Images'), ['class' => 'form-label']) }}
                            </div>
                            <div class="card-body ImageTab">
                                <div class="row">
                                    <div class="dropzone needsclick" id='demo-upload' action="#">
                                        <div class="dz-message needsclick">
                                            <div class="upload-icon"><i class="fa fa-cloud-upload"></i></div>
                                            <h3>{{ __('Drop files here or click to upload.') }}</h3>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-1">Only add JPEG and PNG files.</small>
                                    <p id="dropzone-error" class="text-danger mt-2" style="display: none;">
                                    </p>
                                    <div class="preview-dropzon" style="display: none;">
                                        <div class="dz-preview dz-file-preview">
                                            <div class="dz-image"><img data-dz-thumbnail="" src=""
                                                    alt=""></div>
                                            <div class="dz-details">
                                                <div class="dz-size"><span data-dz-size=""></span></div>
                                                <div class="dz-filename"><span data-dz-name=""></span></div>
                                            </div>
                                            <div class="dz-progress"><span class="dz-upload"
                                                    data-dz-uploadprogress=""> </span></div>
                                            <div class="dz-success-mark"><i class="fa fa-check"
                                                    aria-hidden="true"></i></div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-end mt-3">
                    <button type="button" class="btn btn-secondary btn-rounded nextButton"
                        data-next-tab="#profile-3" id="imageNext">
                        {{ __('Next') }}
                    </button>
                </div>
            </div>
            <div class="tab-pane" id="profile-3" role="tabpanel" aria-labelledby="profile-tab-3">
                <div class="card border">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>{{ __('Add Unit') }}</h5>
                        <button type="button" class="btn btn-danger btn-sm remove-service" style="display:none;">
                            {{ __('Remove Unit') }}
                        </button>
                    </div>

                    <div class="card-body">
                        {{-- initial unit (index 0) --}}
                        <div class="row unit_list" data-index="0">
                            <div class="form-group col-md-4">
                                {{ Form::label('units[0][unitname]', __('Name'), ['class' => 'form-label', 'id' => 'unit-name-label-0']) }}
                                {{ Form::text('units[0][unitname]', null, ['class' => 'form-control', 'data-field' => 'unitname', 'placeholder' => __('Enter unit name'), 'required' => 'required']) }}
                            </div>

                            {{-- Bedroom Type Field (for BNB/HOTEL) --}}
                            <div class="form-group col-md-2 bnb-hotel-only" style="display:none;">
                                {{ Form::label('units[0][bedroom_type]', __('Bedroom Type'), ['class' => 'form-label']) }}
                                {{ Form::select('units[0][bedroom_type]', ['' => __('Select Type'), 'double' => __('Double'), 'triple' => __('Triple'), 'quadruple' => __('Quadruple'), 'quintuple' => __('Quintuple')], null, ['class' => 'form-control', 'data-field' => 'bedroom_type', 'required' => 'required']) }}
                            </div>

                            <div class="form-group col-md-2 bnb-hotel-hide">
                                {{ Form::label('units[0][bedroom]', __('Bedroom'), ['class' => 'form-label']) }}
                                {{ Form::number('units[0][bedroom]', null, ['class' => 'form-control', 'data-field' => 'bedroom', 'placeholder' => __('Enter number of bedroom'), 'required' => 'required']) }}
                            </div>

                            <div class="form-group col-md-2">
                                {{ Form::label('units[0][double_beds]', __('Double Beds'), ['class' => 'form-label']) }}
                                {{ Form::number('units[0][double_beds]', null, ['class' => 'form-control', 'data-field' => 'double_beds', 'placeholder' => __('Enter number of double beds')]) }}
                            </div>

                            <div class="form-group col-md-2">
                                {{ Form::label('units[0][single_beds]', __('Single Beds'), ['class' => 'form-label']) }}
                                {{ Form::number('units[0][single_beds]', null, ['class' => 'form-control', 'data-field' => 'single_beds', 'placeholder' => __('Enter number of single beds')]) }}
                            </div>

                            <div class="form-group col-md-2">
                                {{ Form::label('units[0][sofa_beds]', __('Sofa Beds (Count)'), ['class' => 'form-label']) }}
                                {{ Form::number('units[0][sofa_beds]', null, ['class' => 'form-control', 'data-field' => 'sofa_beds', 'placeholder' => __('Enter number of sofa beds')]) }}
                            </div>

                            {{-- <div class="form-group col-md-2">
                    {{ Form::label('units[0][balcony]', __('Balcony (Count)'), ['class' => 'form-label']) }}
                            {{ Form::number('units[0][balcony]', null, ['class' => 'form-control', 'data-field' => 'balcony', 'placeholder' => __('Enter number of balconies')]) }}
                        </div> --}}


                        <div class="form-group col-md-2 bnb-hotel-hide">
                            {{ Form::label('units[0][kitchen]', __('Kitchen'), ['class' => 'form-label']) }}
                            {{ Form::select('units[0][kitchen]', ['yes' => 'Yes', 'no' => 'No'], null, ['class' => 'form-control', 'data-field' => 'kitchen', 'required' => 'required']) }}
                        </div>

                        <div class="form-group col-md-2">
                            {{ Form::label('units[0][baths]', __('Bath'), ['class' => 'form-label']) }}
                            {{ Form::number('units[0][baths]', null, ['class' => 'form-control', 'data-field' => 'baths', 'placeholder' => __('Enter number of bath'), 'required' => 'required']) }}
                        </div>

                        <div class="col-sm-4">
                            <div class="mb-3">
                                <div class="form-group">
                                    {{ Form::label('units[0][balcony]', __('Balcony'), ['class' => 'form-label']) }}
                                    {{ Form::select('units[0][balcony]', ['yes' => 'Yes', 'no' => 'No'], null, ['class' => 'form-control', 'data-field' => 'balcony', 'required' => 'required']) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4 holiday-home-hide">
                            <div class="mb-3">
                                <div class="form-group">
                                    {{ Form::label('units[0][opening_type]', __('Opening Type'), ['class' => 'form-label']) }}
                                    {{ Form::select('units[0][opening_type]', ['key' => 'Key', 'code' => 'Code'], null, ['class' => 'form-control opening-type', 'data-field' => 'opening_type']) }}
                                </div>
                            </div>
                        </div>

                        {{-- Access Code Fields (initially hidden) --}}
                        <div class="access-code-fields holiday-home-hide" style="display:none;">
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    {{ Form::label('units[0][street_opening_code]', __('Street Opening Code'), ['class' => 'form-label']) }}
                                    {{ Form::text('units[0][street_opening_code]', null, [
                                'class' => 'form-control',
                                'data-field' => 'street_opening_code',
                                'placeholder' => 'Enter street opening code'
                            ]) }}
                                </div>

                                <div class="col-sm-6 mb-3">
                                    {{ Form::label('units[0][door_opening_code]', __('Door Opening Code'), ['class' => 'form-label']) }}
                                    {{ Form::text('units[0][door_opening_code]', null, [
                                'class' => 'form-control',
                                'data-field' => 'door_opening_code',
                                'placeholder' => 'Enter door opening code'
                            ]) }}
                                </div>

                                <div class="col-sm-6 mb-3">
                                    {{ Form::label('units[0][key_description]', __('Key Description'), ['class' => 'form-label']) }}
                                    {{ Form::text('units[0][key_description]', null, [
                                'class' => 'form-control',
                                'data-field' => 'key_description',
                                'placeholder' => 'Describe the key or entry details'
                            ]) }}
                                </div>

                                <div class="col-sm-6 mb-3">
                                    {{ Form::label('units[0][other_access_information]', __('Other Access Information'), ['class' => 'form-label']) }}
                                    {{ Form::text('units[0][other_access_information]', null, [
                                'class' => 'form-control',
                                'data-field' => 'other_access_information',
                                'placeholder' => 'Enter any other access instructions'
                            ]) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4 holiday-home-hide">
                            <div class="mb-3">
                                <div class="form-group">
                                    {{ Form::label('units[0][piano]', __('Floor'), ['class' => 'form-label']) }}
                                    {{ Form::text('units[0][piano]', null, ['class' => 'form-control', 'data-field' => 'piano', 'placeholder' => __('Enter Floor')]) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4 holiday-home-hide bnb-hotel-hide">
                            <div class="mb-3">
                                <div class="form-group">
                                    {{ Form::label('units[0][staircase]', __('Staircase'), ['class' => 'form-label']) }}
                                    {{ Form::text('units[0][staircase]', null, ['class' => 'form-control', 'data-field' => 'staircase', 'placeholder' => __('Enter Staircase')]) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12 holiday-home-hide">
                            <div class="mb-3">
                                <div class="form-group">
                                    {{ Form::label('units[0][access_description]', __('Access Description'), ['class' => 'form-label']) }}
                                    {{ Form::textarea('units[0][access_description]', null, [
                                'class' => 'form-control',
                                'rows' => 3,
                                'data-field' => 'access_description',
                                'placeholder' => __('Enter Access Description'),
                                'required' => 'required',
                            ]) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12 holiday-home-hide bnb-hotel-hide">
                            <div class="mb-3">
                                <div class="form-group">
                                    {{ Form::label('units[0][sign_detail]', __('Plate/Identifying Detail'), ['class' => 'form-label']) }}
                                    {{ Form::text('units[0][sign_detail]', null, ['class' => 'form-control', 'data-field' => 'sign_detail', 'placeholder' => __('Enter sign on door or other identifying detail')]) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-12 bnb-hotel-hide">
                            <div class="mb-3">
                                <div class="form-group">
                                    {{ Form::label('units[0][description]', __('Description'), ['class' => 'form-label']) }}
                                    {{ Form::textarea('units[0][description]', null, ['class' => 'form-control', 'rows' => 4, 'data-field' => 'description', 'placeholder' => __('Enter Property Description')]) }}
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-12">
                            {{ Form::label('units[0][notes]', __('Notes'), ['class' => 'form-label']) }}
                            {{ Form::textarea('units[0][notes]', null, ['class' => 'form-control', 'rows' => 3, 'data-field' => 'notes', 'placeholder' => __('Enter notes')]) }}
                        </div>

                        <div class="form-group col-md-12">
                            {{ Form::label('units[0][arrangement_photos][]', __('Photo Settings (Bed/Towel Arrangement)'), ['class' => 'form-label']) }}
                            <input type="file" name="units[0][arrangement_photos][]" class="form-control" data-field="arrangement_photos" multiple accept="image/jpeg,image/png">
                            <small class="text-muted d-block mt-1">Only add JPEG and PNG files.</small>
                        </div>
                    </div>

                    <hr class="mt-2 mb-4 border border-secondary">
                    <div class="unit_list_results"></div>

                    <div class="col-lg-12 mb-2 text-end">
                        <button type="button" class="btn btn-secondary btn-md add-unit">
                            {{ __('Add Unit') }}
                        </button>
                    </div>

                </div>
            </div>

            <div class="col-lg-12 mb-2">
                <div class="group-button text-end">
                    {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded nextButton', 'id' => 'property-submit', 'type' => 'button']) }}
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>
{{ Form::close() }}
@endsection
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const nextButton = document.getElementById('firstNextButton');
        const submitButton = document.getElementById('property-submit');
        const imageNext = document.getElementById('imageNext');

        // B&B dynamic unit section
        const propertyTypeMain = document.getElementById('property_type_main');
        const bnbUnitsSection = document.getElementById('bnb-units-section');
        if (propertyTypeMain && bnbUnitsSection) {
            function toggleBnbUnitsSection() {
                // Check for B&B or similar in the selected option's text
                const selectedOption = propertyTypeMain.options[propertyTypeMain.selectedIndex];
                if (selectedOption && selectedOption.text.toLowerCase().includes('bnb')) {
                    bnbUnitsSection.classList.remove('d-none');
                } else {
                    bnbUnitsSection.classList.add('d-none');
                }
            }
            propertyTypeMain.addEventListener('change', toggleBnbUnitsSection);
            // Run on page load in case of old value
            toggleBnbUnitsSection();
        }

        // Opening type dynamic fields for Property tab
        const propertyOpeningTypeSelect = document.getElementById('property_opening_type_select');
        const propertyCodeFields = document.getElementById('property-code-fields');
        if (propertyOpeningTypeSelect) {
            propertyOpeningTypeSelect.addEventListener('change', function() {
                if (this.value === 'code') {
                    propertyCodeFields.classList.remove('d-none');
                } else {
                    // Hide code fields for 'key' or empty selection
                    propertyCodeFields.classList.add('d-none');
                }
            });
        }

        // Opening type dynamic fields (kept for any global opening toggles)
        const openingTypeSelect = document.getElementById('opening_type_select');
        const codeFields = document.getElementById('code-fields');
        const keyFields = document.getElementById('key-fields');
        if (openingTypeSelect) {
            openingTypeSelect.addEventListener('change', function() {
                if (this.value === 'code') {
                    codeFields.classList.remove('d-none');
                    keyFields.classList.add('d-none');
                } else if (this.value === 'key') {
                    keyFields.classList.remove('d-none');
                    codeFields.classList.add('d-none');
                } else {
                    codeFields.classList.add('d-none');
                    keyFields.classList.add('d-none');
                }
            });
        }

        // Property Type - Hide/Show unit fields based on type
        const propertyTypeSelect = document.getElementById('property_type_select');
        function togglePropertyTypeFields() {
            if (!propertyTypeSelect) return;
            
            const selectedOption = propertyTypeSelect.options[propertyTypeSelect.selectedIndex];
            const selectedText = selectedOption ? selectedOption.text.toLowerCase() : '';
            
            const isHolidayHome = selectedText.includes('casa vacanze') || selectedText.includes('holiday home');
            const isBnbHotel = selectedText.includes('affittacamere') || selectedText.includes('bnb') || 
                               selectedText.includes('guesthouse') || selectedText.includes('hotel');
            
            // Find all unit lists (including dynamically added ones)
            const allUnitLists = document.querySelectorAll('.unit_list');
            
            allUnitLists.forEach(function(unitList, index) {
                // Handle Holiday Home fields
                const holidayHomeFields = unitList.querySelectorAll('.holiday-home-hide');
                holidayHomeFields.forEach(function(field) {
                    if (isHolidayHome) {
                        field.style.display = 'none';
                        // Remove required attribute from access_description when hidden
                        const accessDesc = field.querySelector('[data-field="access_description"]');
                        if (accessDesc) {
                            accessDesc.removeAttribute('required');
                        }
                    } else {
                        field.style.display = '';
                        // Restore required attribute for access_description when shown (if not BNB/HOTEL)
                        if (!isBnbHotel) {
                            const accessDesc = field.querySelector('[data-field="access_description"]');
                            if (accessDesc && field.classList.contains('col-sm-12')) {
                                const label = field.querySelector('label');
                                if (label && label.textContent.includes('Access Description')) {
                                    accessDesc.setAttribute('required', 'required');
                                }
                            }
                        }
                    }
                });
                
                // Handle BNB/HOTEL fields
                const bnbHotelHideFields = unitList.querySelectorAll('.bnb-hotel-hide');
                bnbHotelHideFields.forEach(function(field) {
                    if (isBnbHotel) {
                        field.style.display = 'none';
                        // Remove required attributes when hidden
                        const requiredFields = field.querySelectorAll('[required]');
                        requiredFields.forEach(function(reqField) {
                            reqField.removeAttribute('required');
                        });
                    } else {
                        field.style.display = '';
                        // Restore required for bedroom and kitchen when shown (if not Holiday Home)
                        if (!isHolidayHome) {
                            const bedroomField = field.querySelector('[data-field="bedroom"]');
                            const kitchenField = field.querySelector('[data-field="kitchen"]');
                            if (bedroomField && field.querySelector('label') && field.querySelector('label').textContent.includes('Bedroom')) {
                                bedroomField.setAttribute('required', 'required');
                            }
                            if (kitchenField) {
                                kitchenField.setAttribute('required', 'required');
                            }
                        }
                    }
                });
                
                // Show Bedroom Type field for BNB/HOTEL
                const bnbHotelOnlyFields = unitList.querySelectorAll('.bnb-hotel-only');
                bnbHotelOnlyFields.forEach(function(field) {
                    if (isBnbHotel) {
                        field.style.display = '';
                    } else {
                        field.style.display = 'none';
                        // Remove required when hidden
                        const requiredFields = field.querySelectorAll('[required]');
                        requiredFields.forEach(function(reqField) {
                            reqField.removeAttribute('required');
                        });
                    }
                });
                
                // Make Floor and Opening Type required for BNB/HOTEL
                if (isBnbHotel) {
                    const floorField = unitList.querySelector('[data-field="piano"]');
                    const openingTypeField = unitList.querySelector('[data-field="opening_type"]');
                    const accessDescField = unitList.querySelector('[data-field="access_description"]');
                    
                    if (floorField) {
                        floorField.setAttribute('required', 'required');
                    }
                    if (openingTypeField) {
                        openingTypeField.setAttribute('required', 'required');
                    }
                    if (accessDescField) {
                        accessDescField.setAttribute('required', 'required');
                    }
                } else {
                    // Remove required if not BNB/HOTEL (unless it's already required for other reasons)
                    const floorField = unitList.querySelector('[data-field="piano"]');
                    const openingTypeField = unitList.querySelector('[data-field="opening_type"]');
                    
                    if (floorField && !isHolidayHome) {
                        // Floor is optional for regular properties
                        floorField.removeAttribute('required');
                    }
                    if (openingTypeField && !isHolidayHome) {
                        // Opening type is optional for regular properties
                        openingTypeField.removeAttribute('required');
                    }
                }
                
                // Update Name label
                const nameLabel = unitList.querySelector('label[for*="unitname"]');
                if (nameLabel) {
                    if (isHolidayHome) {
                        nameLabel.textContent = 'Structure Name';
                    } else if (isBnbHotel) {
                        nameLabel.textContent = 'Room Name';
                    } else {
                        nameLabel.textContent = 'Name';
                    }
                }
            });
        }
        
        if (propertyTypeSelect) {
            propertyTypeSelect.addEventListener('change', togglePropertyTypeFields);
            // Run on page load in case of old value
            togglePropertyTypeFields();
        }

        // Also trigger when new units are added
        $(document).on('click', '.add-unit', function() {
            setTimeout(function() {
                togglePropertyTypeFields();
            }, 100);
        });

        if (!nextButton || !imageNext || !submitButton) return;

        // STEP 1: Property Details Tab Validation with conditional logic
        nextButton.addEventListener('click', function(e) {
            // Check if any conditional fields are filled
            const conditionalFields = document.querySelectorAll('.propertyDetails .conditional-field');
            let hasConditionalFields = false;
            
            conditionalFields.forEach(function(field) {
                if (field.value && field.value.trim()) {
                    hasConditionalFields = true;
                }
            });

            // If conditional fields are present, remove required from standard fields
            const standardRequiredFields = document.querySelectorAll('.propertyDetails [required]');
            let fieldsToValidate = [];
            
            if (hasConditionalFields) {
                // Skip validation for standard required fields if conditional fields are present
                fieldsToValidate = [];
            } else {
                // Validate all required fields normally
                fieldsToValidate = Array.from(standardRequiredFields);
            }

            let isValid = true;
            if (fieldsToValidate.length > 0) {
                isValid = validateRequiredFields(fieldsToValidate);
            }

            if (!isValid) {
                e.preventDefault();
                e.stopImmediatePropagation();
            }
        });

        // STEP 2: Image Upload Validation
        imageNext.addEventListener('click', function(e) {
            const myDropzone = Dropzone.forElement('#demo-upload');
            const dropzoneError = document.getElementById('dropzone-error');

            if (!myDropzone || myDropzone.files.length === 0) {
                e.preventDefault();
                e.stopImmediatePropagation();

                if (dropzoneError) {
                    dropzoneError.textContent = 'Please upload at least one image before proceeding.';
                    dropzoneError.style.display = 'block';
                }

                return;
            }
            if (dropzoneError) {
                dropzoneError.textContent = '';
                dropzoneError.style.display = 'none';
            }
        });

        // STEP 3: Final Submit Button Validation with conditional logic
        submitButton.addEventListener('click', function(e) {
            const form = submitButton.closest('form');
            
            // Check if any conditional fields in Property Details are filled
            const conditionalFields = document.querySelectorAll('.propertyDetails .conditional-field');
            let hasConditionalFields = false;
            
            conditionalFields.forEach(function(field) {
                if (field.value && field.value.trim()) {
                    hasConditionalFields = true;
                }
            });

            // Get all required fields
            const allRequiredFields = form.querySelectorAll('[required]');
            let fieldsToValidate = [];
            
            if (hasConditionalFields) {
                // Skip validation for Property Details required fields if conditional fields are present
                const propertyDetailsRequired = document.querySelectorAll('.propertyDetails [required]');
                const propertyDetailsRequiredArray = Array.from(propertyDetailsRequired);
                const allRequiredArray = Array.from(allRequiredFields);
                
                // Filter out Property Details required fields
                fieldsToValidate = allRequiredArray.filter(function(field) {
                    return !propertyDetailsRequiredArray.includes(field);
                });
            } else {
                // Validate all required fields normally
                fieldsToValidate = Array.from(allRequiredFields);
            }

            let isValid = true;
            if (fieldsToValidate.length > 0) {
                isValid = validateRequiredFields(fieldsToValidate);
            }

            if (!isValid) {
                // highlight which unit fields are missing for developer debugging
                console.warn('Validation failed. Missing required fields:', Array.from(fieldsToValidate).filter(f => !f.value || !String(f.value).trim()).map(f => f.name));
                e.preventDefault();
                e.stopImmediatePropagation();
                $('#property-submit').attr('disabled', false);
            } else {
                // allow default handler above to run AJAX click bound earlier
                // trigger the existing click handler that builds FormData -> already bound at top
            }
        });

        function validateRequiredFields(fields) {
            let valid = true;
            fields.forEach(function(field) {
                // Skip validation for hidden fields
                const fieldElement = field.closest('.holiday-home-hide');
                if (fieldElement && fieldElement.style.display === 'none') {
                    return; // Skip this field
                }
                
                // Also check if the field itself or its parent is hidden
                let isHidden = false;
                let element = field;
                while (element && element !== document.body) {
                    if (element.style && element.style.display === 'none') {
                        isHidden = true;
                        break;
                    }
                    element = element.parentElement;
                }
                
                if (isHidden) {
                    return; // Skip hidden fields
                }
                
                if (!field.value || !field.value.trim()) {
                    field.classList.add('is-invalid');
                    valid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            return valid;
        }
    });
</script>