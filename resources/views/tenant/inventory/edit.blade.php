<x-tenant-layout>
    <div class="p-8 max-w-3xl mx-auto">
        @include('layouts.partials.flash-messages')

        <div class="mb-8">
            <a href="{{ route('tenant.inventory.show', $item) }}" class="text-sm text-slate-500 hover:text-primary flex items-center gap-1 mb-4">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>{{ __('Back to Item') }}
            </a>
            <h2 class="font-h1 text-h1 text-on-surface">{{ __('Edit Inventory Item') }}</h2>
            <p class="font-body-md text-body-md text-on-surface-variant mt-1">{{ $item->name }}</p>
        </div>

        <form action="{{ route('tenant.inventory.update', $item) }}" method="POST" class="bg-surface-container-lowest border border-outline-variant rounded-xl p-8 shadow-sm space-y-6">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block font-label-md text-label-md text-on-surface mb-1" for="name">{{ __('Item Name') }} <span class="text-error">*</span></label>
                    <input id="name" name="name" type="text" value="{{ old('name', $item->name) }}"
                        class="w-full border border-outline rounded-lg px-4 py-2.5 text-on-surface bg-surface focus:ring-2 focus:ring-primary outline-none @error('name') border-error @enderror"
                        required />
                    @error('name')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-1" for="inventory_category_id">{{ __('Category') }} <span class="text-error">*</span></label>
                    <select id="inventory_category_id" name="inventory_category_id" required
                        class="w-full border border-outline rounded-lg px-4 py-2.5 text-on-surface bg-surface focus:ring-2 focus:ring-primary outline-none @error('inventory_category_id') border-error @enderror">
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('inventory_category_id', $item->inventory_category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('inventory_category_id')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-1" for="type">{{ __('Type') }} <span class="text-error">*</span></label>
                    <select id="type" name="type" required
                        class="w-full border border-outline rounded-lg px-4 py-2.5 text-on-surface bg-surface focus:ring-2 focus:ring-primary outline-none @error('type') border-error @enderror">
                        @foreach ($types as $type)
                            <option value="{{ $type->value }}" @selected(old('type', $item->type->value) === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    @error('type')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-1" for="unit_measure">{{ __('Unit of Measure') }} <span class="text-error">*</span></label>
                    <input id="unit_measure" name="unit_measure" type="text" value="{{ old('unit_measure', $item->unit_measure) }}"
                        class="w-full border border-outline rounded-lg px-4 py-2.5 text-on-surface bg-surface focus:ring-2 focus:ring-primary outline-none @error('unit_measure') border-error @enderror" required />
                    @error('unit_measure')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-1" for="sku">{{ __('SKU / Item Code') }}</label>
                    <input id="sku" name="sku" type="text" value="{{ old('sku', $item->sku) }}"
                        class="w-full border border-outline rounded-lg px-4 py-2.5 text-on-surface bg-surface focus:ring-2 focus:ring-primary outline-none" />
                    @error('sku')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block font-label-md text-label-md text-on-surface mb-1" for="min_stock_level">{{ __('Minimum Stock Level') }}</label>
                    <input id="min_stock_level" name="min_stock_level" type="number" min="0" step="0.01" value="{{ old('min_stock_level', $item->min_stock_level) }}"
                        class="w-full border border-outline rounded-lg px-4 py-2.5 text-on-surface bg-surface focus:ring-2 focus:ring-primary outline-none @error('min_stock_level') border-error @enderror" />
                    @error('min_stock_level')<p class="text-error text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-3 pt-6">
                    <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', $item->is_active))
                        class="w-4 h-4 text-primary border-outline rounded focus:ring-primary" />
                    <label for="is_active" class="font-label-md text-label-md text-on-surface">{{ __('Active') }}</label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-outline-variant">
                <a href="{{ route('tenant.inventory.show', $item) }}" class="px-5 py-2.5 rounded-lg border border-outline font-label-md text-label-md text-on-surface hover:bg-surface-container transition-colors">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:opacity-90 transition-opacity shadow-md shadow-primary/20">
                    {{ __('Save Changes') }}
                </button>
            </div>
        </form>
    </div>
</x-tenant-layout>
