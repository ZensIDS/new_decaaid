{{--
    Muncul halus saat masuk layar (Alpine intersect). Hormati prefers-reduced-motion.
    <x-ui.reveal>...</x-ui.reveal>
    <x-ui.reveal :delay="150">...</x-ui.reveal>   (delay dalam ms, untuk berurutan)
--}}
@props(['delay' => 0])

<div x-data="{ shown: false }"
     x-intersect.once.margin.-40px="shown = true"
     :class="{ 'is-shown': shown }"
     style="transition-delay: {{ (int) $delay }}ms"
     {{ $attributes->merge(['class' => 'reveal']) }}>
    {{ $slot }}
</div>