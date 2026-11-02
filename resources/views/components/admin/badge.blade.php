{{--
    Penanda status. <x-admin.badge tone="success">Aktif</x-admin.badge>
    tone: neutral | brand | success | warning | danger | dummy
    "dummy" dipakai untuk menandai data contoh yang harus diganti (Aturan Kerja no. 8).
--}}
@props(['tone' => 'neutral'])

@php
    $tones = [
        'neutral' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'brand' => 'bg-blue-50 text-[#1B3590] ring-blue-200',
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'warning' => 'bg-amber-50 text-amber-800 ring-amber-200',
        'danger' => 'bg-red-50 text-[#c92a24] ring-red-200',
        'dummy' => 'bg-fuchsia-50 text-fuchsia-700 ring-fuchsia-200',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset '.($tones[$tone] ?? $tones['neutral'])]) }}>{{ $slot }}</span>