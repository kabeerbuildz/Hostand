@extends('layouts.app')
@section('page-title')
    {{ __('Property Edit') }}
@endsection

@push('script-page')
    <script src="{{ asset('assets/js/vendors/dropzone/dropzone.js') }}"></script>
    <script>
        (function ($) {
            'use strict';

            // helper: toast wrapper (uses toastr if present, otherwise alert)
            function notify(type, message, title) {
                if (typeof toastr !== 'undefined') {
                    if (type === 'success') toastr.success(message, title || 'Success');
                    else if (type === 'error') toastr.error(message, title || 'Error');
                    else if (type === 'info') toastr.info(message, title || '');
                    else toastr.warning(message, title || '');
                } else {
                    // fallback
                    alert((title ? title + ': ' : '') + message);
                }
            }

            // Dropzone init (unchanged behavior)
            var dropzone;
            try {
                dropzone = new Dropzone('#demo-upload', {
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
            } catch (err) {
                console.warn('Dropzone not initialized:', err);
                dropzone = null;
            }

            // Ensure jQuery present
            if (typeof $ === 'undefined') {
                console.error('jQuery not found. Make sure jQuery is loaded before this script.');
                return;
            }

            // Update handler — submit via AJAX
            $(document).on('click', '#property-update', function (e) {
                e.preventDefault(); // prevent default form submission
                var $btn = $(this);
                $btn.attr('disabled', true);

                var fd = new FormData();
                // thumbnail file (single)
                var fileInput = document.getElementById('thumbnail');
                var thumbnailFile = fileInput && fileInput.files && fileInput.files.length ? fileInput.files[0] : null;

                // Append Dropzone files (if any)
                if ($('#demo-upload').length && $('#demo-upload')[0].dropzone) {
                    var dzFiles = $('#demo-upload')[0].dropzone.getAcceptedFiles();
                    $.each(dzFiles, function (key, f) {
                        fd.append('property_images[' + key + ']', f);
                    });
                }

                // Append thumbnail: if no file chosen, append empty string to keep key present
                fd.append('thumbnail', thumbnailFile || '');

                // Append serialized form inputs
                var other_data = $('#property_form').serializeArray();
                $.each(other_data, function (key, input) {
                    fd.append(input.name, input.value);
                });

                // Method spoof to PUT for update route
                fd.append('_method', 'PUT');

                var ajaxUrl = "{{ route('property.update', $property->id) }}";
                console.log('Sending update to:', ajaxUrl);

                $.ajax({
                    url: ajaxUrl,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: fd,
                    contentType: false,
                    processData: false,
                    type: 'POST',
                    success: function (data) {
                        if (data && data.status && data.status === 'success') {
                            setTimeout(function () {
                                // reload current page
                                location.reload();
                            }, 100);
                        } else {
                            var message = (data && data.msg) ? data.msg : '{{ __("An error occurred while updating.") }}';
                            notify('error', message);
                        }
                    },
                    error: function (xhr) {
                        var message = 'Something went wrong';
                        if (xhr && xhr.responseJSON) {
                            // prefer msg then message then errors
                            message = xhr.responseJSON.msg || xhr.responseJSON.message || message;
                            if (!message && xhr.responseJSON.errors) {
                                // show first validation error
                                var errors = xhr.responseJSON.errors;
                                for (var k in errors) {
                                    if (errors[k] && errors[k].length) {
                                        message = errors[k][0];
                                        break;
                                    }
                                }
                            }
                        } else if (xhr && xhr.responseText) {
                            message = xhr.responseText;
                        }
                        notify('error', message);
                    },
                    complete: function () {
                        // always re-enable the button
                        $btn.attr('disabled', false);
                    },
                    timeout: 120000 // 2 minutes — safe for uploads
                });
            });

            // DELETE handler (thumbnail & property images)
            $(document).on('click', '.delete-image', function (e) {
                e.preventDefault();

                var $btn = $(this);
                var id = $btn.data('id');
                var type = $btn.data('type') || ''; // expected 'thumbnail' or 'property-image'

                if (!id) {
                    console.warn('Delete button missing data-id');
                    return;
                }

                if (!confirm("{{ __('Are you sure you want to delete this image?') }}")) {
                    return;
                }

                var url;
                if (type === 'thumbnail') {
                    url = "{{ url('/property/thumbnail') }}/" + id;
                } else {
                    // default to property image endpoint
                    url = "{{ url('/property/image') }}/" + id;
                }

                console.log('Delete request URL:', url, 'type:', type);

                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        _method: "DELETE",
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        console.log('Delete response:', response);

                        // Response could be { success: true, message: 'Deleted' } or { status: 'success', msg: 'Deleted' }
                        var success = (response && (response.success === true || response.status === 'success'));
                        var message = response.message || response.msg || '{{ __("Deleted") }}';

                        if (success) {
                            if (type === 'thumbnail') {
                                // remove thumbnail wrapper if present
                                var wrapper = ".thumbnail-wrapper-" + id;
                                if ($(wrapper).length) {
                                    $(wrapper).remove();
                                }
                                // optional: show fallback text where appropriate
                                var $thumbCardBody = $('.card-body').has(wrapper).first();
                                if ($thumbCardBody.length === 0) {
                                    // append fallback to first card-body (best-effort)
                                    $('.card-body').first().append('<p>{{ __('No thumbnail uploaded.') }}</p>');
                                }
                            } else {
                                // remove property image container
                                $('#image-' + id).remove();
                            }
                            notify('success', message);
                        } else {
                            notify('error', message || '{{ __("Could not delete image") }}');
                        }
                    },
                    error: function (xhr) {
                        var msg = 'Something went wrong';
                        try {
                            var json = xhr.responseJSON || JSON.parse(xhr.responseText);
                            msg = json.message || json.msg || xhr.responseText || msg;
                        } catch (e) {
                            msg = xhr.responseText || msg;
                        }
                        notify('error', msg);
                    }
                });
            });

            console.log('Property edit script initialized');
        })(jQuery);
    </script>
