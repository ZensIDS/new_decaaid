{{--
    Kartu ringkasan angka (dashboard). tone: brand | amber | violet | red | emerald
    <x-admin.stat-card label="Pesan Baru" :value="0" icon="mail" tone="brand" note="..." dummy />
--}}
@props(['label', 'value', 'icon', 'tone' => 'brand', 'note' => null, 'dummy' => false])

@php
    $tones = [
        'brand' => 'from-blue-500 to-indigo-600 shadow-blue-500/30',
        'amber' => 'from-amber-400 to-orange-500 shadow-orange-500/30',
        'violet' => 'from-violet-500 to-purple-600 shadow-purple-500/30',
        'red' => 'from-rose-500 to-red-600 shadow-red-500/30',
        'emerald' => 'from-emerald-400 to-teal-600 shadow-emerald-500/30',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'group rounded-2xl bg-white p-5 shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(16,24,40,0.12)] ring-1 ring-slate-900/5 transition hover:-translate-y-0.5 hover:shadow-lg']) }}>
    <div class="flex items-start justify-between gap-3">
        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br text-white shadow-lg {{ $tones[$tone] ?? $tones['brand'] }}">
            <x-admin.icon :name="$icon" class="h-6 w-6" />
        </span>
        @if ($dummy)<x-admin.badge tone="dummy">Dummy</x-admin.badge>@endif
    </div>
    <p class="mt-5 text-4xl font-bold tracking-tight text-slate-900">{{ $value }}</p>
    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $label }}</p>
    @if ($note)<p class="mt-0.5 text-xs text-slate-500">{{ $note }}</p>@endif
</div>