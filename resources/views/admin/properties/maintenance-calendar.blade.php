@extends('layouts.app')
@section('page-title')
    {{ __('Daily Maintenance Calendar') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.properties.index') }}">{{ __('Admin Properties') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Maintenance Calendar') }}</li>
@endsection

@push('script-page')
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
    // Events are already translated in PHP using __()
    const events = @json($events);
    console.log(events);

    var calendarEl = document.getElementById('maintenanceCalendar');
    
    const calendar = new FullCalendar.Calendar(calendarEl, {
           initialView: 'dayGridMonth', // default month view
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            locale: 'it',
            buttonText: {
                today: '{{ __("Today") }}',
                month: '{{ __("Month") }}',
                week: '{{ __("Week") }}',
                day: '{{ __("Day") }}',
                list: '{{ __("List") }}'
            },
            allDayText: '{{ __("All Day") }}',
            events: events,

            // ✅ Professional stacking for week/day views
            slotEventOverlap: false,   // stack events vertically
            eventOverlap: false,       // prevent true overlap
            eventDisplay: 'block',     // makes events full-width and modern

            // Optional: reduce padding for dense schedule
            eventMinHeight: 24,        // smaller height for each event
            eventMaxStack: 999,        // effectively no limit

        eventClick: function(info) {
            const data = info.event.extendedProps;
            
            const statusBadges = {
                'pending': 'bg-warning',
                'in_progress': 'bg-info',
                'completed': 'bg-success',
            };

            let attachment = data.attachment ? 
                `<a href="{{ asset('storage/upload/issue_attachment') }}/${data.attachment}" download class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-download"></i> {{ __('Download Attachment') }}
                </a>` : 
                '<span class="text-muted">{{ __("No attachment") }}</span>';

            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3"><i class="ti ti-building"></i> {{ __("Property Information") }}</h6>
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
                
                ${data.description ? `
                <div class="mt-3">
                    <h6 class="text-secondary mb-2"><i class="ti ti-file-text"></i> {{ __("Description") }}</h6>
                    <p class="text-muted">${data.description}</p>
                </div>
                ` : ''}

                
                
                <div class="mt-4 d-flex gap-2 flex-wrap">
                    <a href="#" 
                       class="btn btn-primary btn-sm customModal" 
                       data-size="lg" 
                       data-title="{{ __('View Maintenance Details') }}" 
                       data-url="${data.show_url}">
                       <i class="ti ti-eye"></i> {{ __("View Details") }}
                    </a>
                    
                    <a href="#" 
                       class="btn btn-warning btn-sm customModal" 
                       data-size="lg" 
                       data-title="{{ __('Edit Maintenance Request') }}" 
                       data-url="${data.edit_url}">
                       <i class="ti ti-edit"></i> {{ __("Edit") }}
                    </a>
                    
                    <a href="#" 
                       class="btn btn-info btn-sm customModal" 
                       data-size="lg" 
                       data-title="{{ __('Update Status') }}" 
                       data-url="${data.status_url}">
                       <i class="ti ti-check-square"></i> {{ __("Update Status") }}
                    </a>
                    
                    <form method="POST" action="${data.delete_url}" class="delete-form d-inline">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-danger btn-sm" 
                                onclick="return confirm('{{ __("Are you sure you want to delete this maintenance request?") }}')">
                            <i class="ti ti-trash"></i> {{ __("Delete") }}
                        </button>
                    </form>
                </div>
            `;

            // ✅ eventTitle is translated in PHP already
            document.getElementById('eventTitle').innerText = info.event.title + ' — ' + info.event.startStr;
            document.getElementById('eventBody').innerHTML = html;
            new bootstrap.Modal(document.getElementById('eventModal')).show();
        },
        eventDidMount: function(info) {
            // Add tooltips to events
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
    <!-- Header Section -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-gradient-info text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="text-white mb-1">{{ __('Daily Maintenance Calendar') }}</h3>
                            <p class="text-white-50 mb-0">
                                <i class="ti ti-calendar"></i> {{ __('Comprehensive view of all maintenance requests across all properties') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('admin.properties.index') }}" class="btn btn-light me-2">
                                <i class="ti ti-arrow-left"></i> {{ __('Back to Properties') }}
                            </a>

                            @can('create maintenance request')
                            <a href="#" 
                            class="btn btn-light customModal" 
                            data-size="lg" 
                            data-title="{{ __('Create Maintenance Request') }}"
                            data-url="{{ route('maintenance-request.create') }}">
                                <i class="ti ti-plus"></i> {{ __('New Request') }}
                            </a>
                        @endcan


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mt-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-warning bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-clock text-warning f-24"></i>
                    </div>
                    <h4 class="text-warning mb-1">{{ $maintenanceRequests->where('status', 'pending')->count() }}</h4>
                    <p class="text-muted mb-0">{{ __('Pending Requests') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-info bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-tools text-info f-24"></i>
                    </div>
                    <h4 class="text-info mb-1">{{ $maintenanceRequests->where('status', 'in_progress')->count() }}</h4>
                    <p class="text-muted mb-0">{{ __('In Progress') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-success bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-check-circle text-success f-24"></i>
                    </div>
                    <h4 class="text-success mb-1">{{ $maintenanceRequests->where('status', 'completed')->count() }}</h4>
                    <p class="text-muted mb-0">{{ __('Completed') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-primary bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-calendar text-primary f-24"></i>
                    </div>
                    <h4 class="text-primary mb-1">{{ $maintenanceRequests->count() }}</h4>
                    <p class="text-muted mb-0">{{ __('Total Requests') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-primary">
                        <i class="ti ti-calendar"></i> {{ __('Maintenance Calendar') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div id="maintenanceCalendar"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-primary">
                        <i class="ti ti-bolt"></i> {{ __('Quick Actions') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.properties.services') }}" class="btn btn-outline-info w-100">
                                <i class="ti ti-list"></i> {{ __('All Services List') }}
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.properties.analytics') }}" class="btn btn-outline-warning w-100">
                                <i class="ti ti-chart-bar"></i> {{ __('Analytics') }}
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.properties.index') }}" class="btn btn-outline-success w-100">
                                <i class="ti ti-buildings"></i> {{ __('All Properties') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="eventBody"></div>
            </div>
        </div>
    </div>
@endsection


{{-- for attchment i saved this --}}
{{-- <div class="mt-3">
                    <h6 class="text-secondary mb-2"><i class="ti ti-paperclip"></i> {{ __("Attachment") }}</h6>
                    ${attachment}
                </div> --}}