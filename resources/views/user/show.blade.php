@extends('layouts.app')

@section('page-title', __('User Details'))

@section('content')
<div class="card mt-5 shadow-sm border-0">
    <div class="card-header bg-secondary text-white">
        <h5 class="mb-0">{{ __('User Details') }}</h5>
    </div>

    <div class="card-body">
        <div class="row">
            {{-- Role --}}
            @if(Auth::user()->type != 'super admin')
                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted">{{ __('Assigned Role') }}</label>
                    <div class="border rounded p-2 bg-light">
                        {{ !empty($user->roles) ? $user->roles->first()->name : __('No Role Assigned') }}
                    </div>
                </div>
            @endif

            {{-- Name --}}
            @if(Auth::user()->type == 'super admin')
                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted">{{ __('Name') }}</label>
                    <div class="border rounded p-2 bg-light">
                        {{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) }}
                    </div>
                </div>
            @else
                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted">{{ __('First Name') }}</label>
                    <div class="border rounded p-2 bg-light">{{ $user->first_name ?? '—' }}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="fw-bold text-muted">{{ __('Last Name') }}</label>
                    <div class="border rounded p-2 bg-light">{{ $user->last_name ?? '—' }}</div>
                </div>
            @endif

            {{-- Email --}}
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">{{ __('User Email') }}</label>
                <div class="border rounded p-2 bg-light">{{ $user->email ?? '—' }}</div>
            </div>

            {{-- Phone --}}
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">{{ __('User Phone Number') }}</label>
                <div class="border rounded p-2 bg-light">{{ $user->phone_number ?? '—' }}</div>
            </div>

            {{-- Telephone --}}
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">{{ __('Telephone Number') }}</label>
                <div class="border rounded p-2 bg-light">{{ $user->telephone_number ?? '—' }}</div>
            </div>

            {{-- Company --}}
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">{{ __('Company Name') }}</label>
                <div class="border rounded p-2 bg-light">{{ $user->company_name ?? '—' }}</div>
            </div>

            {{-- VAT / Tax --}}
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">{{ __('VAT / Tax Code') }}</label>
                <div class="border rounded p-2 bg-light">{{ $user->vat_tax_code ?? '—' }}</div>
            </div>

            {{-- Residential --}}
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">{{ __('Residential Address') }}</label>
                <div class="border rounded p-2 bg-light">{{ $user->residential_address ?? '—' }}</div>
            </div>

            {{-- Certified Email --}}
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">{{ __('Certified Email') }}</label>
                <div class="border rounded p-2 bg-light">{{ $user->certified_email ?? '—' }}</div>
            </div>

            {{-- SDI Code --}}
            <div class="col-md-6 mb-3">
                <label class="fw-bold text-muted">{{ __('SDI Code') }}</label>
                <div class="border rounded p-2 bg-light">{{ $user->sdi ?? '—' }}</div>
            </div>
        </div>

        <div class="text-end mt-4">
         

            <a href="{{ route('users.index') }}" class="btn btn-light btn-rounded border">
                <i data-feather="arrow-left"></i> {{ __('Back to Users') }}
            </a>
        </div>
    </div>
</div>
@endsection
