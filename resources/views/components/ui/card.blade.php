{{--
    Kartu standar. Dengan href: seluruh kartu bisa diklik dan ada efek hover.
    <x-ui.card href="...">isi</x-ui.card>
--}}
@props(['href' => null])

@php
    $classes = 'block h-full rounded-xl bg-white p-6 shadow-card';
    if ($href) {
        $classes .= ' transition duration-300 hover:-translate-y-0.5 hover:shadow-card-hover';
    }
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <div {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</div>
@endif
