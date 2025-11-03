@extends('layouts.app')

@section('page-title')
    {{ __('Contact Diary') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item" aria-current="page"> {{ __('Contact Diary') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">

            {{-- Inbox / Received Messages --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>{{ __('Inbox (Received Messages)') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($inbox as $contact)
                            <div class="col-xxl-3 col-xl-4 col-md-6 mb-3">
                                <div class="card follower-card">
                                    <div class="card-body p-3">
                                        <h3 class="mb-1 text-truncate">{{ $contact->name }}</h3>
                                        <h6 class="text-truncate text-muted mb-2">{{ $contact->email }}</h6>
                                        <p class="mb-2">{{ $contact->message }}</p>
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <p class="mb-0 text-muted text-sm">{{ __('Received Date') }}</p>
                                                <h6 class="mb-0">{{ dateFormat($contact->created_at) }}</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">{{ __('No received messages') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Sent Messages --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>{{ __('Sent Messages') }}</h5>
                    @if (Gate::check('create contact'))
                        <a href="#" class="btn btn-secondary customModal" data-size="md"
                           data-url="{{ route('contact.create') }}" data-title="{{ __('Create Contact') }}">
                           <i class="ti ti-circle-plus align-text-bottom"></i> {{ __('Create Contact') }}
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($sent as $contact)
                            <div class="col-xxl-3 col-xl-4 col-md-6 mb-3">
                                <div class="card follower-card bg-light">
                                    <div class="card-body p-3">
                                        <h3 class="mb-1 text-truncate">{{ __('To: ') }} {{ $contact->email }}</h3>
                                        <p class="mb-2">{{ $contact->message }}</p>
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <p class="mb-0 text-muted text-sm">{{ __('Sent Date') }}</p>
                                                <h6 class="mb-0">{{ dateFormat($contact->created_at) }}</h6>
                                            </div>
                                        </div>
                                  
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted">{{ __('No sent messages') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
