@extends('layouts.get-started')

@section('title', __('Request Submitted'))

@section('content')
    <div class="max-w-lg mx-auto text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mb-6">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
        </div>

        <h1 class="font-display text-3xl font-extrabold text-on-surface mb-3">{{ __('Request Submitted!') }}</h1>
        <p class="text-on-surface-variant leading-relaxed mb-8">
            {{ __('Thank you for your interest in DentalOs. Our team will review your application and contact you by email within 2–3 business days.') }}
        </p>

        @if (session('status'))
            <div class="mb-6 p-4 bg-primary/10 border border-primary/20 rounded-xl text-sm text-on-surface">
                {{ session('status') }}
            </div>
        @endif

        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-xl font-semibold hover:bg-primary-dark transition-colors shadow-soft">
            {{ __('Back to Home') }}
        </a>
    </div>
@endsection
