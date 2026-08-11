@if (session('success'))
    <div class="mb-6 flex items-center gap-3 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-body-md">
        <span class="material-symbols-outlined text-green-600">check_circle</span>
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-6 flex items-center gap-3 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-body-md">
        <span class="material-symbols-outlined text-red-600">error</span>
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-body-md">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-red-600 mt-0.5">error</span>
            <div>
                <p class="font-label-md mb-2">{{ __('Please fix the following errors:') }}</p>
                <ul class="list-disc list-inside space-y-1 text-body-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
