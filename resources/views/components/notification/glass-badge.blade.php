@props(['count' => 0])

<span 
    x-show="unreadCount > 0"
    x-text="unreadCount > 99 ? '99+' : unreadCount"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="scale-50 opacity-0"
    x-transition:enter-end="scale-100 opacity-100"
    class="absolute -top-1.5 -right-1.5 min-w-[19px] h-[19px] px-1 rounded-full bg-gradient-to-tr from-rose-500 via-rose-600 to-red-600 text-white text-[10px] font-bold flex items-center justify-center shadow-[0_2px_8px_rgba(225,29,72,0.45)] border border-white/80 pointer-events-none select-none z-10"
    style="{{ $count > 0 ? '' : 'display: none;' }}"
>
    {{ $count > 99 ? '99+' : $count }}
</span>
