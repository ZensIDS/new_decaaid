@props([
    'src' => null,
    'alt' => '',
    'label' => null,
    'lazy' => true,
])

@php
    $exists = $src && file_exists(public_path(ltrim($src, '/')));
    // Tahap C: ukuran asli gambar (width/height) agar browser menyisihkan ruangnya sebelum gambar dimuat (CLS rendah).
    // Hanya bila belum diberikan lewat atribut; gambar vektor (SVG) dilewati. Hasil di-cache menurut path + waktu ubah file.
    $dims = $exists && ! $attributes->has('width') && ! $attributes->has('height')
        ? \App\Support\ImageSize::of($src)
        : null;
    // Tahap C (v90): gambar responsif untuk unggahan admin (storage/...): varian lebih kecil dibuat sekali lalu dipakai
    // lewat srcset. `sizes` bisa diberikan lewat atribut; bawaannya 100vw (aman: tidak pernah memilih gambar terlalu kecil).
    $srcset = $exists && ! $attributes->has('srcset') ? \App\Support\ImageVariants::srcset($src) : null;
@endphp

@if ($exists)
    <img src="{{ str_starts_with(ltrim($src, '/'), 'img/logo/') ? logo_url($src) : asset($src) }}"
         alt="{{ $alt }}"
         @if ($dims) width="{{ $dims[0] }}" height="{{ $dims[1] }}" @endif
         @if ($srcset) srcset="{{ $srcset }}" sizes="{{ $attributes->get('sizes', '100vw') }}" @endif
         @if ($lazy) loading="lazy" decoding="async" @else fetchpriority="high" @endif
         {{ $attributes->except('sizes') }}>
@else
    {{-- Placeholder rapi bila file gambar belum ada (tidak ada gambar rusak) --}}
    <div role="img"
         aria-label="{{ $alt }}"
         {{ $attributes->except('sizes')->merge(['class' => 'relative flex flex-col items-center justify-center gap-2 overflow-hidden bg-gradient-to-br from-brand-100 via-slate-100 to-brand-200 text-brand-700/60']) }}>
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