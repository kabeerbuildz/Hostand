@extends('layouts.app')

@section('page-title')
    {{ __('Dashboard') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item" aria-current="page">{{ __('Dashboard') }}</li>
@endsection

@push('script-page')
    {{-- Calendar script (FullCalendar Example) --}}
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('service_calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridDay',
                height: 550,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: @json($servicesForTheDay ?? []), // Example: pass events from controller
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
  mmmmmm
@endsection
