@extends('layouts.app')
@section('page-title')
    {{ __('All Services Overview') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.properties.index') }}">{{ __('Admin Properties') }}</a></li>
    <li class="breadcrumb-item active">{{ __('All Services') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center g-2">
                        <div class="col">
                            <h5>{{ __('All Services & Maintenance Requests') }}</h5>
                            <p class="text-muted mb-0">
                                {{ __('Comprehensive overview of all service requests across all properties') }}</p>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.properties.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left"></i> {{ __('Back to Properties') }}
                            </a>
                            <a href="{{ route('admin.properties.analytics') }}" class="btn btn-primary">
                                <i class="ti ti-chart-bar"></i> {{ __('Analytics') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- {{ dd($maintenanceRequests) }} --}}
    <!-- Statistics Summary -->
    <div class="row mt-3 mb-4">
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="serviceTypeFilter" class="form-label">{{ __('Filter by Service Type') }}</label>
                <select id="serviceTypeFilter" class="form-select">
                    <option value="">{{ __('All Types') }}</option>
                    @foreach ($serviceTypes as $type)
                        <option value="{{ $type->title }}">{{ $type->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light-primary">
                <div class="card-body text-center">
                    <h4 class="text-primary">{{ $maintenanceRequests->count() }}</h4>
                    <p class="mb-0">{{ __('Total Services') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light-warning">
                <div class="card-body text-center">
                    <h4 class="text-warning">{{ $maintenanceRequests->where('status', 'pending')->count() }}</h4>
                    <p class="mb-0">{{ __('Pending') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light-info">
                <div class="card-body text-center">
                    <h4 class="text-info">{{ $maintenanceRequests->where('status', 'in_progress')->count() }}</h4>
                    <p class="mb-0">{{ __('In Progress') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light-success">
                <div class="card-body text-center">
                    <h4 class="text-success">{{ $maintenanceRequests->where('status', 'completed')->count() }}</h4>
                    <p class="mb-0">{{ __('Completed') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    @if ($maintenanceRequests->count() > 0)
                        <div class="table-responsive">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="property_filter" class="form-label">{{ __('Filter by Property') }}</label>
                                    <select id="property_filter" class="form-select">
                                        <option value="">{{ __('All Properties') }}</option>
                                        @foreach ($properties as $property)
                                            <option value="{{ $property->name }}">{{ $property->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="owner_filter" class="form-label">{{ __('Filter by Owner') }}</label>
                                    <select id="owner_filter" class="form-select">
                                        <option value="">{{ __('All Owners') }}</option>
                                        @foreach ($owners as $owner)
                                            <option value="{{ $owner->name }}">{{ $owner->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <table id="maintenance_table" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Property') }}</th>
                                        <th>{{ __('Owner') }}</th>
                                        <th>{{ __('Service Type') }}</th>
                                        <th>{{ __('Unit') }}</th>
                                        <th>{{ __('Maintainer') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        {{-- <th>{{ __('Amount') }}</th>     --}}
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($maintenanceRequests as $request)
                                        <tr data-service-type="{{ $request->types->title ?? '' }}">
                                            <td>{{ \Carbon\Carbon::parse($request->request_date)->format('M d, Y') }}</td>
                                            <td>
                                                @if ($request->properties)
                                                    <a href="{{ route('admin.properties.show', $request->properties->id) }}"
                                                        class="text-decoration-none">
                                                        {{ $request->properties->name ?? __('N/A') }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">{{ __('N/A') }}</span>
                                                @endif
                                            </td>


                                            <td>
                                                @if ($request->properties && $request->properties->owner)
                                                    <div>
                                                        <strong>{{ $request->properties->owner->name }}</strong><br>
                                                        <small
                                                            class="text-muted">{{ $request->properties->owner->email }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">{{ __('Unknown') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-light-info">
                                                    {{ $request->types->title ?? __('N/A') }}
                                                </span>
                                            </td>
                                            <td>{{ $request->units->name ?? __('N/A') }}</td>
                                            <td>{{ $request->maintainers->name ?? __('Unassigned') }}</td>
                                            <td>
                                                @if ($request->status == 'pending' && (!$request->maintainers || $request->maintainers->id == 0))
                                                    <span class="badge bg-light-danger">{{ __('To be Assigned') }}</span>
                                                @elseif($request->status == 'pending')
                                                    <span class="badge bg-light-warning">{{ __('Pending') }}</span>
                                                @elseif($request->status == 'in_progress')
                                                    <span class="badge bg-light-info">{{ __('In Progress') }}</span>
                                                @elseif($request->status == 'completed')
                                                    <span class="badge bg-light-success">{{ __('Completed') }}</span>
                                                @endif
                                            </td>

                                            {{-- <td>
                                                @if ($request->amount)
                                                    {{ priceFormat($request->amount) }}
                                                @else
                                                    <span class="text-muted">{{ __('N/A') }}</span>
                                                @endif
                                            </td> --}}
                                            <td>
                                                <a class="btn btn-sm btn-light customModal" data-size="lg"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-original-title="{{ __('View Details') }}" href="#"
                                                    data-url="{{ route('maintenance-request.show', $request->id) }}"
                                                    data-title="{{ __('Maintenance Request Details') }}">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                @if ($request->properties)
                                                    <a class="btn btn-sm btn-light"
                                                        href="{{ route('admin.properties.show', $request->properties->id) }}"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-original-title="{{ __('View Property') }}">
                                                        <i class="ti ti-building"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted">{{ __('No Property') }}</span>
                                                @endif

                                            </td>
                                        </tr>
                                        {{-- @if ($request->notes)
                                        <tr>
                                            <td colspan="9" class="bg-light">
                                                <small><strong>{{ __('Notes') }}:</strong> {{ $request->notes }}</small>
                                            </td>
                                        </tr>
                                        @endif --}}
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="ti ti-building-warehouse" style="font-size: 3rem; color: #dee2e6;"></i>
                            <h5 class="mt-3 text-muted">{{ __('No Service Requests') }}</h5>
                            <p class="text-muted">
                                {{ __('No maintenance requests have been made across all properties yet.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Request Details Modal -->
    <div class="modal fade" id="maintenanceRequestModal" tabindex="-1" role="dialog"
        aria-labelledby="maintenanceRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="maintenanceRequestModalLabel">{{ __('Maintenance Request Details') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="maintenanceRequestModalBody">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- @push('scripts') --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>


    <script>
        $(document).ready(function() {
            @php
                $currentLang = strtolower(app()->getLocale());
                $isItalian = (strpos($currentLang, 'italian') !== false || strpos($currentLang, 'ita') !== false);
            @endphp
            
            var isItalian = {{ $isItalian ? 'true' : 'false' }};
            var datatableConfig = {
                language: isItalian ? {
                    "sEmptyTable": "Nessun dato disponibile nella tabella",
                    "sInfo": "Mostra da _START_ a _END_ di _TOTAL_ voci",
                    "sInfoEmpty": "Mostra da 0 a 0 di 0 voci",
                    "sInfoFiltered": "(filtrate da _MAX_ voci totali)",
                    "sInfoPostFix": "",
                    "sInfoThousands": ",",
                    "sLengthMenu": "Mostra _MENU_ voci",
                    "sLoadingRecords": "Caricamento in corso...",
                    "sProcessing": "Elaborazione in corso...",
                    "sSearch": "Cerca:",
                    "sZeroRecords": "Nessun record corrispondente trovato",
                    "oPaginate": {
                        "sFirst": "Prima",
                        "sLast": "Ultima",
                        "sNext": "Successivo",
                        "sPrevious": "Precedente"
                    },
                    "oAria": {
                        "sSortAscending": ": attiva per ordinare la colonna in ordine crescente",
                        "sSortDescending": ": attiva per ordinare la colonna in ordine decrescente"
                    }
                } : {}
            };
            
            var table = $('#maintenance_table').DataTable(datatableConfig);

            // Custom filtering function
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var property = $('#property_filter').val().toLowerCase();
                var owner = $('#owner_filter').val().toLowerCase();

                var tableProperty = data[1].toLowerCase();
                var tableOwner = data[2].toLowerCase();

                console.log('Row:', tableProperty, tableOwner, 'Filter:', property, owner);

                if ((property === "" || tableProperty.includes(property)) &&
                    (owner === "" || tableOwner.includes(owner))) {
                    return true;
                }
                return false;
            });

            $('#property_filter').on('change', function() {
                var selected = $(this).val();

                // If "All Properties" is selected, make sure filter value is empty
                if (selected === "") {
                    table.column(1).search('').draw(); // Clear column search
                } else {
                    table.draw(); // Apply the normal filter
                }
            });
            // Owner filter
            $('#owner_filter').on('change', function() {
                var selected = $(this).val();

                if (selected === "") {
                    table.column(2).search('').draw(); // Clear Owner filter
                } else {
                    table.draw(); // Apply filter
                }
            });

        });

        $(document).ready(function() {
            // Debugging check
            console.log('jQuery is loaded:', typeof $ !== 'undefined');

            function safeToString(value) {
                return (value || '').toString().toLowerCase().trim();
            }

            function filterRequests() {
                try {
                    var selectedType = safeToString($('#serviceTypeFilter').val());
                    console.log('Filtering by type:', selectedType);

                    var hasVisibleRows = false;

                    $('tbody tr[data-service-type]').each(function() {
                        var rowType = safeToString($(this).data('service-type'));
                        console.log('Row type:', rowType, 'for row:', this);

                        var shouldShow = !selectedType || rowType.includes(selectedType);
                        $(this).toggle(shouldShow);

                        if (shouldShow) hasVisibleRows = true;
                    });

                    // Handle no results
                    $('#no-results').remove();
                    if (!hasVisibleRows) {
                        $('tbody').append(
                            '<tr id="no-results"><td colspan="8" class="text-center py-4 text-muted">' +
                            'No matching requests found' +
                            '</td></tr>'
                        );
                    }
                } catch (error) {
                    console.error('Filter error:', error);
                }
            }

            // Initialize filter
            $('#serviceTypeFilter').on('change', filterRequests);

            // Debug initial state
            console.log('Initial rows count:', $('tbody tr[data-service-type]').length);
            filterRequests(); // Apply initial filter state
        });
    </script>
    {{-- @endpush --}}
@endsection
