@if ($maintenanceRequest->status == 'pending')
    {{-- Show original update form modal --}}
    {{ Form::model($maintenanceRequest, ['route' => ['maintenance-request.action', $maintenanceRequest->id], 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
    <div class="modal-body">
        <div class="row">
            <div class="form-group">
                {{ Form::label('fixed_date', __('Date'), ['class' => 'form-label']) }}
                {{ Form::date('fixed_date', null, ['class' => 'form-control hidesearch']) }}
            </div>
            <div class="form-group">
                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                {{ Form::select('status', $status, $maintenanceRequest->status, ['class' => 'form-control hidesearch']) }}
            </div>
          
            <div class="form-group col-md-12 col-lg-12">
                {{ Form::label('invoice', __('Attachment'), ['class' => 'form-label']) }}
                {{ Form::file('invoice', ['class' => 'form-control']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        {{ Form::submit(__('Update'), ['class' => 'btn btn-secondary btn-rounded']) }}
    </div>
    {{ Form::close() }}
@else
    <div class="modal-body">
        @if (!empty($maintenanceRequest->properties) && $maintenanceRequest->properties->exists)
            <h5 class="text-primary mb-3">{{ __('Property Details') }}</h5>
            <p><strong>{{ __('Property Name') }}:</strong> {{ $maintenanceRequest->properties->name }}</p>
            <p><strong>{{ __('Unit') }}:</strong> {{ $maintenanceRequest->units->name ?? '-' }}</p>
            <p><strong>{{ __('Issue') }}:</strong> {{ $maintenanceRequest->types->title ?? '-' }}</p>
            <p><strong>{{ __('Maintainer') }}:</strong> {{ $maintenanceRequest->maintainers->name ?? '-' }}</p>
            <p><strong>{{ __('Request Date') }}:</strong> {{ dateFormat($maintenanceRequest->request_date) }}</p>
            @if (!empty($maintenanceRequest->issue_attachment))
                <p><strong>{{ __('Attachment') }}:</strong>
                    <a href="{{ asset(Storage::url('upload/issue_attachment') . '/' . $maintenanceRequest->issue_attachment) }}"
                        target="_blank">
                        {{ __('View Attachment') }}
                    </a>
                </p>
            @endif

            <hr>
            <h5 class="text-primary mb-3">{{ __('Service Actions') }}</h5>
            <div class="d-flex gap-2 align-items-center">
                @if ($maintenanceRequest->status == 'in_progress' && !$maintenanceRequest->started_at)
                    <form action="{{ route('operator.start-timer', $maintenanceRequest->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">{{ __('Start') }}</button>
                    </form>
                @endif
                @if ($maintenanceRequest->status == 'in_progress' && $maintenanceRequest->started_at && !$maintenanceRequest->ended_at)
                    <form action="{{ route('operator.stop-timer', $maintenanceRequest->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger">{{ __('End') }}</button>
                    </form>
                @endif
                @if ($maintenanceRequest->ended_at)
                    <span class="badge bg-primary fs-6 p-20 rounded d-flex align-items-center">
                        {{ __('Service Completed on') }}
                        {{ date('d M Y H:i', strtotime($maintenanceRequest->ended_at)) }}
                    </span>
                @endif

            </div>
        @else
            <div class="alert alert-warning">
                {{ __('No property information available.') }}
            </div>
        @endif
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
    </div>


@endif
