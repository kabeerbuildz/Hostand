<div class="modal-body">
    <div class="product-card">
        <div class="row align-items-center">

            {{-- Property --}}
            <div class="col-6">
                <p class="mb-1 mt-2"><b>{{ __('Property') }} :</b>
                    {{ !empty($maintenanceRequest->properties) ? $maintenanceRequest->properties->name : '-' }}
                </p>
            </div>

            {{-- Unit --}}
            <div class="col-6">
                <p class="mb-1 mt-2"><b>{{ __('Unit') }} :</b>
                    {{ !empty($maintenanceRequest->units) ? $maintenanceRequest->units->name : '-' }}
                </p>
            </div>

            {{-- Service Type --}}
            <div class="col-6">
                <p class="mb-1 mt-2"><b>{{ __('Service Type') }} :</b>
                    {{ !empty($maintenanceRequest->service_type) ? $maintenanceRequest->service_type : '-' }}
                </p>
            </div>

            {{-- Maintainer --}}
            <div class="col-6">
                <p class="mb-1 mt-2"><b>{{ __('Maintainer') }} :</b>
                    {{ !empty($maintenanceRequest->maintainers) ? $maintenanceRequest->maintainers->name : 'Unassigned' }}
                </p>
            </div>

            {{-- Arrival Date/Time --}}
           @php
        use Carbon\Carbon;

        $arrivalTime = !empty($maintenanceRequest->arrival_time) 
            ? Carbon::parse($maintenanceRequest->arrival_time)->format('d/m/Y h:i A') 
            : '-';
        @endphp

        <div class="col-6">
            <p class="mb-1 mt-2">
                <b>{{ __('Arrival Date/Time') }} :</b> {{ $arrivalTime }}
            </p>
        </div>


            {{-- Number of People --}}
            <div class="col-6">
                <p class="mb-1 mt-2"><b>{{ __('Number of People') }} :</b>
                    {{ !empty($maintenanceRequest->people_count) ? $maintenanceRequest->people_count : '-' }}
                </p>
            </div>

            {{-- Request Date --}}
            <div class="col-6">
                <p class="mb-1 mt-2"><b>{{ __('Request Date') }} :</b>
                    {{ dateFormat($maintenanceRequest->request_date) }}
                </p>
            </div>

            {{-- Status --}}
            <div class="col-6">
                <p class="mb-1 mt-2"><b>{{ __('Status') }} :</b>
                    @if ($maintenanceRequest->status == 'pending')
                        <span class="badge bg-light-warning">Pending</span>
                    @elseif($maintenanceRequest->status == 'in_progress')
                        <span class="badge bg-light-info">In Progress</span>
                    @else
                        <span class="badge bg-light-primary">Completed</span>
                    @endif
                </p>
            </div>

            {{-- Unit Photos --}}
            @if (!empty($maintenanceRequest->properties) && $maintenanceRequest->properties->propertyImages->count())
                <div class="col-12 mt-3">
                    <b>{{ __('Unit Photos') }} :</b>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        @foreach ($maintenanceRequest->properties->propertyImages as $image)
                            <img src="{{ asset('uploads/property/' . $image->image) }}" alt="Unit Photo"
                                class="img-thumbnail" style="width:120px; height:auto;">
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Notes / Ticket --}}
            <div class="col-12 mt-3">
                <p><b>{{ __('Notes / Ticket') }} :</b></p>
                <p class="border rounded p-2 bg-light">{{ $maintenanceRequest->notes ?? '-' }}</p>
            </div>

            {{-- Ticket Attachments --}}
            {{-- @if (!empty($maintenanceRequest->issue_attachment))
                <div class="col-12 mt-2">
                    <b>{{__('Ticket Attachments')}} :</b>
                    <a href="{{ asset(Storage::url('upload/issue_attachment')).'/'.$maintenanceRequest->issue_attachment }}" 
                       target="_blank">
                        <i class="fa fa-image"></i> View
                    </a>
                </div>
            @endif --}}

            {{-- Clockin / Clockout --}}
            @if (auth()->user()->type == 'operator')
                <div class="col-12 mt-4 text-center">
                    @if ($maintenanceRequest->status == 'pending')
                        <form action="{{ route('maintenance-request.clockin', $maintenanceRequest->id) }}"
                            method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">{{ __('Clock In (Start)') }}</button>
                        </form>
                    @elseif($maintenanceRequest->status == 'in_progress')
                        <form action="{{ route('maintenance-request.clockout', $maintenanceRequest->id) }}"
                            method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">{{ __('Clock Out (End)') }}</button>
                        </form>
                    @endif
                </div>
            @endif

            {{-- Super Admin: Work Session Info --}}
            @if (auth()->user()->type == 'super admin')
                <div class="col-12 mt-4">
                    <div class="card shadow-sm border-0 rounded">
                        <div class="card-header bg-primary text-white py-2">
                            <h6 class="mb-0">{{ __('Work Session Details') }}</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="row">
                                {{-- Started At --}}
                                <div class="col-md-4 col-12 mb-3">
                                    <p class="mb-1 text-muted"><b>{{ __('Started At') }}:</b></p>
                                    <p class="fw-semibold">
                                        {{ !empty($maintenanceRequest->started_at) ? $maintenanceRequest->started_at : '-' }}
                                    </p>
                                </div>

                                {{-- Ended At --}}
                                <div class="col-md-4 col-12 mb-3">
                                    <p class="mb-1 text-muted"><b>{{ __('Ended At') }}:</b></p>
                                    <p class="fw-semibold">
                                        {{ !empty($maintenanceRequest->ended_at) ? $maintenanceRequest->ended_at : '-' }}
                                    </p>
                                </div>

                                {{-- Hours Worked --}}
                                {{-- Hours Worked --}}
{{-- Hours Worked --}}
<div class="col-md-4 col-12 mb-3">
    <p class="mb-1 text-muted"><b>{{ __('Hours Worked') }}:</b></p>
    <p class="fw-semibold">
        @if(!empty($maintenanceRequest->hours_worked))
            @php
                $totalSeconds = $maintenanceRequest->hours_worked * 3600; // convert hours to seconds
                $hours = floor($totalSeconds / 3600);
                $minutes = floor(($totalSeconds % 3600) / 60);
                $seconds = round($totalSeconds % 60);
            @endphp
            {{ $hours }} {{ __('hours') }} {{ $minutes }} {{ __('minutes') }} {{ $seconds }} {{ __('seconds') }}
        @else
            -
        @endif
    </p>
</div>


                            </div>
                        </div>
                    </div>
                </div>
            @endif


        </div>
    </div>
</div>
