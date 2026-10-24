@php
    $inputClass = 'w-full px-4 py-3 bg-surface-container-lowest border border-outline-variant rounded-lg focus:ring-2 focus:ring-primary outline-none font-body-md';
    $initialModal = match (true) {
        old('_form') === 'create' => 'create',
        old('_form') === 'edit' => 'edit',
        old('_form') === 'admin' => 'admin',
        default => null,
    };
    $branchUpdateUrlTemplate = route('clinics.branches.update', [$organization, '__TENANT__']);
    $branchAdminUrlTemplate = route('clinics.branches.primary-admin.update', [$organization, '__TENANT__']);
    $branchDeleteUrlTemplate = route('clinics.branches.destroy', [$organization, '__TENANT__']);
@endphp

<x-app-layout>
    <div
        class="p-8 max-w-5xl mx-auto"
        x-data="{
            modal: @js($initialModal),
            appHost: @js($appHost),
            organizationSlug: @js($organization->slug),
            createName: @js(old('_form') === 'create' ? old('branch_name', '') : ''),
            createSlug: @js(old('_form') === 'create' ? old('branch_slug', '') : ''),
            editId: @js(old('_form') === 'edit' ? old('branch_id') : null),
            editName: @js(old('_form') === 'edit' ? old('branch_name', '') : ''),
            editActive: {{ old('_form') === 'edit' ? (old('is_active') ? 'true' : 'false') : 'true' }},
            adminId: @js(old('_form') === 'admin' ? old('branch_id') : null),
            adminBranchName: '',
            adminName: @js(old('_form') === 'admin' ? old('admin_name', '') : ''),
            adminEmail: @js(old('_form') === 'admin' ? old('admin_email', '') : ''),
            deleteId: null,
            deleteName: '',
            slugify(value) {
                return String(value || '')
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '')
                    .slice(0, 63);
            },
            syncCreateSlug() {
                this.createSlug = this.slugify(this.createName);
            },
            pathPreview(slug) {
                if (! slug) {
                    return '';
                }

                return `${this.appHost}/${this.organizationSlug}/${slug}`;
            },
            openCreate() {
                this.createName = '';
                this.createSlug = '';
                this.modal = 'create';
            },
            openEdit(branch) {
                this.editId = branch.id;
                this.editName = branch.name;
                this.editActive = !! branch.is_active;
                this.modal = 'edit';
            },
            openAdmin(branch) {
                this.adminId = branch.id;
                this.adminBranchName = branch.name;
                this.adminName = branch.admin_name || '';
                this.adminEmail = branch.admin_email || '';
                this.modal = 'admin';
            },
            openDelete(branch) {
                this.deleteId = branch.id;
                this.deleteName = branch.name;
                this.modal = 'delete';
            },
            closeModal() {
                this.modal = null;
            },
        }"
        @keydown.escape.window="closeModal()"
    >
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <a href="{{ route('clinics.edit', $organization) }}" class="inline-flex items-center gap-1 text-primary font-label-md hover:underline mb-4">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    {{ __('Back to Clinic') }}
                </a>
                <h2 class="font-h1 text-h2 text-on-surface mb-2">{{ __('Manage Branches') }}</h2>
                <p class="font-body-md text-on-surface-variant">{{ $organization->name }}</p>
            </div>

            <button
                type="button"
                @click="openCreate()"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90 transition-opacity"
            >
                <span class="material-symbols-outlined text-sm">add</span>
                {{ __('Add Branch') }}
            </button>
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-secondary-container/30 border border-secondary-container rounded-lg text-body-md text-on-surface">
                {{ session('status') }}
            </div>
        @endif

        <x-input-error :messages="$errors->get('branch')" class="mb-6" />

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between gap-4">
                <h3 class="font-h3 text-on-surface">{{ __('Branches') }}</h3>
                <span class="text-body-sm text-on-surface-variant">{{ trans_choice(':count branch|:count branches', $branches->count(), ['count' => $branches->count()]) }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Branch') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Primary admin') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Access path') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-6 py-4 font-label-md text-on-surface-variant uppercase tracking-wider text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($branches as $branch)
                            @php
                                $branchAdmin = $branchAdmins[(string) $branch->id] ?? null;
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-900 flex items-center gap-2">
                                        {{ $branch->name }}
                                        @if ($organization->isAdminBranch($branch))
                                            <span class="text-[10px] uppercase tracking-wide font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-100">{{ __('Admin') }}</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500">{{ $branch->slug }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($branchAdmin)
                                        <div class="text-slate-900">{{ $branchAdmin['name'] }}</div>
                                        <div class="text-xs text-slate-500">{{ $branchAdmin['email'] }}</div>
                                        @unless ($organization->isAdminBranch($branch))
                                            <div class="text-[10px] uppercase tracking-wide text-slate-400 mt-1">{{ __('Synced from primary clinic') }}</div>
                                        @endunless
                                    @else
                                        <span class="text-slate-400">{{ __('Not set') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-body-md">
                                    <a
                                        href="{{ \App\Support\TenantPortal::loginUrl($branch) }}"
                                        target="_blank"
                                        class="text-primary hover:underline break-all"
                                    >
                                        {{ $appHost }}/{{ \App\Support\TenantPortal::pathPrefix($branch) }}
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($branch->is_active)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-green-50 text-green-700 border border-green-200">
                                            {{ __('ACTIVE') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-bold bg-red-50 text-red-700 border border-red-200">
                                            {{ __('INACTIVE') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-1">
                                        @if (auth()->user()?->isSuperAdmin() && $organization->isAdminBranch($branch))
                                            <button
                                                type="button"
                                                class="p-2 text-slate-400 hover:text-primary hover:bg-blue-50 rounded-lg transition-all"
                                                title="{{ __('Edit primary admin') }}"
                                                @click="openAdmin(@js([
                                                    'id' => $branch->id,
                                                    'name' => $branch->name,
                                                    'admin_name' => $branchAdmin['name'] ?? '',
                                                    'admin_email' => $branchAdmin['email'] ?? '',
                                                ]))"
                                            >
                                                <span class="material-symbols-outlined text-sm">manage_accounts</span>
                                            </button>
                                        @endif
                                        <button
                                            type="button"
                                            class="p-2 text-slate-400 hover:text-primary hover:bg-blue-50 rounded-lg transition-all"
                                            title="{{ __('Edit') }}"
                                            @click="openEdit(@js([
                                                'id' => $branch->id,
                                                'name' => $branch->name,
                                                'is_active' => (bool) $branch->is_active,
                                            ]))"
                                        >
                                            <span class="material-symbols-outlined text-sm">edit</span>
                                        </button>
                                        <button
                                            type="button"
                                            class="p-2 text-slate-400 hover:text-error hover:bg-red-50 rounded-lg transition-all disabled:opacity-40 disabled:pointer-events-none"
                                            title="{{ __('Delete') }}"
                                            @click="openDelete(@js([
                                                'id' => $branch->id,
                                                'name' => $branch->name,
                                            ]))"
                                            @disabled($branches->count() <= 1)
                                        >
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-body-md text-on-surface-variant">
                                    {{ __('No branches yet. Add the first branch to get started.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Create modal --}}
        <div
            x-show="modal === 'create'"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="{{ $initialModal === 'create' ? '' : 'display: none;' }}"
        >
            <div class="absolute inset-0 bg-slate-900/40" @click="closeModal()"></div>
            <div class="relative w-full max-w-lg bg-white rounded-xl border border-slate-200 shadow-xl p-6" @click.stop>
                <div class="flex items-start justify-between gap-4 mb-6">
                    <div>
                        <h3 class="font-h3 text-on-surface">{{ __('Add Branch') }}</h3>
                        <p class="text-body-sm text-on-surface-variant mt-1">{{ __('Create another location for this clinic.') }}</p>
                    </div>
                    <button type="button" class="p-1 text-slate-400 hover:text-on-surface" @click="closeModal()">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form method="POST" action="{{ route('clinics.branches.store', $organization) }}" class="space-y-4" @submit="syncCreateSlug()">
                    @csrf
                    <input type="hidden" name="_form" value="create" />

                    <div>
                        <label for="create_branch_name" class="block font-label-md text-on-surface-variant mb-2">{{ __('Branch Name') }}</label>
                        <input
                            id="create_branch_name"
                            name="branch_name"
                            type="text"
                            x-model="createName"
                            @input="syncCreateSlug()"
                            required
                            placeholder="{{ __('e.g. Uptown Clinic') }}"
                            class="{{ $inputClass }} @error('branch_name') border-error @enderror"
                        />
                        <x-input-error :messages="old('_form') === 'create' ? $errors->get('branch_name') : []" class="mt-2" />
                    </div>

                    <div>
                        <label for="create_branch_slug" class="block font-label-md text-on-surface-variant mb-2">{{ __('Branch Slug') }}</label>
                        <input
                            id="create_branch_slug"
                            name="branch_slug"
                            type="text"
                            x-model="createSlug"
                            readonly
                            tabindex="-1"
                            class="{{ $inputClass }} bg-surface-container-low text-on-surface-variant @error('branch_slug') border-error @enderror"
                        />
                        <p class="mt-1 text-body-sm text-outline" x-show="createSlug" x-cloak>
                            {{ __('This is where it will be accessed:') }}
                            <span class="font-medium text-on-surface" x-text="pathPreview(createSlug)"></span>
                        </p>
                        <x-input-error :messages="old('_form') === 'create' ? $errors->get('branch_slug') : []" class="mt-2" />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="closeModal()" class="px-5 py-2.5 border border-outline-variant rounded-lg font-label-md text-on-surface hover:bg-surface-container-low transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90 transition-opacity">
                            {{ __('Create Branch') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit modal --}}
        <div
            x-show="modal === 'edit'"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="{{ $initialModal === 'edit' ? '' : 'display: none;' }}"
        >
            <div class="absolute inset-0 bg-slate-900/40" @click="closeModal()"></div>
            <div class="relative w-full max-w-lg bg-white rounded-xl border border-slate-200 shadow-xl p-6" @click.stop>
                <div class="flex items-start justify-between gap-4 mb-6">
                    <div>
                        <h3 class="font-h3 text-on-surface">{{ __('Edit Branch') }}</h3>
                        <p class="text-body-sm text-on-surface-variant mt-1">{{ __('Update the branch name or active status.') }}</p>
                    </div>
                    <button type="button" class="p-1 text-slate-400 hover:text-on-surface" @click="closeModal()">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form
                    method="POST"
                    class="space-y-4"
                    :action="editId ? @js($branchUpdateUrlTemplate).replace('__TENANT__', editId) : '#'"
                >
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="_form" value="edit" />
                    <input type="hidden" name="branch_id" :value="editId" />

                    <div>
                        <label for="edit_branch_name" class="block font-label-md text-on-surface-variant mb-2">{{ __('Branch Name') }}</label>
                        <input
                            id="edit_branch_name"
                            name="branch_name"
                            type="text"
                            x-model="editName"
                            required
                            class="{{ $inputClass }} @error('branch_name') border-error @enderror"
                        />
                        <x-input-error :messages="old('_form') === 'edit' ? $errors->get('branch_name') : []" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="hidden" name="is_active" value="0" />
                        <input
                            id="edit_is_active"
                            name="is_active"
                            type="checkbox"
                            value="1"
                            x-model="editActive"
                            class="w-4 h-4 text-primary border-outline-variant rounded focus:ring-primary"
                        />
                        <label for="edit_is_active" class="font-body-md text-on-surface-variant">{{ __('Branch is active') }}</label>
                    </div>
                    <x-input-error :messages="old('_form') === 'edit' ? $errors->get('is_active') : []" class="mt-2" />

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="closeModal()" class="px-5 py-2.5 border border-outline-variant rounded-lg font-label-md text-on-surface hover:bg-surface-container-low transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90 transition-opacity">
                            {{ __('Save Changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Primary admin modal --}}
        <div
            x-show="modal === 'admin'"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="{{ $initialModal === 'admin' ? '' : 'display: none;' }}"
        >
            <div class="absolute inset-0 bg-slate-900/40" @click="closeModal()"></div>
            <div class="relative w-full max-w-lg bg-white rounded-xl border border-slate-200 shadow-xl p-6" @click.stop>
                <div class="flex items-start justify-between gap-4 mb-6">
                    <div>
                        <h3 class="font-h3 text-on-surface">{{ __('Edit primary admin') }}</h3>
                        <p class="text-body-sm text-on-surface-variant mt-1">
                            {{ __('Update the clinic admin account for') }}
                            <span class="font-medium text-on-surface" x-text="adminBranchName"></span>.
                        </p>
                    </div>
                    <button type="button" class="p-1 text-slate-400 hover:text-on-surface" @click="closeModal()">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form
                    method="POST"
                    class="space-y-4"
                    :action="adminId ? @js($branchAdminUrlTemplate).replace('__TENANT__', adminId) : '#'"
                >
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="_form" value="admin" />
                    <input type="hidden" name="branch_id" :value="adminId" />

                    <div>
                        <label for="admin_name" class="block font-label-md text-on-surface-variant mb-2">{{ __('Admin name') }}</label>
                        <input id="admin_name" name="admin_name" type="text" x-model="adminName" required class="{{ $inputClass }}" />
                        <x-input-error :messages="old('_form') === 'admin' ? $errors->get('admin_name') : []" class="mt-2" />
                    </div>
                    <div>
                        <label for="admin_email" class="block font-label-md text-on-surface-variant mb-2">{{ __('Admin email') }}</label>
                        <input id="admin_email" name="admin_email" type="email" x-model="adminEmail" required class="{{ $inputClass }}" />
                        <x-input-error :messages="old('_form') === 'admin' ? $errors->get('admin_email') : []" class="mt-2" />
                    </div>
                    <x-password-with-generate
                        id="admin_password"
                        name="admin_password"
                        :label="__('New password')"
                        :with-confirmation="true"
                        :hint="__('Leave blank to keep the current password. Required when creating a new admin.')"
                        :input-class="$inputClass"
                        error-bag="admin_password"
                    />

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="closeModal()" class="px-5 py-2.5 border border-outline-variant rounded-lg font-label-md text-on-surface hover:bg-surface-container-low transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-primary text-on-primary rounded-lg font-label-md hover:opacity-90 transition-opacity">
                            {{ __('Save primary admin') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Delete modal --}}
        <div
            x-show="modal === 'delete'"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display: none;"
        >
            <div class="absolute inset-0 bg-slate-900/40" @click="closeModal()"></div>
            <div class="relative w-full max-w-md bg-white rounded-xl border border-slate-200 shadow-xl p-6" @click.stop>
                <div class="mb-6">
                    <h3 class="font-h3 text-on-surface">{{ __('Delete Branch') }}</h3>
                    <p class="text-body-md text-on-surface-variant mt-2">
                        {{ __('Are you sure you want to delete') }}
                        <span class="font-medium text-on-surface" x-text="deleteName"></span>?
                        {{ __('This removes the branch database and cannot be undone.') }}
                    </p>
                </div>

                <form method="POST" :action="deleteId ? @js($branchDeleteUrlTemplate).replace('__TENANT__', deleteId) : '#'">
                    @csrf
                    @method('DELETE')

                    <div class="flex justify-end gap-3">
                        <button type="button" @click="closeModal()" class="px-5 py-2.5 border border-outline-variant rounded-lg font-label-md text-on-surface hover:bg-surface-container-low transition-colors">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-red-600 text-white rounded-lg font-label-md hover:bg-red-700 transition-colors">
                            {{ __('Delete Branch') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
