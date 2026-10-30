{{--
    Judul bagian: label kecil + judul + paragraf pengantar.
    <x-ui.section-title eyebrow="Unit Bisnis" title="..." text="..." align="center" />
    tag  : h1 | h2 | h3 (default h2), light: true untuk latar gelap
--}}
@props([
    'eyebrow' => null,
    'title',
    'text' => null,
    'align' => 'left',
    'tag' => 'h2',
    'light' => false,
])

<div {{ $attributes->merge(['class' => ($align === 'center' ? 'mx-auto text-center ' : '').'max-w-2xl']) }}>
    @if ($eyebrow)
        <p class="eyebrow">{{ $eyebrow }}</p>
    @endif

    <{{ $tag }} class="mt-3 text-3xl leading-tight md:text-4xl {{ $light ? 'text-white' : '' }}">{{ $title }}</{{ $tag }}>

    @if ($text)
        <p class="mt-4 text-base leading-relaxed md:text-lg {{ $light ? 'text-brand-100' : '' }}">{{ $text }}</p>
    @endif
</div>
