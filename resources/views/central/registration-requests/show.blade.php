<x-app-layout>
    <div class="p-8 max-w-5xl mx-auto">
        <div class="mb-8">
            <a class="inline-flex items-center gap-1 text-primary font-label-md hover:underline mb-4" href="{{ route('registration-requests.index') }}">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                {{ __('Back to registration requests') }}
            </a>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="font-h1 text-h2 text-on-surface mb-2">{{ $registrationRequest->name }}</h2>
                    <p class="font-body-md text-on-surface-variant">
                        {{ __('Submitted :date', ['date' => $registrationRequest->created_at->format('M j, Y g:i A')]) }}
                    </p>
                </div>
                <span class="inline-flex px-2.5 py-1 rounded-md text-xs font-bold {{ $registrationRequest->status->colorClass() }}">
                    {{ $registrationRequest->status->label() }}
                </span>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-secondary-container/30 border border-secondary-container rounded-lg text-body-md text-on-surface">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 bg-error/5 border border-error/20 rounded-lg text-body-md text-error">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl shadow-sm p-6 space-y-6">
                <div>
                    <h3 class="font-h3 text-on-surface mb-4">{{ __('Application details') }}</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Organization') }}</dt>
                            <dd class="text-body-md text-on-surface mt-1">{{ $registrationRequest->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Location') }}</dt>
                            <dd class="text-body-md text-on-surface mt-1">{{ $registrationRequest->location }}</dd>
                        </div>
                        <div>
                            <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Government approval') }}</dt>
                            <dd class="text-body-md text-on-surface mt-1">{{ $registrationRequest->government_approval }}</dd>
                        </div>
                        <div>
                            <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Contact person') }}</dt>
                            <dd class="text-body-md text-on-surface mt-1">{{ $registrationRequest->contact_person }}</dd>
                        </div>
                        <div>
                            <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Email') }}</dt>
                            <dd class="text-body-md text-on-surface mt-1">
                                <a class="text-primary hover:underline" href="mailto:{{ $registrationRequest->email }}">{{ $registrationRequest->email }}</a>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Phone') }}</dt>
                            <dd class="text-body-md text-on-surface mt-1">{{ $registrationRequest->phone }}</dd>
                        </div>
                    </dl>
                </div>

                @if ($registrationRequest->reviewed_at)
                    <div class="border-t border-slate-100 pt-6">
                        <h3 class="font-h3 text-on-surface mb-4">{{ __('Review') }}</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Reviewed by') }}</dt>
                                <dd class="text-body-md text-on-surface mt-1">{{ $registrationRequest->reviewer?->name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Reviewed at') }}</dt>
                                <dd class="text-body-md text-on-surface mt-1">{{ $registrationRequest->reviewed_at->format('M j, Y g:i A') }}</dd>
                            </div>
                            @if ($registrationRequest->rejection_reason)
                                <div>
                                    <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Rejection reason') }}</dt>
                                    <dd class="text-body-md text-on-surface mt-1 whitespace-pre-line">{{ $registrationRequest->rejection_reason }}</dd>
                                </div>
                            @endif
                            @if ($registrationRequest->organization)
                                <div>
                                    <dt class="text-label-sm text-on-surface-variant uppercase">{{ __('Linked clinic') }}</dt>
                                    <dd class="text-body-md text-on-surface mt-1">
                                        <a class="text-primary hover:underline" href="{{ route('clinics.show', $registrationRequest->organization) }}">
                                            {{ $registrationRequest->organization->name }}
                                        </a>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                @can('review', $registrationRequest)
                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                        <h3 class="font-h3 text-on-surface mb-2">{{ __('Approve') }}</h3>
                        <p class="text-body-sm text-on-surface-variant mb-4">
                            {{ __('Approving sends the applicant an onboarding email with a secure link.') }}
                        </p>
                        <form method="POST" action="{{ route('registration-requests.approve', $registrationRequest) }}" onsubmit="return confirm(@js(__('Approve this registration request?')))">
                            @csrf
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-primary text-on-primary px-4 py-2.5 rounded-lg font-label-md hover:opacity-90">
                                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                {{ __('Approve request') }}
                            </button>
                        </form>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-6">
                        <h3 class="font-h3 text-on-surface mb-2">{{ __('Reject') }}</h3>
                        <p class="text-body-sm text-on-surface-variant mb-4">
                            {{ __('Provide a clear reason. The applicant will be notified by email.') }}
                        </p>
                        <form method="POST" action="{{ route('registration-requests.reject', $registrationRequest) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block font-label-md text-on-surface-variant mb-2" for="rejection_reason">{{ __('Rejection reason') }} *</label>
                                <textarea
                                    id="rejection_reason"
                                    name="rejection_reason"
                                    rows="4"
                                    required
                                    class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-body-md @error('rejection_reason') border-error @enderror"
                                    placeholder="{{ __('Explain why this application cannot be approved…') }}"
                                >{{ old('rejection_reason') }}</textarea>
                                @error('rejection_reason')
                                    <p class="mt-1 text-body-sm text-error">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 border border-error/30 text-error px-4 py-2.5 rounded-lg font-label-md hover:bg-error/5">
                                <span class="material-symbols-outlined text-[18px]">cancel</span>
                                {{ __('Reject request') }}
                            </button>
                        </form>
                    </div>
                @else
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-6">
                        <p class="text-body-md text-on-surface-variant">
                            {{ __('This request has already been reviewed and can no longer be changed here.') }}
                        </p>
                    </div>
                @endcan
            </div>
        </div>
    </div>
</x-app-layout>
