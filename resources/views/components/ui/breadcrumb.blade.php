{{--
    <x-ui.breadcrumb :items="[['Beranda', lroute('home')], ['Tentang Kami', lroute('about.profile')], ['Profil Perusahaan', null]]" />
    Item terakhir (url null) = halaman saat ini.
--}}
@props(['items' => [], 'light' => false])

<nav aria-label="Breadcrumb" {{ $attributes }}>
    <ol class="flex flex-wrap items-center gap-1.5 text-sm {{ $light ? 'text-brand-100' : 'text-slate-500' }}">
        @foreach ($items as [$label, $url])
            <li class="flex items-center gap-1.5">
                @if (! $loop->first)
                    <x-ui.icon name="chevron-right" class="h-3.5 w-3.5 opacity-60" />
                @endif

                @if ($url)
                    <a href="{{ $url }}" class="transition {{ $light ? 'hover:text-white' : 'hover:text-brand-700' }}">{{ $label }}</a>
                @else
                    <span aria-current="page" class="font-semibold {{ $light ? 'text-white' : 'text-ink' }}">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