@endpush

@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{ route('property.index') }}">{{ __('Property') }}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{ __('Edit') }}</a>
        </li>
    </ul>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">

            {{ Form::model($property, ['route' => ['property.update', $property->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data', 'id' => 'property_form']) }}
            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="info-group">
                                <div class="form-group ">
                                    {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
                                    {{ Form::select('type', $types, null, ['class' => 'form-control hidesearch']) }}
                                </div>

                                <div class="form-group">
                                    {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Property Name')]) }}
                                </div>

                                <div class="form-group ">
                                    {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                                    {{ Form::textarea('description', null, ['class' => 'form-control', 'rows' => 8, 'placeholder' => __('Enter Property Description')]) }}
                                </div>

                                <div class="form-group">
                                    {{ Form::label('thumbnail', __('Thumbnail Image'), ['class' => 'form-label']) }}
                                    {{ Form::file('thumbnail', ['class' => 'form-control', 'id' => 'thumbnail']) }}
                                </div>

                                <div class="form-group">
                                    {{ Form::label('property_type', __('Tipo locazione'), ['class' => 'form-label']) }}
                                    {{ Form::select('property_type', $propertyTypes, null, ['class' => 'form-control basic-select', 'required' => 'required']) }}
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="info-group">
                                <div class="form-group">
                                    {{ Form::label('country', __('Country'), ['class' => 'form-label']) }}
                                    {{ Form::text('country', null, ['class' => 'form-control', 'placeholder' => __('Enter Property Country')]) }}
                                </div>
                                <div class="form-group">
                                    {{ Form::label('state', __('State'), ['class' => 'form-label']) }}
                                    {{ Form::text('state', null, ['class' => 'form-control', 'placeholder' => __('Enter Property State')]) }}
                                </div>
                                <div class="form-group">
                                    {{ Form::label('city', __('City'), ['class' => 'form-label']) }}
                                    {{ Form::text('city', null, ['class' => 'form-control', 'placeholder' => __('Enter Property City')]) }}
                                </div>
                                <div class="form-group">
                                    {{ Form::label('zip_code', __('Zip Code'), ['class' => 'form-label']) }}
                                    {{ Form::text('zip_code', null, ['class' => 'form-control', 'placeholder' => __('Enter Property Zip Code')]) }}
                                </div>
                                <div class="form-group ">
                                    {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}
                                    {{ Form::textarea('address', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter Property Address')]) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3 w-100">
                    <div class="card-header">
                        {{ __('Thumbnail Image') }}
                    </div>
                    <div class="card-body">
                        @if ($property->thumbnail)
                            <div class="col-md-3 mb-3 thumbnail-wrapper-{{ $property->thumbnail->id }}">
                                <div class="position-relative border rounded overflow-hidden">
                                    <img src="{{ asset('uploads/thumbnail/' . $property->thumbnail->image) }}"
                                         class="img-fluid" style="height:150px; width:100%; object-fit:cover;">
                                    <button type="button"
                                            class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 delete-image"
                                            data-id="{{ $property->thumbnail->id }}" data-type="thumbnail">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @else
                            <p>{{ __('No thumbnail uploaded.') }}</p>
                        @endif
                    </div>
                </div>

                <div class="card mt-3 w-100">
                    <div class="card-header">
                        {{ __('Property Images') }}
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @forelse($property->propertyImages as $image)
                                <div class="col-md-3 mb-3" id="image-{{ $image->id }}">
                                    <div class="position-relative border rounded overflow-hidden">
                                        <img src="{{ asset('uploads/property/' . $image->image) }}" class="img-fluid"
                                            style="height:150px; width:100%; object-fit:cover;">
                                        <button type="button"
                                            class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 delete-image"
                                            data-id="{{ $image->id }}" data-type="property-image">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <p>{{ __('No extra property images uploaded.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            {{ Form::label('demo-upload', __('Property Images'), ['class' => 'form-label']) }}
                        </div>
                        <div class="card-body">
                            <div class="dropzone needsclick" id='demo-upload' action="#">
                                <div class="dz-message needsclick">
                                    <div class="upload-icon"><i class="fa fa-cloud-upload"></i></div>
                                    <h3>{{ __('Drop files here or click to upload.') }}</h3>
                                </div>
                            </div>
                            <div class="preview-dropzon" style="display: none;">
                                <div class="dz-preview dz-file-preview">
                                    <div class="dz-image"><img data-dz-thumbnail="" src="" alt=""></div>
                                    <div class="dz-details">
                                        <div class="dz-size"><span data-dz-size=""></span></div>
                                        <div class="dz-filename"><span data-dz-name=""></span></div>
                                    </div>
                                    <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress=""> </span>
                                    </div>
                                    <div class="dz-success-mark"><i class="fa fa-check" aria-hidden="true"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12 mb-4">
                    <div class="group-button text-end">
                        {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary btn-rounded', 'id' => 'property-update']) }}
                    </div>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
@endsection
