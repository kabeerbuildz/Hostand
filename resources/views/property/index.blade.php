@extends('layouts.app')

@section('page-title')
    {{ __('Property') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('Property') }}</li>
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

    $('#property-submit').on('click', function() {
        $('#property-submit').attr('disabled', true);
        var fd = new FormData();
        var file = document.getElementById('thumbnail').files[0];

        var files = $('#demo-upload').get(0).dropzone.getAcceptedFiles();
        $.each(files, function(key, file) {
            fd.append('property_images[' + key + ']', $('#demo-upload')[0].dropzone.getAcceptedFiles()[key]);
        });
        fd.append('thumbnail', file);

        var other_data = $('#property_form').serializeArray();
        $.each(other_data, function(key, input) {
            fd.append(input.name, input.value);
        });

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
                if (data.status == "success") {
                    toastrs(data.status, data.msg, data.status);
                    var url = '{{ route('property.show', ':id') }}';
                    url = url.replace(':id', data.id);
                    setTimeout(() => {
                        window.location.href = url;
                    }, 1000);
                } else {
                    toastrs('Error', data.msg, 'error');
                    $('#property-submit').attr('disabled', false);
                }
            },
            error: function(data) {
                $('#property-submit').attr('disabled', false);
                toastrs('Error', data.responseJSON?.error || 'Something went wrong', 'error');
            },
        });
    });
</script>
@endpush

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="">
            <div class="card-header">
                <div class="row align-items-center g-2">
                    <div class="col">
                        <h5>{{ __('Property List') }}</h5>
                    </div>
                    @can('create property')
                        <div class="col-auto">
                            <a class="btn btn-secondary" href="{{ route('property.create') }}" data-size="md">
                                <i class="ti ti-circle-plus align-text-bottom"></i> {{ __('Create Property') }}
                            </a>
                        </div>
                    @endcan
                </div>
            </div>

            <div class="row mt-3">
                @foreach ($properties as $property)
@php
    // Default fallback image (absolute URL)
    $defaultImage = asset('uploads/whiteImage/download1.jpeg'); // make sure whiteimage.png is inside public/uploads/

    if (!empty($property->thumbnail) && !empty($property->thumbnail->image)) {
        $path = public_path('uploads/thumbnail/' . $property->thumbnail->image);

        if (file_exists($path)) {
            // Image exists in public/uploads/thumbnail
            $thumbnail = asset('uploads/thumbnail/' . $property->thumbnail->image);
        } else {
            // Image file missing → use default
            $thumbnail = $defaultImage;
        }
    } else {
        // No thumbnail set → use default
        $thumbnail = $defaultImage;
    }
@endphp

<div class="col-sm-6 col-md-4 col-xxl-3">
    <div class="card product-card">
        <div class="card-img-top">
            <img src="{{ $thumbnail }}" alt="{{ $property->name }}" class="img-prod" />
        </div>



                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <a href="@can('show property') {{ route('property.show', $property->id) }} @endcan"
                                       class="fw-semibold mb-0 text-truncate">
                                        <h4>{{ $property->name }}</h4>
                                    </a>
                                    @if (Gate::check('edit property') || Gate::check('delete property') || Gate::check('show property'))
                                        <div class="dropdown">
                                            <a class="dropdown-toggle text-primary opacity-50 arrow-none" href="#"
                                               data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="ti ti-dots f-16"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                {!! Form::open([
                                                    'method' => 'DELETE',
                                                    'route' => ['property.destroy', $property->id],
                                                    'id' => 'property-' . $property->id,
                                                ]) !!}
                                                @can('edit property')
                                                    <a class="dropdown-item" href="{{ route('property.edit', $property->id) }}">
                                                        <i class="material-icons-two-tone">edit</i> {{ __('Edit Property') }}
                                                    </a>
                                                @endcan
                                                @can('show property')
                                                    <a class="dropdown-item" href="{{ route('property.show', $property->id) }}">
                                                        <i class="material-icons-two-tone">remove_red_eye</i> {{ __('View property') }}
                                                    </a>
                                                @endcan
                                                @can('delete property')
                                                    <a class="dropdown-item confirm_dialog" href="#">
                                                        <i class="material-icons-two-tone">delete</i> {{ __('Delete Property') }}
                                                    </a>
                                                @endcan
                                                {!! Form::close() !!}
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-12 my-2">
                                    <button type="button" class="btn btn-light-secondary">
                                        <i class="material-icons-two-tone">ad_units</i> {{ $property->totalUnit() }} {{ __('Unit') }}
                                    </button>
                                    <button type="button" class="btn btn-light-secondary">
                                        <i class="material-icons-two-tone">meeting_room</i> {{ $property->totalRoom() }} {{ __('Rooms') }}
                                    </button>
                                    @php
                                        $maintenanceCount = $property->maintenanceRequests()->count();
                                        $pendingCount = $property->maintenanceRequests()->where('status', 'pending')->count();
                                    @endphp
                                    @if ($maintenanceCount > 0)
                                        <button type="button" class="btn btn-light-info">
                                            <i class="material-icons-two-tone">build</i> {{ $maintenanceCount }} {{ __('Services') }}
                                            @if ($pendingCount > 0)
                                                <span class="badge bg-warning ms-1">{{ $pendingCount }}</span>
                                            @endif
                                        </button>
                                    @endif
                                </div>

                                <p class="prod-content my-2 text-muted">
                                    {{ Str::limit($property->description, 200) }}
                                </p>

                                 <div class="d-flex align-items-center justify-content-between mt-3">
                                    <span class="badge bg-light-secondary" data-bs-toggle="tooltip"
                                          title="{{ __('Type') }}">{{ $property->locationType->title ?? 'Unknown' }}</span>
                                </div> 
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</div>
@endsection
