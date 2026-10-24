<div class="flex gap-3">
    <span class="mt-0.5 material-symbols-outlined text-[18px] {{ ($item['unread'] ?? false) ? 'text-primary' : 'text-slate-400' }}">
        {{ ($item['unread'] ?? false) ? 'mark_email_unread' : 'notifications' }}
    </span>
    <div class="min-w-0">
        <p class="text-sm font-semibold text-slate-900 truncate">{{ $item['title'] }}</p>
        <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ $item['body'] }}</p>
        @if (! empty($item['time']))
            <p class="text-[11px] text-slate-400 mt-1">{{ $item['time'] }}</p>
        @endif
    </div>
</div>
