@extends('layouts.app')
@section('page-title')
    {{ __('Property Edit') }}
@endsection

@push('script-page')
    <script src="{{ asset('assets/js/vendors/dropzone/dropzone.js') }}"></script>
    <script>
        // Dropzone init (unchanged)
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

        // Property update handler (uses FormData + method spoofing)
        (function ($) {
            'use strict';

            // confirm jQuery is available
            if (typeof $ === 'undefined') {
                console.error('jQuery not found. Make sure jQuery is loaded before this script.');
            } else {
                console.log('Update handler attached');
            }

            $('#property-update').on('click', function() {
                $('#property-update').attr('disabled', true);

                var fd = new FormData();
                var fileInput = document.getElementById('thumbnail');
                var file = fileInput && fileInput.files.length ? fileInput.files[0] : undefined;

                // Append dropzone files
                if ($('#demo-upload').length && $('#demo-upload')[0].dropzone) {
                    var files = $('#demo-upload')[0].dropzone.getAcceptedFiles();
                    $.each(files, function(key, f) {
                        fd.append('property_images[' + key + ']', f);
                    });
                }

                // Append thumbnail
                if (file === undefined) {
                    fd.append('thumbnail', '');
                } else {
                    fd.append('thumbnail', file);
                }

                // Append serialized inputs
                var other_data = $('#property_form').serializeArray();
                $.each(other_data, function(key, input) {
                    fd.append(input.name, input.value);
                });

                // Method spoof for PUT
                fd.append('_method', 'PUT');

                console.log('Sending update to:', "{{ route('property.update', $property->id) }}");

                $.ajax({
                    url: "{{ route('property.update', $property->id) }}",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: fd,
                    contentType: false,
                    processData: false,
                    type: 'POST', // using POST + _method=PUT
                    success: function(data) {
                        if (data.status == "success") {
                            $('#property-update').attr('disabled', true);
                            toastrs('success', data.msg, 'success');
                            var url = '{{ route('property.show', ':id') }}';
                            url = url.replace(':id', data.id);
                            setTimeout(function() {
                                window.location.href = url;
                            }, 1000);
                        } else {
                            toastrs('Error', data.msg, 'error');
                            $('#property-update').attr('disabled', false);
                        }
                    },
                    error: function(data) {
                        $('#property-update').attr('disabled', false);
                        if (data.error) {
                            toastrs('Error', data.error, 'error');
                        } else {
                            toastrs('Error', data, 'error');
                        }
                    },
                });
            });

            // ***** DELETE handler (thumbnail & property images) with debug logs *****
            $(document).on('click', '.delete-image', function(e) {
                e.preventDefault();

                console.log('Delete button clicked');

                var $btn = $(this);
                var id = $btn.data('id');
                var type = $btn.data('type') || ''; // expected: 'thumbnail' | 'property-image'
                if (!id) {
                    console.warn('Delete button missing data-id');
                    return;
                }

                if (!confirm("Are you sure you want to delete this image?")) return;

                var url;
                if (type === 'thumbnail') {
                    url = "{{ url('/property/thumbnail') }}/" + id;
                } else {
                    // default to property image
                    url = "{{ url('/property/image') }}/" + id;
                }

                console.log('Delete request URL:', url, 'type:', type);

                $.ajax({
                    url: url,
                    type: "POST", // POST + _method=DELETE
                    data: {
                        _method: "DELETE",
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        console.log('Delete response:', response);
                        if (response && response.success) {
                            if (type === 'thumbnail') {
                                var wrapper = ".thumbnail-wrapper-" + id;
                                if ($(wrapper).length) {
                                    $(wrapper).remove();
                                    // show fallback text in the thumbnail card-body
                                    var $thumbnailCard = $('.card').has('div.thumbnail-wrapper-' + id).first();
                                    if ($thumbnailCard.length) {
                                        $thumbnailCard.find('.card-body').first().append('<p>{{ __('No thumbnail uploaded.') }}</p>');
                                    } else {
                                        $('.card-body').first().append('<p>{{ __('No thumbnail uploaded.') }}</p>');
                                    }
                                }
                            } else {
                                $('#image-' + id).remove();
                            }

                            if (typeof toastr !== 'undefined') {
                                toastr.success(response.message || '{{ __("Deleted") }}');
                            } else {
                                alert(response.message || '{{ __("Deleted") }}');
                            }
                        } else {
                            alert(response.message || '{{ __("Could not delete image") }}');
                        }
                    },
                    error: function(xhr) {
                        console.error('Delete error:', xhr);
                        var msg = 'Something went wrong';
                        try {
                            var json = xhr.responseJSON || JSON.parse(xhr.responseText);
                            msg = json.message || xhr.responseText || msg;
                        } catch (e) {
                            msg = xhr.responseText || msg;
                        }
                        alert(msg);
                    }
                });
            });

            console.log('Delete handler attached');
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
                                    {{-- Added id="thumbnail" so JS can read the file input --}}
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

                <div class="card mt-3">
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

                <div class="card mt-3">
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
