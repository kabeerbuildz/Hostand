

@extends('layouts.app')


@section('page-title')
    {{ __('m_calendar') }}
@endsection

@push('script-page')
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('m_calendar') }}</li>
@endsection

@section('card-action-btn')
    @can('create maintenance request')
        <a class="btn btn-secondary btn-sm ml-20 customModal" href="#" data-size="lg"
            data-url="{{ route('maintenance-request.create') }}" data-title="{{ __('Create Maintenance Request') }}">
            <i class="ti-plus mr-2"></i>{{ __('Create Maintenance Request') }}
        </a>
    @endcan
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card table-card">
                <div class="card-header">
                    <h5>{{ __('m_calendar_view') }}</h5>
                </div>

                <div class="card-body pt-0">
                    @can('create maintenance request')
                        <div class="col-auto" style="margin-left:23px; margin-top: 23px;">
                            <a href="#" class="btn btn-secondary customModal" data-size="lg"
                                data-url="{{ route('maintenance-request.create') }}"
                                data-title="{{ __('Create Maintenance Request') }}">
                                <i class="ti ti-circle-plus align-text-bottom"></i>
                                {{ __('Create Maintenance Request') }}
                            </a>
                        </div>
                    @endcan

                    <div id="calendar"></div>
                </div>
            </div>
        </div>

        <!-- Modal -->
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

    {{-- FullCalendar v6 --}}
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>

    <script>
        // currentUserType if you need it later
        const currentUserType = "{{ \Auth::user()->type }}";

        // Events payload passed from controller - must contain arrival_time and people_count
        const events = @json($events);

        console.log(events);
        

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'it',
                initialView: 'dayGridMonth',
                height: 'auto',
                events: events,
                eventClick: function(info) {
                    const data = info.event.extendedProps || {};

                    // simple status -> badge mapping (adjust classes to your theme)
                    const statusBadges = {
                        'pending': 'bg-warning',
                        'in_progress': 'bg-info',
                        'completed': 'bg-success',
                    };

                    // Attachment display: create download link only if present
                    let attachment = '-';
                    if (data.attachment) {
                        // server-side asset path injected into JS
                        attachment = `<a href="{{ asset('storage/upload/issue_attachment') }}/${data.attachment}" target="_blank" rel="noopener noreferrer"><i class="ti ti-download"></i> Download</a>`;
                    }

                    // Arrival time display: use provided value or '-'
                    // Arrival time
            let arrivalTime = data.arrival_time ? data.arrival_time : '-';

            // People count
            let peopleCount = (data.people_count !== undefined && data.people_count !== null && data.people_count !== '') 
                ? data.people_count 
                : '-';

                    
                   

                const translations = {
                        property: "{{ __('Property') }}",
                        unit: "{{ __('Unit') }}",
                        maintainer: "{{ __('Maintainer') }}",
                        arrival_time: "{{ __('Arrival Time') }}",
                        number_of_people: "{{ __('Number of People') }}",
                        status: "{{ __('Status') }}",
                        attachment: "{{ __('Attachment') }}",
                        view_maintenance: "{{ __('View') }}",
                        edit_maintenance: "{{ __('Edit') }}",
                        delete: "{{ __('Delete') }}",
                        update_status: "{{ __('Update Status') }}",
                    };

                   // Convert arrival time to readable format
                        let arrivalTimeFormatted = '-';
                        if (data.arrival_time) {
                            const dateObj = new Date(data.arrival_time);
                            const year = dateObj.getFullYear();
                            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                            const day = String(dateObj.getDate()).padStart(2, '0');
                            const time = dateObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
                            arrivalTimeFormatted = `${year}-${month}-${day} ${time}`;
                        }
                        console.log(data);

                        let html = `
                            <div class="mb-2">
                                <p class="mb-1"><strong>${translations.property}:</strong> ${data.property_name ?? data.property ?? '-'}</p>
                                <p class="mb-1"><strong>${translations.unit}:</strong> ${data.unit_name ?? data.unit ?? '-'}</p>
                                <p class="mb-1"><strong>${translations.maintainer}:</strong> ${data.maintainer ?? '-'}</p>
                                <p class="mb-1"><strong>${translations.arrival_time}:</strong> ${arrivalTimeFormatted}</p>
                                <p class="mb-1"><strong>${translations.number_of_people}:</strong> ${peopleCount}</p>
                                <p class="mb-1"><strong>${translations.status}:</strong> 
                                    <span class="badge ${statusBadges[data.status] ?? 'bg-secondary'}">
                                        ${(data.status ?? '-').toString().replace(/_/g, ' ')}
                                    </span>
                                </p>
                                <p class="mb-1"><strong>${translations.attachment}:</strong> ${attachment}</p>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-3 align-items-center" style="gap:8px;flex-wrap:wrap;">
                                <!-- View Button -->
                                <a href="#"
                                    class="btn btn-warning btn-sm customModal"
                                    data-size="lg"
                                    data-title="${translations.view_maintenance}"
                                    data-url="${data.show_url}"
                                    style="display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0.45rem 1rem;font-size:0.85rem;font-weight:500;line-height:1.2;border-radius:6px;transition:all 0.2s ease-in-out;">
                                    <i class="ti ti-eye me-1" style="font-size:1rem;"></i> ${translations.view_maintenance}
                                </a>
                        `;


                    @if (Auth::user()->type !== 'maintainer')
                        html += `
                            <!-- Edit Button -->
                            <a href="#"
                                class="btn btn-secondary btn-sm customModal"
                                data-size="lg"
                                data-title="${translations.edit_maintenance}"
                                data-url="${data.edit_url}"
                                style="display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0.45rem 1rem;font-size:0.85rem;font-weight:500;line-height:1.2;border-radius:6px;transition:all 0.2s ease-in-out;">
                                <i class="ti ti-edit me-1" style="font-size:1rem;"></i> ${translations.edit_maintenance}
                            </a>

                            <!-- Delete Button -->
                            <form method="POST" action="${data.delete_url}" class="delete-form" style="display:inline-block;margin:0;">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit"
                                    class="btn btn-danger btn-sm"
                                    style="display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0.45rem 1rem;font-size:0.85rem;font-weight:500;line-height:1.2;border-radius:6px;transition:all 0.2s ease-in-out;">
                                    <i class="ti ti-trash me-1" style="font-size:1rem;"></i> ${translations.delete}
                                </button>
                            </form>

                            <!-- Update Status Button -->
                            <a href="#"
                                class="btn btn-info btn-sm customModal"
                                data-size="lg"
                                data-title="${translations.update_status}"
                                data-url="${data.status_url}"
                                style="display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0.45rem 1rem;font-size:0.85rem;font-weight:500;line-height:1.2;border-radius:6px;transition:all 0.2s ease-in-out;">
                                <i class="ti ti-check-square me-1" style="font-size:1rem;"></i> ${translations.update_status}
                            </a>
                        `;
                    @endif

                    html += `</div>`;



                    html += `</div>`;

                    document.getElementById('eventTitle').innerText = (info.event.title || '') + (info.event.start ? ' — ' + info.event.startStr : '');
                    document.getElementById('eventBody').innerHTML = html;

                    // Show bootstrap modal
                    new bootstrap.Modal(document.getElementById('eventModal')).show();
                }
            });

            calendar.render();
        });
    </script>
