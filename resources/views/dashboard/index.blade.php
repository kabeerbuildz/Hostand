@extends('layouts.app')
@section('page-title')
    {{ __('Dashboard') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item" aria-current="page">{{ __('Dashboard') }}</li>
@endsection

@push('script-page')
    <script>
        var options = {
            chart: {
                type: 'area',
                height: 250,
                toolbar: {
                    show: false
                }
            },
            colors: ['#2ca58d', '#0a2342'],
            dataLabels: {
                enabled: false
            },
            legend: {
                show: true,
                position: 'top'
            },
            markers: {
                size: 1,
                colors: ['#fff', '#fff', '#fff'],
                strokeColors: ['#2ca58d', '#0a2342'],
                strokeWidth: 1,
                shape: 'circle',
                hover: {
                    size: 4
                }
            },
            stroke: {
                width: 2,
                curve: 'smooth'
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    type: 'vertical',
                    inverseColors: false,
                    opacityFrom: 0.5,
                    opacityTo: 0
                }
            },
            grid: {
                show: false
            },
            series: [{
                    name: "{{ __('Total Income') }}",
                    data: {!! json_encode($result['incomeExpenseByMonth']['income']) !!}
                },
                {
                    name: "{{ __('Total Expense') }}",
                    data: {!! json_encode($result['incomeExpenseByMonth']['expense']) !!}
                }
            ],
            xaxis: {
                categories: {!! json_encode($result['incomeExpenseByMonth']['label']) !!},
                tooltip: {
                    enabled: false
                },
                labels: {
                    hideOverlappingLabels: true
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            }
        };
        var chart = new ApexCharts(document.querySelector('#incomeExpense'), options);
        chart.render();
    </script>
@endpush

@php
    $settings = settings();

@endphp
@section('content')
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar bg-light-secondary">
                                <i class="ti ti-building f-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-1">{{ __('Total Property') }}</p>
                            <div class="d-flex align-items-center justify-content-between">
                                <h4 class="mb-0">{{ $result['totalProperty'] }}</h4>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar bg-light-warning">
                                <i class="ti ti-3d-cube-sphere f-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-1">{{ __('Total Unit') }}</p>
                            <div class="d-flex align-items-center justify-content-between">
                                <h4 class="mb-0">{{ $result['totalUnit'] }}</h4>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar bg-light-primary">
                                <i class="ti ti-file-invoice f-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-1">{{ __('Total Invoice') }}</p>
                            <div class="d-flex align-items-center justify-content-between">
                                <h4 class="mb-0">{{ $settings['CURRENCY_SYMBOL'] }}<span
                                        class="count">{{ $result['totalIncome'] }}</h4>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avtar bg-light-danger">
                                <i class="ti ti-exposure f-24"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-1">{{ __('Total Services in Progress') }}</p>
                            <div class="d-flex align-items-center justify-content-between">
                                <h4 class="mb-0">
                                    <span class="count">{{ $result['totalServices'] }}</span>
                                </h4>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>


    </div>



    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-1">{{ __('Weekly Calendar') }}</h5>
                <p class="text-muted mb-2">{{ __('Maintenance Schedule for This Week') }}</p>

                <div id="calendar"></div>
            </div>
        </div>
    </div>

    @php
        $locale = app()->getLocale();
        // Normalize locale names to ISO codes for FullCalendar
        $localeMap = [
            'italian' => 'it',
            'english' => 'en'
        ];
        $fcLocale = isset($localeMap[$locale]) ? $localeMap[$locale] : $locale;
    @endphp

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>
    @if($fcLocale !== 'en')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/locales/{{ $fcLocale }}.global.min.js"></script>
    @endif

    <script>
        const events = @json($events);

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');

            const currentLang = "{{ $fcLocale }}"; // normalized locale code for FullCalendar


            const calendar = new FullCalendar.Calendar(calendarEl, {
                locale: currentLang, // use normalized locale
                initialView: 'timeGridWeek', // WEEK VIEW ONLY
                height: 'auto',

                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: '' // hide buttons for day/month
                },
                    
                allDayText: '{{ __("All Day") }}',
                events: events,

                eventClick: function(info) {
                    const data = info.event.extendedProps || {};

                    const statusBadges = {
                        'pending': 'bg-warning',
                        'in_progress': 'bg-info',
                        'completed': 'bg-success',
                    };

                    let attachment = '-';
                    if (data.attachment) {
                        attachment = `<a href="{{ asset('storage/upload/issue_attachment') }}/${data.attachment}" target="_blank">
                                <i class="ti ti-download"></i> Download
                              </a>`;
                    }

                    let arrivalTimeFormatted = '-';
                    if (data.arrival_time) {
                        const dateObj = new Date(data.arrival_time);
                        const y = dateObj.getFullYear();
                        const m = String(dateObj.getMonth() + 1).padStart(2, '0');
                        const d = String(dateObj.getDate()).padStart(2, '0');
                        const t = dateObj.toLocaleTimeString([], {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true
                        });
                        arrivalTimeFormatted = `${y}-${m}-${d} ${t}`;
                    }

                    let html = `
                <p><strong>Property:</strong> ${data.property_name ?? '-'}</p>
                <p><strong>Unit:</strong> ${data.unit_name ?? '-'}</p>
                <p><strong>Maintainer:</strong> ${data.maintainer ?? '-'}</p>
                <p><strong>Arrival:</strong> ${arrivalTimeFormatted}</p>
                <p><strong>People:</strong> ${data.people_count ?? '-'}</p>
                <p><strong>Status:</strong> 
                    <span class="badge ${statusBadges[data.status] ?? 'bg-secondary'}">
                        ${(data.status ?? '-').replace(/_/g,' ')}
                    </span>
                </p>
                <p><strong>Attachment:</strong> ${attachment}</p>
                
                <div class="mt-3 d-flex gap-2">
                    <a href="#" 
                       class="btn btn-warning btn-sm customModal"
                       data-size="lg"
                       data-title="View"
                       data-url="${data.show_url}">
                       <i class="ti ti-eye me-1"></i> View
                    </a>
            `;

                    @if (Auth::user()->type !== 'maintainer')
                        html += `
                    <a href="#" 
                       class="btn btn-secondary btn-sm customModal"
                       data-size="lg"
                       data-title="Edit"
                       data-url="${data.edit_url}">
                       <i class="ti ti-edit me-1"></i> Edit
                    </a>

                    <form method="POST" action="${data.delete_url}" class="delete-form">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm">
                            <i class="ti ti-trash me-1"></i> Delete
                        </button>
                    </form>

                    <a href="#" 
                       class="btn btn-info btn-sm customModal"
                       data-size="lg"
                       data-title="Update Status"
                       data-url="${data.status_url}">
                       <i class="ti ti-check-square me-1"></i> Update Status
                    </a>
            `;
                    @endif

                    document.getElementById('eventTitle').innerText =
                        info.event.title + ' — ' + info.event.startStr;

                    document.getElementById('eventBody').innerHTML = html;

                    new bootstrap.Modal(document.getElementById('eventModal')).show();
                }
            });

            calendar.render();
        });
    </script>
@endsection
