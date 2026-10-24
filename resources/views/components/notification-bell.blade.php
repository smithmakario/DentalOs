@props([
    'items' => collect(),
    'unreadCount' => 0,
])

<div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
    <button
        type="button"
        @click="open = ! open"
        class="p-2 text-slate-500 hover:bg-slate-50 rounded-full transition-colors relative"
        aria-label="{{ __('Notifications') }}"
    >
        <span class="material-symbols-outlined" data-icon="notifications">notifications</span>
        @if ($unreadCount > 0)
            <span class="absolute top-1.5 right-1.5 min-w-[0.5rem] h-2 px-0.5 rounded-full bg-error"></span>
        @endif
    </button>

    <div
        x-cloak
        x-show="open"
        @click.outside="open = false"
        x-transition
        class="absolute right-0 mt-2 w-96 max-w-[calc(100vw-2rem)] bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden"
    >
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <p class="font-label-md text-on-surface">{{ __('Notifications') }}</p>
            @if ($unreadCount > 0)
                <span class="text-xs font-semibold text-primary">{{ trans_choice(':count new|:count new', $unreadCount, ['count' => $unreadCount]) }}</span>
            @endif
        </div>
        <ul class="max-h-96 overflow-y-auto divide-y divide-slate-100">
            @forelse ($items as $item)
                <li>
                    @if (! empty($item['url']))
                        <a href="{{ $item['url'] }}" class="block px-4 py-3 hover:bg-slate-50 transition-colors">
                            @include('components.partials.notification-item', ['item' => $item])
                        </a>
                    @else
                        <div class="px-4 py-3">
                            @include('components.partials.notification-item', ['item' => $item])
                        </div>
                    @endif
                </li>
            @empty
                <li class="px-4 py-8 text-center text-sm text-slate-500">{{ __('No notifications yet.') }}</li>
            @endforelse
        </ul>
    </div>
</div>
