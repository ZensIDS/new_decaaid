@props([
    'src' => null,
    'alt' => '',
    'label' => null,
    'lazy' => true,
])

@php
    $exists = $src && file_exists(public_path(ltrim($src, '/')));
@endphp

@if ($exists)
    <img src="{{ str_starts_with(ltrim($src, '/'), 'img/logo/') ? logo_url($src) : asset($src) }}"
         alt="{{ $alt }}"
         @if ($lazy) loading="lazy" decoding="async" @else fetchpriority="high" @endif
         {{ $attributes }}>
@else
    {{-- Placeholder rapi bila file gambar belum ada (tidak ada gambar rusak) --}}
    <div role="img"
         aria-label="{{ $alt }}"
         {{ $attributes->merge(['class' => 'relative flex flex-col items-center justify-center gap-2 overflow-hidden bg-gradient-to-br from-brand-100 via-slate-100 to-brand-200 text-brand-700/60']) }}>
        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <rect x="3" y="4" width="18" height="16" rx="2.5"></rect>
            <circle cx="8.5" cy="9.5" r="1.5"></circle>
            <path d="m21 16-5.2-5.2a1 1 0 0 0-1.4 0L6 19"></path>
        </svg>
        @if ($label)
            <span class="px-3 text-center text-xs font-semibold leading-tight">{{ $label }}</span>
        @endif
    </div>
@endif