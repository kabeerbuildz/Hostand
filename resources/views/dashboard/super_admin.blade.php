@extends('layouts.app')

@section('page-title')
    {{ __('Dashboard') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item" aria-current="page">{{ __('Dashboard') }}</li>
@endsection

@push('script-page')
    {{-- Required Libraries --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/it.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .status-pending {
            background-color: #ffc107 !important;
            border-color: #ffc107 !important;
            color: #fff !important;
        }
        .status-in_progress {
            background-color: #17a2b8 !important;
            border-color: #17a2b8 !important;
            color: #fff !important;
        }
        .status-completed {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            color: #fff !important;
        }
        /* Modern block-style events for timeGrid views */
        .fc .fc-timegrid-event {
            border-radius: 6px;
            padding: 2px 6px;
            font-size: 0.85rem;
            line-height: 1.2;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        /* Month view events */
        .fc .fc-daygrid-event {
            border-radius: 6px;
            font-size: 0.85rem;
            padding: 2px 4px;
        }
        /* Text truncation if too long */
        .fc .fc-event-title {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Debug: Check what events data we're receiving
            var calendarEl = document.getElementById('service_calendar');
            var events = @json($events);
            console.log('Calendar Events:', events);
            
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridDay'
                },
                locale: 'it',
                initialView: 'timeGridDay',
                buttonText: {
                    today: '{{ __("Today") }}',
                    day: '{{ __("Day") }}'
                },
                allDayText: '{{ __("All Day") }}',
                events: @json($events),
                
                // Professional stacking for week/day views
                slotEventOverlap: false,
                eventOverlap: false,
                eventDisplay: 'block',
                
                // Reduce padding for dense schedule
                eventMinHeight: 24,
                eventMaxStack: 999,
                
                eventClick: function(info) {
                    const data = info.event.extendedProps;
                    
                    const statusBadges = {
                        'pending': 'bg-warning',
                        'in_progress': 'bg-info',
                        'completed': 'bg-success',
                    };

                    const modalContent = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3"><i class="ti ti-building"></i> {{ __("Property Details") }}</h6>
                                <div class="mb-3">
                                    <strong>{{ __("Property") }}:</strong> ${data.property}
                                </div>
                                <div class="mb-3">
                                    <strong>{{ __("Unit") }}:</strong> ${data.unit}
                                </div>
                                <div class="mb-3">
                                    <strong>{{ __("Owner") }}:</strong> ${data.owner}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-info mb-3"><i class="ti ti-tools"></i> {{ __("Service Details") }}</h6>
                                <div class="mb-3">
                                    <strong>{{ __("Maintainer") }}:</strong> ${data.maintainer}
                                </div>
                                <div class="mb-3">
                                    <strong>{{ __("Status") }}:</strong> 
                                    <span class="badge ${statusBadges[data.status] ?? 'bg-secondary'}">
                                        ${data.status.replace('_', ' ').toUpperCase()}
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <strong>{{ __("Date") }}:</strong> ${info.event.startStr}
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 text-end">
                                <a href="#" class="btn btn-info btn-sm customModal" data-size="lg" data-title="{{ __('Maintenance Request Details') }}" data-url="${data.show_url}">
                                    <i class="ti ti-eye"></i> {{ __("View Details") }}
                                </a>
                                @can('edit maintenance request')
                                <a href="#" class="btn btn-primary btn-sm customModal" data-size="lg" data-title="{{ __('Edit Maintenance Request') }}" data-url="${data.edit_url}">
                                    <i class="ti ti-pencil"></i> {{ __("Edit") }}
                                </a>
                                @endcan
                            </div>
                        </div>
                    `;

                    // Update modal content
                    $('#eventTitle').html(info.event.title);
                    $('#eventBody').html(modalContent);
                    
                    // Show modal
                    $('#eventModal').modal('show');
                },

                // Add tooltips to events
                eventDidMount: function(info) {
                    $(info.el).tooltip({
                        title: info.event.title + ' - ' + info.event.extendedProps.property,
                        placement: 'top',
                        trigger: 'hover'
                    });
                }
            });
            calendar.render();
        });
    </script>
@endpush

@section('content')
    <div class="row">
        {{-- KPI CARDS --}}
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar bg-light-primary">
                            <i class="ti ti-broom f-24"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-1">{{ __('Active Cleaners') }}</p>
                        <h4 class="mb-0">{{ $result['activeCleaners'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar bg-light-success">
                            <i class="ti ti-bike f-24"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-1">{{ __('Active Riders') }}</p>
                        <h4 class="mb-0">{{ $result['activeRiders'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar bg-light-warning">
                            <i class="ti ti-tools f-24"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-1">{{ __('Active Maintenance') }}</p>
                        <h4 class="mb-0">{{ $result['activeMaintenance'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar bg-light-info">
                            <i class="ti ti-clipboard-list f-24"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-1">{{ __('Requests Today') }}</p>
                        <h4 class="mb-0">{{ $result['requestsToday'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar bg-light-danger">
                            <i class="ti ti-alert-circle f-24"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-1">{{ __('Open Tickets') }}</p>
                        <h4 class="mb-0">{{ $result['openTickets'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CALENDAR SECTION --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="ti ti-calendar"></i> {{ __('Service (Today)') }}
                    </h5>
                </div>
                <div class="card-body">
                    <div id="service_calendar" style="min-height: 600px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="eventBody">
                </div>
            </div>
        </div>
    </div>

    {{-- NOTES + CHAT --}}
        <div class="col-lg-4 col-md-12">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>{{ __('Notes for Today') }}</h5>
                    @if(!empty($notesWidgetAvailable))
                        <a href="{{ route('note.index') }}" class="btn btn-sm btn-outline-primary">{{ __('Open Notes') }}</a>
                    @else
                        <a href="" class="btn btn-sm btn-primary">{{ __('Add Note') }}</a>
                    @endif
                </div>
                <div class="card-body">
                    @if(!empty($notes))
                        <ul class="list-group">
                            @foreach($notes as $note)
                                <li class="list-group-item">{{ $note->title }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">{{ __('No notes for today.') }}</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Live Chat') }}</h5>
                </div>
                <div class="card-body text-center">
                    <a href="{{ route('contact.index') }}" class="btn btn-success w-100">
                        <i class="ti ti-message-circle"></i> {{ __('Open Live Chat') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
