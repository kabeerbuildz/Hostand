@extends('layouts.app')
@section('page-title')
    {{ __('Service Assignment Management') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('Service Assignment') }}</li>
@endsection

@section('content')
    <!-- Header Section -->
    <div class="row">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="text-white mb-1">{{ __('Service Assignment Management') }}</h3>
                            <p class="text-white-50 mb-0">
                                <i class="ti ti-users"></i> {{ __('Assign and manage maintenance services to operators') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <button type="button" class="btn btn-light me-2" data-bs-toggle="modal"
                                data-bs-target="#bulkAssignModal">
                                <i class="ti ti-list-check"></i> {{ __('Bulk Assign') }}
                            </button>
                            <a href="{{ route('admin.properties.index') }}" class="btn btn-light">
                                <i class="ti ti-arrow-left"></i> {{ __('Back to Properties') }}
                            </a>
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
                    <h4 class="text-warning mb-1">{{ $allRequests->count() }}</h4>
                    <p class="text-muted mb-0">{{ __('Total Requests') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-info bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-user-check text-info f-24"></i>
                    </div>
                    <h4 class="text-info mb-1">{{ $maintainers->count() }}</h4>
                    <p class="text-muted mb-0">{{ __('Available Operators') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-warning bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-clock text-warning f-24"></i>
                    </div>
                    <h4 class="text-warning mb-1">{{ $pendingRequests->count() }}</h4>
                    <p class="text-muted mb-0">{{ __('Pending Requests') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-success bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-tools text-success f-24"></i>
                    </div>
                    <h4 class="text-success mb-1">{{ $todayAssigned->count() }}</h4>
                    <p class="text-muted mb-0">{{ __('Today\'s Assignments') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avtar bg-primary bg-opacity-10 mx-auto mb-3">
                        <i class="ti ti-calendar text-primary f-24"></i>
                    </div>
                    <h4 class="text-primary mb-1">{{ $tomorrowAssigned->count() }}</h4>
                    <p class="text-muted mb-0">{{ __('Tomorrow\'s Assignments') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- All Services -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-primary">
                            <i class="ti ti-list"></i> {{ __('All Service Requests') }}
                        </h6>
                        <div class="d-flex gap-2 align-items-center">
                            <small class="text-muted me-2">{{ __('Filter:') }}</small>
                            <select id="statusFilter" class="form-select form-select-sm" style="width: auto;">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="pending">{{ __('Pending') }}</option>
                                <option value="in_progress">{{ __('In Progress') }}</option>
                                <option value="completed">{{ __('Completed') }}</option>
                            </select>
                            <select id="assignFilter" class="form-select form-select-sm" style="width: auto;">
                                <option value="">{{ __('All Assignments') }}</option>
                                <option value="assigned">{{ __('Assigned') }}</option>
                                <option value="unassigned">{{ __('Unassigned') }}</option>
                            </select>
                            <button type="button" id="clearFilters" class="btn btn-outline-secondary btn-sm">
                                <i class="ti ti-x"></i> {{ __('Clear') }}
                            </button>
                            <small id="filterCount" class="text-muted ms-2"></small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if ($allRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Property') }}</th>
                                        <th>{{ __('Unit') }}</th>
                                        <th>{{ __('Service Type') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Assigned To') }}</th>
                                        <th>{{ __('Description') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($allRequests as $request)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="selected_services[]"
                                                    value="{{ $request->id }}" class="form-check-input service-checkbox">
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($request->arrival_time)->format('M d, Y') }}
                                                </small>
                                            </td>
                                            <td>
                                                <strong>{{ $request->properties->name ?? 'N/A' }}</strong>
                                            </td>
                                            <td>{{ $request->units->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-light-info">
                                                    {{ $request->types->title ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($request->status == 'pending' && (!$request->maintainers || $request->maintainers->id == 0))
                                                    <span
                                                        class="badge bg-danger text-white fw-bold text-uppercase px-2 py-1 shadow-sm rounded"
                                                        style="font-size: 0.85rem;">
                                                        {{ __('To be Assigned') }}
                                                    </span>
                                                @elseif ($request->status == 'pending')
                                                    <span
                                                        class="badge bg-warning text-dark text-uppercase px-2 py-1 shadow-sm rounded"
                                                        style="font-size: 0.85rem;">
                                                        {{ __('Pending') }}
                                                    </span>
                                                @elseif($request->status == 'in_progress')
                                                    <span
                                                        class="badge bg-info text-white text-uppercase px-2 py-1 shadow-sm rounded"
                                                        style="font-size: 0.85rem;">
                                                        {{ __('In Progress') }}
                                                    </span>
                                                @elseif($request->status == 'completed')
                                                    <span
                                                        class="badge bg-success text-white text-uppercase px-2 py-1 shadow-sm rounded"
                                                        style="font-size: 0.85rem;">
                                                        {{ __('Completed') }}
                                                    </span>
                                                @else
                                                    <span
                                                        class="badge bg-secondary text-white text-uppercase px-2 py-1 shadow-sm rounded"
                                                        style="font-size: 0.85rem;">
                                                        {{ ucfirst($request->status) }}
                                                    </span>
                                                @endif
                                            </td>



                                            <td>
                                                @if ($request->maintainers && $request->maintainers->id)
                                                    <small class="text-success">
                                                        <i class="ti ti-user"></i> {{ $request->maintainers->name }}
                                                    </small>
                                                @else
                                                    <small class="text-muted">
                                                        <i class="ti ti-user-off"></i> {{ __('Unassigned') }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ Str::limit($request->description, 50) }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    @if ($request->status == 'pending' && (!$request->maintainers || !$request->maintainers->id))
                                                        <button type="button" class="btn btn-outline-primary btn-sm"
                                                            data-bs-toggle="modal" data-bs-target="#assignModal"
                                                            data-request-id="{{ $request->id }}"
                                                            data-request-property="{{ $request->properties->name ?? 'N/A' }}"
                                                            data-request-unit="{{ $request->units->name ?? 'N/A' }}"
                                                            data-request-type="{{ $request->types->title ?? 'N/A' }}"
                                                            data-service-type-id="{{ $request->types->id ?? '' }}"
                                                            data-service-date="{{ $request->arrival_time ? \Carbon\Carbon::parse($request->arrival_time)->format('Y-m-d\TH:i') : '' }}">
                                                            <i class="ti ti-user-plus"></i> {{ __('Assign') }}
                                                        </button>
                                                    @elseif($request->status == 'pending' && $request->maintainers && $request->maintainers->id)
                                                        <button type="button" class="btn btn-outline-warning btn-sm"
                                                            data-bs-toggle="modal" data-bs-target="#reassignModal"
                                                            data-request-id="{{ $request->id }}"
                                                            data-current-maintainer="{{ $request->maintainers->name }}"
                                                            data-service-date="{{ $request->arrival_time ? \Carbon\Carbon::parse($request->arrival_time)->format('Y-m-d\TH:i') : '' }}">
                                                            
                                                            <i class="ti ti-user-switch"></i> {{ __('Reassign') }}
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                            onclick="unassignService({{ $request->id }})">
                                                            <i class="ti ti-user-minus"></i> {{ __('Unassign') }}
                                                        </button>
                                                    @elseif($request->status == 'in_progress')
                                                        <button type="button" class="btn btn-outline-warning btn-sm"
                                                            data-bs-toggle="modal" data-bs-target="#reassignModal"
                                                            data-request-id="{{ $request->id }}"
                                                            data-current-maintainer="{{ $request->maintainers && $request->maintainers->id ? $request->maintainers->name : 'Unassigned' }}">
                                                            <i class="ti ti-user-switch"></i> {{ __('Reassign') }}
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                            onclick="unassignService({{ $request->id }})">
                                                            <i class="ti ti-user-minus"></i> {{ __('Unassign') }}
                                                        </button>
                                                    @endif
                                                    <a href="#" class="btn btn-outline-info btn-sm customModal"
                                                        data-size="lg" data-title="{{ __('View Request Details') }}"
                                                        data-url="{{ route('maintenance-request.show', $request->id) }}">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="ti ti-inbox" style="font-size: 3rem; color: #6c757d;"></i>
                            <h5 class="mt-3 text-muted">{{ __('No Service Requests') }}</h5>
                            <p class="text-muted">{{ __('No maintenance requests found in the system.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Today's and Tomorrow's Assignments -->
    <div class="row">
        <!-- Today's Assignments -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-success">
                        <i class="ti ti-calendar"></i> {{ __('Today\'s Assignments') }} ({{ $today->format('M d, Y') }})
                    </h6>
                </div>
                <div class="card-body">
                    @if ($todayAssigned->count() > 0)
                        @foreach ($todayAssigned as $assignment)
                            <div class="card mb-2 border-success">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $assignment->properties->name ?? 'N/A' }}</h6>
                                            <small class="text-muted">{{ $assignment->units->name ?? 'N/A' }} -
                                                {{ $assignment->types->title ?? 'N/A' }}</small>
                                            <br>
                                            <small class="text-success">
                                                <i class="ti ti-user"></i>
                                                {{ $assignment->maintainers && $assignment->maintainers->id ? $assignment->maintainers->name : 'Unassigned' }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-success">{{ __('Assigned') }}</span>
                                            <br>
                                            <small
                                                class="text-muted">{{ \Carbon\Carbon::parse($assignment->arrival_time)->format('Y-m-d') }}
</small>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-outline-warning btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#reassignModal"
                                            data-request-id="{{ $assignment->id }}"
                                            data-current-maintainer="{{ $assignment->maintainers && $assignment->maintainers->id ? $assignment->maintainers->name : 'Unassigned' }}">
                                            <i class="ti ti-user-switch"></i> {{ __('Reassign') }}
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                            onclick="unassignService({{ $assignment->id }})">
                                            <i class="ti ti-user-minus"></i> {{ __('Unassign') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <i class="ti ti-calendar-off" style="font-size: 2rem; color: #dee2e6;"></i>
                            <p class="text-muted mt-2 mb-0">{{ __('No assignments for today.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tomorrow's Assignments -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 text-info">
                        <i class="ti ti-calendar"></i> {{ __('Tomorrow\'s Assignments') }}
                        ({{ $today->copy()->addDay()->format('M d, Y') }})
                    </h6>
                </div>
                <div class="card-body">
                    @if ($tomorrowAssigned->count() > 0)
                        @foreach ($tomorrowAssigned as $assignment)
                            <div class="card mb-2 border-info">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $assignment->properties->name ?? 'N/A' }}</h6>
                                            <small class="text-muted">{{ $assignment->units->name ?? 'N/A' }} -
                                                {{ $assignment->types->title ?? 'N/A' }}</small>
                                            <br>
                                            <small class="text-info">
                                                <i class="ti ti-user"></i>
                                                {{ $assignment->maintainers && $assignment->maintainers->id ? $assignment->maintainers->name : 'Unassigned' }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-info">{{ __('Assigned') }}</span>
                                            <br>
                                            <small
                                                class="text-muted">{{ \Carbon\Carbon::parse($assignment->request_date)->format('H:i') }}</small>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-outline-warning btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#reassignModal"
                                            data-request-id="{{ $assignment->id }}"
                                            data-current-maintainer="{{ $assignment->maintainers && $assignment->maintainers->id ? $assignment->maintainers->name : 'Unassigned' }}">
                                            <i class="ti ti-user-switch"></i> {{ __('Reassign') }}
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                            onclick="unassignService({{ $assignment->id }})">
                                            <i class="ti ti-user-minus"></i> {{ __('Unassign') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <i class="ti ti-calendar-off" style="font-size: 2rem; color: #dee2e6;"></i>
                            <p class="text-muted mt-2 mb-0">{{ __('No assignments for tomorrow.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

<!-- Assign Service Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Assign Service') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.service-assignment.assign') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="maintenance_request_id" id="assignRequestId">
                    <input type="hidden" id="assignServiceTypeId">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Property') }}</label>
                            <input type="text" class="form-control" id="assignProperty" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Unit') }}</label>
                            <input type="text" class="form-control" id="assignUnit" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Service Type') }}</label>
                            <input type="text" class="form-control" id="assignServiceType" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Assign to Operator') }}</label>
                            <select name="maintainer_id" id="assignMaintainerSelect" class="form-control" required>
                                <option value="">{{ __('Loading compatible operators...') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Service Date & Time') }}</label>
                            <!-- datetime-local so operator can see date + time -->
                            <input type="datetime-local" id="assignServiceDate" name="assigned_date"
                                class="form-control" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2"
                                placeholder="{{ __('Optional notes for the operator...') }}"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Assign Service') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reassign Service Modal -->
<div class="modal fade" id="reassignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Reassign Service') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.service-assignment.reassign') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="maintenance_request_id" id="reassignRequestId">

                    <div class="alert alert-info">
                        <i class="ti ti-info-circle"></i> {{ __('Currently assigned to:') }} <strong
                            id="currentMaintainer"></strong>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('New Operator') }}</label>
                            <select name="new_maintainer_id" class="form-control" required>
                                <option value="">{{ __('Select New Operator') }}</option>
                                @foreach ($maintainers as $maintainer)
                                    <option value="{{ $maintainer->id }}">{{ $maintainer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('New Service Date') }}</label>
                            <input type="datetime-local" id="reassignServiceDate" name="new_date" class="form-control" required>

                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Reassignment Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="{{ __('Reason for reassignment...') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-warning">{{ __('Reassign Service') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Assign Modal -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Bulk Assign Services') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.service-assignment.bulk-assign') }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle"></i>
                        {{ __('Select services from the table above, then assign them to an operator.') }}
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Operator') }}</label>
                            <select name="maintainer_id" class="form-control" required>
                                <option value="">{{ __('Select Operator') }}</option>
                                @foreach ($maintainers as $maintainer)
                                    <option value="{{ $maintainer->id }}">{{ $maintainer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ __('Assignment Date') }}</label>
                            <input type="date" name="assigned_date" class="form-control"
                                value="{{ $today->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea name="notes" class="form-control" rows="3"
                            placeholder="{{ __('Notes for all selected services...') }}"></textarea>
                    </div>

                    <div id="selectedServicesList" class="d-none">
                        <h6>{{ __('Selected Services:') }}</h6>
                        <div id="selectedServicesDisplay"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="bulkAssignBtn"
                        disabled>{{ __('Assign Selected Services') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('script-page')
    <script>
        // helper to format current datetime for datetime-local input (YYYY-MM-DDTHH:MM)
        function nowLocalDatetime() {
            function pad(n) {
                return n < 10 ? '0' + n : n;
            }
            var d = new Date();
            return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) +
                'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
        }

        // Assign Modal
        $('#assignModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var requestId = button.data('request-id');
            var property = button.data('request-property');
            var unit = button.data('request-unit');
            var serviceType = button.data('request-type');
            var serviceTypeId = button.data('service-type-id');
            var serviceDate = button.data('service-date'); // expected in 'YYYY-MM-DDTHH:MM' if provided

            $('#assignRequestId').val(requestId);
            $('#assignProperty').val(property);
            $('#assignUnit').val(unit);
            $('#assignServiceType').val(serviceType);
            $('#assignServiceTypeId').val(serviceTypeId);

            // Set the service date & time (datetime-local expects 'YYYY-MM-DDTHH:MM')
            if (serviceDate && serviceDate !== '') {
                $('#assignServiceDate').val(serviceDate);
            } else {
                // fallback to current datetime (rounded to minute)
                $('#assignServiceDate').val(nowLocalDatetime());
            }

            // Load compatible maintainers
            loadCompatibleMaintainers(serviceTypeId);
        });

        function loadCompatibleMaintainers(serviceTypeId) {
            if (!serviceTypeId) {
                $('#assignMaintainerSelect').html('<option value="">{{ __('No service type selected') }}</option>');
                return;
            }

            $('#assignMaintainerSelect').html('<option value="">{{ __('Loading compatible operators...') }}</option>');

            $.ajax({
                url: '{{ route('admin.service-assignment.compatible-maintainers') }}',
                method: 'GET',
                data: {
                    service_type_id: serviceTypeId
                },
                success: function(response) {
                    var select = $('#assignMaintainerSelect');
                    select.html('<option value="">{{ __('Select Operator') }}</option>');

                    if (response.maintainers && response.maintainers.length > 0) {
                        response.maintainers.forEach(function(maintainer) {
                            select.append('<option value="' + maintainer.id + '">' + maintainer.name +
                                ' (' + maintainer.type + ')</option>');
                        });
                    } else {
                        select.html('<option value="">{{ __('No operators available') }}</option>');
                    }
                },
                error: function() {
                    $('#assignMaintainerSelect').html(
                        '<option value="">{{ __('Error loading operators') }}</option>');
                }
            });
        }

        // Reassign Modal
        $('#reassignModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var requestId = button.data('request-id');
            var currentMaintainer = button.data('current-maintainer');
            var serviceDate = button.data('service-date'); // full format (YYYY-MM-DDTHH:MM)
            console.log(currentMaintainer);
            

            $('#reassignRequestId').val(requestId);
            $('#currentMaintainer').text(currentMaintainer);

            // ⭐ Set date + time directly
            if (serviceDate) {
                $('#reassignServiceDate').val(serviceDate);
            }
        });


        // Select All Checkbox
        $('#selectAll').change(function() {
            $('.service-checkbox').prop('checked', $(this).is(':checked'));
            updateSelectedServices();
        });

        // Individual Checkboxes
        $(document).on('change', '.service-checkbox', function() {
            updateSelectedServices();
        });

        function updateSelectedServices() {
            var selectedServices = $('.service-checkbox:checked');
            var selectedIds = [];

            selectedServices.each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length > 0) {
                $('#selectedServicesList').removeClass('d-none');
                $('#selectedServicesDisplay').html('<span class="badge bg-primary me-1">' + selectedIds.length +
                    ' services selected</span>');
                $('#bulkAssignBtn').prop('disabled', false);

                // Add hidden inputs for selected services
                $('input[name="service_ids[]"]').remove();
                selectedIds.forEach(function(id) {
                    $('#bulkAssignModal form').append('<input type="hidden" name="service_ids[]" value="' + id +
                        '">');
                });
            } else {
                $('#selectedServicesList').addClass('d-none');
                $('#bulkAssignBtn').prop('disabled', true);
                $('input[name="service_ids[]"]').remove();
            }
        }

        function unassignService(requestId) {
            if (confirm('{{ __('Are you sure you want to unassign this service?') }}')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('admin.service-assignment.unassign') }}';

                var csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                var requestIdInput = document.createElement('input');
                requestIdInput.type = 'hidden';
                requestIdInput.name = 'maintenance_request_id';
                requestIdInput.value = requestId;

                form.appendChild(csrfToken);
                form.appendChild(requestIdInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Filter functionality
        $('#statusFilter, #assignFilter').change(function() {
            filterTable();
        });

        // Clear filters
        $('#clearFilters').click(function() {
            $('#statusFilter').val('');
            $('#assignFilter').val('');
            filterTable();
        });

        function filterTable() {
            var statusFilter = $('#statusFilter').val();
            var assignFilter = $('#assignFilter').val();

            $('tbody tr').each(function() {
                var row = $(this);
                var statusBadge = row.find('td:nth-child(6) .badge');
                var assignedText = row.find('td:nth-child(7) small').text().trim();

                var showRow = true;

                // Status filter
                if (statusFilter) {
                    var statusMatch = false;
                    if (statusFilter === 'pending' && statusBadge.hasClass('bg-warning')) {
                        statusMatch = true;
                    } else if (statusFilter === 'in_progress' && statusBadge.hasClass('bg-info')) {
                        statusMatch = true;
                    } else if (statusFilter === 'completed' && statusBadge.hasClass('bg-success')) {
                        statusMatch = true;
                    }

                    if (!statusMatch) {
                        showRow = false;
                    }
                }

                // Assignment filter
                if (assignFilter) {
                    if (assignFilter === 'assigned' && assignedText.includes('Unassigned')) {
                        showRow = false;
                    } else if (assignFilter === 'unassigned' && !assignedText.includes('Unassigned')) {
                        showRow = false;
                    }
                }

                if (showRow) {
                    row.show();
                } else {
                    row.hide();
                }
            });

            // Update row count display
            updateRowCount();
        }

        function updateRowCount() {
            var visibleRows = $('tbody tr:visible').length;
            var totalRows = $('tbody tr').length;

            if (visibleRows === totalRows) {
                $('#filterCount').text('');
            } else {
                $('#filterCount').text('(' + visibleRows + ' of ' + totalRows + ')');
            }
        }
    </script>
@endpush
