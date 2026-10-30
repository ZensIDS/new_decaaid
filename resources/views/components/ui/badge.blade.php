{{-- <x-ui.badge tone="brand|accent|neutral|dark">Teks</x-ui.badge> --}}
@props(['tone' => 'brand'])

@php
    $tones = [
        'brand' => 'bg-brand-50 text-brand-700',
        'accent' => 'bg-accent-50 text-accent-700',
        'neutral' => 'bg-slate-100 text-slate-600',
        'dark' => 'bg-brand-900 text-white',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold '.($tones[$tone] ?? $tones['brand'])]) }}>
    {{ $slot }}
</span>
