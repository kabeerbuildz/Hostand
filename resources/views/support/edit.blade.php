{{Form::model($support, array('route' => array('support.update', $support->id), 'method' => 'PUT','enctype' => "multipart/form-data")) }}
{{ csrf_field() }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-12">
            {{Form::label('subject',__('Subject'),array('class'=>'form-label'))}}
            {{Form::text('subject',null,array('class'=>'form-control','placeholder'=>__('Enter ticket subject')))}}
        </div>
        <div class="form-group col-md-12">
            {{Form::label('priority',__('Priority'),array('class'=>'form-label'))}}
            {{Form::select('priority',$priority,null,array('class'=>'form-control hidesearch'))}}
        </div>
        @if(\Auth::user()->type == 'super admin' || \Auth::user()->type == 'admin')
        <div class="form-group col-md-12">
            {{Form::label('status',__('Status'),array('class'=>'form-label'))}}
            {{Form::select('status',$status,null,array('class'=>'form-control hidesearch'))}}
        </div>
        @endif
        <div class="form-group col-md-12">
            {{Form::label('attachment',__('Add New Photo Attachments'),array('class'=>'form-label'))}}
            {{Form::file('attachment[]',array('class'=>'form-control','multiple'=>'multiple','accept'=>'image/*'))}}
            <small class="form-text text-muted">{{ __('You can upload multiple photos (JPEG, PNG, JPG, GIF) up to 2MB each') }}</small>
        </div>
        <div class="form-group col-md-12">
            {{Form::label('description',__('Description'),array('class'=>'form-label'))}}
            {{Form::textarea('description',null,array('class'=>'form-control','rows'=>5))}}
        </div>
    </div>
</div>
<div class="modal-footer">
    {{Form::submit(__('Update Ticket'),array('class'=>'btn btn-secondary btn-rounded'))}}
</div>
{{ Form::close() }}

