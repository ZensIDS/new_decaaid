{{--
    Tombol / tautan bergaya tombol.
    <x-ui.button href="..." variant="primary" size="lg" icon="arrow-right">Teks</x-ui.button>
    variant: primary | secondary | outline | accent | light | outline-light | link
    size   : sm | md | lg
    Bila ada href dirender <a>, bila tidak dirender <button>.
--}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'icon' => null,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-semibold capitalize transition disabled:cursor-not-allowed disabled:opacity-50';

    $sizes = [
        'sm' => 'rounded-lg px-3.5 py-2 text-sm',
        'md' => 'rounded-lg px-5 py-2.5 text-sm',
        'lg' => 'rounded-lg px-6 py-3.5 text-base',
    ];

    $variants = [
        'primary' => 'bg-brand-700 text-white hover:bg-brand-800',
        'secondary' => 'bg-brand-50 text-brand-800 hover:bg-brand-100',
        'outline' => 'border border-brand-700 text-brand-700 hover:bg-brand-50',
        'accent' => 'bg-accent-500 text-white hover:bg-accent-600',
        'light' => 'bg-white text-brand-800 hover:bg-brand-50',
        'outline-light' => 'border border-white/40 text-white hover:bg-white/10',
        'link' => 'text-brand-700 underline-offset-4 hover:text-brand-800 hover:underline',
    ];

    $classes = $base.' '.($variant === 'link' ? 'rounded' : ($sizes[$size] ?? $sizes['md'])).' '.($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
        @if ($icon)<x-ui.icon :name="$icon" class="h-4 w-4" />@endif
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
        @if ($icon)<x-ui.icon :name="$icon" class="h-4 w-4" />@endif
    </button>
@endif