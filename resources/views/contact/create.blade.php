{{ Form::open(['url' => 'contact', 'method' => 'post']) }}
@php
$isAdmin = \Auth::check() && \Auth::user()->type === 'owner';
    $hostandName = 'Hostand';
    $hostandEmail = 'servizi.atman@gmail.com';
    $hostandContact = '+39 3509750228';
@endphp
<div class="modal-body">
    <div class="row">
        {{-- Owner Search Field --}}
        <div class="form-group col-md-12">
            {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
            @if($isAdmin)
                {{ Form::text('name', $hostandName, ['class' => 'form-control', 'readonly' => true]) }}
            @else
                <select id="owner_search" name="name" class="form-control" style="width: 100%;"></select>
            @endif
        </div>

        {{-- Email Input --}}
        <div class="form-group col-md-12">
            {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
            {{ Form::text('email', $isAdmin ? $hostandEmail : null, ['class' => 'form-control', 'placeholder' => __('Enter contact email'), 'readonly' => $isAdmin]) }}
        </div>

        {{-- Contact Number --}}
        <div class="form-group col-md-12">
            {{ Form::label('contact_number', __('Contact Number'), ['class' => 'form-label']) }}
            {{ Form::text('contact_number', $isAdmin ? $hostandContact : null, ['class' => 'form-control', 'placeholder' => __('Enter contact number'), 'readonly' => $isAdmin]) }}
        </div>

        {{-- Subject --}}
        <div class="form-group col-md-12">
            {{ Form::label('subject', __('Subject'), ['class' => 'form-label']) }}
            {{ Form::text('subject', null, ['class' => 'form-control', 'placeholder' => __('Enter contact subject')]) }}
        </div>

        {{-- Message --}}
        <div class="form-group col-md-12">
            {{ Form::label('message', __('Message'), ['class' => 'form-label']) }}
            {{ Form::textarea('message', null, ['class' => 'form-control', 'rows' => 5, 'placeholder' => __('Enter your message')]) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    {{ Form::submit(__('Create'), ['class' => 'btn btn-secondary btn-rounded']) }}
</div>

@if(!$isAdmin)
<!-- Load jQuery first -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Load Select2 CSS/JS once -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endif

@if(!$isAdmin)
<script>
$(document).ready(function() {
    console.log('[Step 1] Document ready fired');

    if (!$.fn.select2) {
        console.error('[Step 2] Select2 is not loaded!');
        return;
    }
    console.log('[Step 2] Select2 is loaded');

    var $ownerSearch = $('#owner_search');

    if (!$ownerSearch.length) {
        console.error('[Step 3] #owner_search element not found!');
        return;
    }
    console.log('[Step 3] #owner_search element found');

    $ownerSearch.select2({
        placeholder: 'Search owner by name',
        allowClear: true,
        dropdownParent: $ownerSearch.closest('.modal'),
        ajax: {
            url: '{{ route('users.search') }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                console.log('[Step 4] AJAX data function called. Search term:', params.term);
                return { q: params.term };
            },
            processResults: function(data) {
                console.log('[Step 5] processResults called. Data received:', data);
                return { results: data.results };
            },
            cache: true,
            error: function(xhr, status, error) {
                console.error('[Step 6] AJAX error:', status, error, xhr.responseText);
            }
        },
        templateResult: function(user) {
            console.log('[Step 7] templateResult called for:', user);
            if (!user.id) return user.text;
            return user.text + (user.email ? ' (' + user.email + ')' : '');
        },
        templateSelection: function(user) {
            console.log('[Step 8] templateSelection called for:', user);
            return user.text || user.id;
        }
    });
    console.log('[Step 9] Select2 initialized');

    $ownerSearch.on('select2:select', function(e) {
        var data = e.params.data;
        console.log('[Step 10] select2:select fired. Selected data:', data);
        $('input[name="email"]').val(data.email || '');
        $('input[name="contact_number"]').val(data.contact || '');
    });

    $ownerSearch.on('select2:clear', function() {
        console.log('[Step 11] select2:clear fired');
        $('input[name="email"]').val('');
        $('input[name="contact_number"]').val('');
    });

    console.log('[Step 12] Script setup complete');
});
</script>
@endif
{{ Form::close() }}
