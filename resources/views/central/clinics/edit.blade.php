<x-app-layout>
    <div class="p-8 max-w-3xl mx-auto">
        <div class="mb-8">
            <a href="{{ route('clinics.index') }}" class="inline-flex items-center gap-1 text-primary font-label-md hover:underline mb-4">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                {{ __('Back to Clinics') }}
            </a>
            <h2 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Edit Clinic') }}</h2>
            <p class="font-body-md text-on-surface-variant">{{ $organization->name }}</p>
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-secondary-container/30 border border-secondary-container rounded-lg text-body-md text-on-surface">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-8">
            <form method="POST" action="{{ route('clinics.update', $organization) }}" class="space-y-6">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="block font-label-md text-on-surface-variant mb-2">{{ __('Clinic Name') }}</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $organization->name) }}" required
                        class="w-full px-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none font-body-md @error('name') border-error @enderror" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <label for="type" class="block font-label-md text-on-surface-variant mb-2">{{ __('Organization Type') }}</label>
                    <select id="type" name="type" required
                        class="w-full px-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none font-body-md">
                        <option value="single" @selected(old('type', $organization->type->value) === 'single')>{{ __('Single Practice') }}</option>
                        <option value="dso" @selected(old('type', $organization->type->value) === 'dso')>{{ __('DSO / Multi-location') }}</option>
                    </select>
                    <x-input-error :messages="$errors->get('type')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block font-label-md text-on-surface-variant mb-2">{{ __('Contact Email') }}</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $organization->email) }}"
                            class="w-full px-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none font-body-md @error('email') border-error @enderror" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <label for="phone" class="block font-label-md text-on-surface-variant mb-2">{{ __('Phone') }}</label>
                        <input id="phone" name="phone" type="text" value="{{ old('phone', $organization->phone) }}"
                            class="w-full px-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none font-body-md @error('phone') border-error @enderror" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <label for="address" class="block font-label-md text-on-surface-variant mb-2">{{ __('Address') }}</label>
                    <textarea id="address" name="address" rows="2"
                        class="w-full px-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none font-body-md @error('address') border-error @enderror">{{ old('address', $organization->address) }}</textarea>
                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                </div>

                <div class="flex items-center gap-3">
                    <input type="hidden" name="is_active" value="0" />
                    <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $organization->is_active))
                        class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary" />
                    <label for="is_active" class="font-body-md text-on-surface-variant">{{ __('Clinic is active') }}</label>
                </div>
                <x-input-error :messages="$errors->get('is_active')" class="mt-2" />

                <div class="flex justify-between items-center pt-4 border-t border-slate-100">
                    <a href="{{ route('clinics.staff.index', $organization) }}" class="inline-flex items-center gap-2 text-primary font-label-md hover:underline">
                        <span class="material-symbols-outlined text-sm">group</span>
                        {{ __('Manage Staff') }}
                    </a>
                    <a href="{{ route('clinics.branches.index', $organization) }}" class="inline-flex items-center gap-2 text-primary font-label-md hover:underline">
                        <span class="material-symbols-outlined text-sm">account_tree</span>
                        {{ __('Manage Branches') }} ({{ $organization->branches_count }})
                    </a>
                    <div class="flex gap-3">
                        <a href="{{ route('clinics.index') }}" class="px-6 py-2.5 border border-outline-variant rounded-lg font-label-md text-on-surface hover:bg-surface-container-low transition-colors">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90 transition-opacity">
                            {{ __('Save Changes') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
