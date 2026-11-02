{{--
    Penanda ikon Pilar Bisnis (jenis bisnis tidak punya logo sendiri; logo ada di tingkat brand).
    <x-ui.pillar-mark :unit="$unit" class="h-14 w-14" />   (ukuran ikon otomatis, atur kotak lewat class)
    $unit['icon'] = nama ikon di components/ui/icon.
--}}
@props(['unit', 'dark' => false])

<span {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center rounded-2xl '.($dark ? 'bg-white/10 text-white ring-1 ring-white/20' : 'bg-brand-50 text-brand-700 ring-1 ring-brand-100')]) }}
      aria-hidden="true">
    <x-ui.icon :name="$unit['icon'] ?? 'briefcase'" class="h-1/2 w-1/2" />
</span>