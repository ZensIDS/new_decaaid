{{--
    Tombol admin. Bila ada href dirender <a>, bila tidak <button>.
    <x-admin.button variant="primary" icon="plus" href="...">Tambah</x-admin.button>
    variant: primary | secondary | danger | ghost     size: sm | md
    Teks tombol otomatis Capitalize Each Word (K17).
--}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'icon' => null,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-xl font-semibold capitalize transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

    $sizes = [
        'sm' => 'px-3.5 py-2 text-xs',
        'md' => 'px-5 py-2.5 text-sm',
    ];

    $variants = [
        'primary' => 'bg-gradient-to-r from-[#1B3590] to-[#3558D6] text-white shadow-md shadow-blue-900/20 hover:brightness-110 focus-visible:ring-[#1B3590]',
        'secondary' => 'bg-white text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 hover:bg-slate-50 focus-visible:ring-[#1B3590]',
        'danger' => 'bg-gradient-to-r from-[#E5352E] to-[#F0584F] text-white shadow-md shadow-red-900/20 hover:brightness-110 focus-visible:ring-[#E5352E]',
        'ghost' => 'text-slate-600 hover:bg-slate-100 focus-visible:ring-[#1B3590]',
    ];

    $classes = $base.' '.($sizes[$size] ?? $sizes['md']).' '.($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-admin.icon :name="$icon" class="h-4 w-4" />@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-admin.icon :name="$icon" class="h-4 w-4" />@endif
        {{ $slot }}
    </button>
@endif