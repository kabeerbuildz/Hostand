@extends('layouts.app')

@section('page-title')
    {{ __('Dashboard') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item" aria-current="page">{{ __('Dashboard') }}</li>
@endsection

@push('script-page')
    {{-- Required Libraries --}}
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/it.global.min.js"></script>
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
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
                    today: '{{ __('Today') }}',
                    day: '{{ __('Day') }}'
                },
                allDayText: '{{ __('All Day') }}',
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
                                <h6 class="text-primary mb-3"><i class="ti ti-building"></i> {{ __('Property Details') }}</h6>
                                <div class="mb-3">
                                    <strong>{{ __('Property') }}:</strong> ${data.property}
                                </div>
                                <div class="mb-3">
                                    <strong>{{ __('Unit') }}:</strong> ${data.unit}
                                </div>
                                <div class="mb-3">
                                    <strong>{{ __('Owner') }}:</strong> ${data.owner}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-info mb-3"><i class="ti ti-tools"></i> {{ __('Service Details') }}</h6>
                                <div class="mb-3">
                                    <strong>{{ __('Maintainer') }}:</strong> ${data.maintainer}
                                </div>
                                <div class="mb-3">
                                    <strong>{{ __('Status') }}:</strong> 
                                    <span class="badge ${statusBadges[data.status] ?? 'bg-secondary'}">
                                        ${data.status.replace('_', ' ').toUpperCase()}
                                    </span>
                                </div>
                                <div class="mb-3">
                                    <strong>{{ __('Date') }}:</strong> ${info.event.startStr}
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12 text-end">
                                <a href="#" class="btn btn-info btn-sm customModal" data-size="lg" data-title="{{ __('Maintenance Request Details') }}" data-url="${data.show_url}">
                                    <i class="ti ti-eye"></i> {{ __('View Details') }}
                                </a>
                                @can('edit maintenance request')
                                <a href="#" class="btn btn-primary btn-sm customModal" data-size="lg" data-title="{{ __('Edit Maintenance Request') }}" data-url="${data.edit_url}">
                                    <i class="ti ti-pencil"></i> {{ __('Edit') }}
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
                            <i class="ti ti-users f-24"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-1">{{ __('Total Owner') }}</p>
                        <h4 class="mb-0">{{ $result['totalOwner'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar bg-light-success">
                            <i class="ti ti-progress f-24"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-1">{{ __('Services in Progress') }}</p>
                        <h4 class="mb-0">{{ $result['totalService'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar bg-light-warning">
                            <i class="ti ti-ticket f-24"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-1">{{ __('Number of Open Tickets') }}</p>
                        <h4 class="mb-0">{{ $result['totalOpenTicket'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avtar bg-light-info">
                            <i class="ti ti-calendar-event f-24"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-1">{{ __('Requests Today') }}</p>
                        <h4 class="mb-0">{{ $result['requestsToday'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        {{-- 
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
        </div> --}}
    </div>

    {{-- CALENDAR --}}
    <div class="row mt-4">
        <div class="col-lg-8 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Service Calendar (Today)') }}</h5>
                </div>
                <div class="card-body">
                    <div id="service_calendar"></div>
                </div>
            </div>
        </div>

        {{-- NOTES + CHAT --}}
        <div class="col-lg-4 col-md-12">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>{{ __('Notes for Today') }}</h5>
                    @if (!empty($notesWidgetAvailable))
                        <a href="{{ route('note.index') }}"
                            class="btn btn-sm btn-outline-primary">{{ __('Open Notes') }}</a>
                    @else
                        <a href="#" class="btn btn-sm btn-primary customModal rounded" data-size="md"
                            data-url="{{ route('note.create') }}" data-title="{{ __('Create Note') }}">
                            {{ __('Add Note') }}
                        </a>
                    @endif
                </div>

                <div class="card-body">
                    @if (!empty($notes) && count($notes) > 0)
                        <ul class="list-group">
                            @foreach ($notes as $note)
                                <li class="list-group-item border-0 mb-2 shadow-sm rounded p-3">

                                    <!-- Title -->
                                    <h6 class="mb-1 fw-bold text-primary">
                                        <i class="ti ti-notes"></i> {{ $note->title }}
                                    </h6>

                                    <!-- Description (optional) -->
                                    @if (!empty($note->description))
                                        <p class="text-muted mb-0" style="font-size: 14px;">
                                            {{ $note->description }}
                                        </p>
                                    @else
                                        <p class="text-muted mb-0" style="font-size: 14px;">
                                            {{ __('No description provided.') }}
                                        </p>
                                    @endif

                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0 text-center">{{ __('No notes for today.') }}</p>
                    @endif
                </div>

            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                    <h5 class="mb-0">{{ __('Live Chat') }}</h5>
                    <small class="text-muted">{{ __('Recent Messages') }}</small>
                </div>

                <div class="card-body">

                    @if (isset($recentMessages) && $recentMessages->count() > 0)
                        <ul class="list-group mb-3">
                            @foreach ($recentMessages as $message)
                                <li class="list-group-item border-0 mb-2 p-3 rounded shadow-sm"
                                    style="background: #f8f9fa; transition: 0.2s;">

                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong class="text-primary" style="font-size: 15px;">
                                                {{ $message->name ?? 'User' }}
                                            </strong>
                                            <div class="text-muted mt-1" style="font-size: 13px; line-height: 1.3;">
                                                {{ Str::limit($message->message, 60) }}
                                            </div>
                                        </div>

                                        <span class="badge bg-light text-muted border rounded"
                                            style="font-size: 11px; height: fit-content;">
                                            @php
                                                $locale = app()->getLocale();
                                                // Normalize locale names to ISO codes
                                                $localeMap = [
                                                    'italian' => 'it',
                                                    'english' => 'en'
                                                ];
                                                if (isset($localeMap[$locale])) {
                                                    $locale = $localeMap[$locale];
                                                }
                                            @endphp
                                            {{ $message->created_at->locale($locale)->diffForHumans() }}
                                        </span>
                                    </div>

                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-center text-muted mb-3">{{ __('No recent messages') }}</p>
                    @endif

                    <a href="{{ route('contact.index') }}" class="btn btn-success w-100 py-2">
                        <i class="ti ti-message-circle"></i> {{ __('Open Live Chat') }}
                    </a>

                </div>
            </div>




        </div>
    </div>
@endsection
