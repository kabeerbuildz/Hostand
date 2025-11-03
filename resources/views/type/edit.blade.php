{{ Form::model($type, ['route' => ['type.update', $type->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="form-group">
        {{ Form::label('title', __('Title'), ['class'=>'form-label']) }}
        {{ Form::text('title', null, ['class'=>'form-control','placeholder'=>__('Enter Invoice / Expense / Maintenance Issue, Type Title')]) }}
    </div>
    <div class="form-group">
        {{ Form::label('type', __('Type'), ['class'=>'form-label']) }}
        {!! Form::select('type', collect($types)->mapWithKeys(function($value, $key) {
            return [$key => __($value)]; // Translate each option
        }), null, ['class' => 'form-control hidesearch','required'=>'required']) !!}
    </div>
</div>
<div class="modal-footer">
    {{ Form::submit(__('Update'), ['class'=>'btn btn-secondary btn-rounded']) }}
</div>
{{ Form::close() }}
