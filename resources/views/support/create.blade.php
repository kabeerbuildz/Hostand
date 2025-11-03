{{ Form::open(array('url'=>'support','method'=>'post', 'enctype' => "multipart/form-data")) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('subject', __('Subject'), array('class'=>'form-label')) }}
            {{ Form::text('subject', null, array('class'=>'form-control','placeholder'=>__('Enter ticket subject'))) }}
        </div>

      {{-- Service Request Dropdown --}}
<div class="form-group col-md-12">
    {{ Form::label('request_id', __('Service Request'), ['class'=>'form-label']) }}
    
    {{ Form::select(
        'request_id',
        $requests->mapWithKeys(function($req) {
            return [
                $req->id => $req->properties->name . ' (' . $req->types->title . ')'
            ];
        }),
        null,
        ['class'=>'form-control hidesearch', 'placeholder'=>__('Select service request')]
    ) }}
    <small>You can create ticket for todays requests only</small>
</div>



        <div class="form-group col-md-12">
            {{ Form::label('priority', __('Priority'), array('class'=>'form-label')) }}
            {{ Form::select('priority', $priority, null, array('class'=>'form-control hidesearch')) }}
        </div>

        @if(\Auth::user()->type == 'super admin' || \Auth::user()->type == 'admin')
        <div class="form-group col-md-12">
            {{ Form::label('assign_user', __('Assign To'), array('class'=>'form-label')) }}
            {{ Form::select('assign_user', $admins->pluck('name','id'), null, array('class'=>'form-control hidesearch','placeholder'=>__('Select user to assign...'))) }}
        </div>
        @endif

        <div class="form-group col-md-12">
            {{ Form::label('attachment', __('Photo Attachments'), array('class'=>'form-label')) }}
            {{ Form::file('attachment[]', array('class'=>'form-control','multiple'=>'multiple','accept'=>'image/*')) }}
            <small class="form-text text-muted">{{ __('You can upload multiple photos (JPEG, PNG, JPG, GIF) up to 2MB each') }}</small>
        </div>

        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'), array('class'=>'form-label')) }}
            {{ Form::textarea('description', null, array('class'=>'form-control','rows'=>5,'placeholder'=>__('Describe the problem in detail...'))) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Create Ticket'), array('class'=>'btn btn-secondary btn-rounded')) }}
</div>
{{ Form::close() }}
