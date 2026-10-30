{{--
    Daftar berita + chip kategori + paginasi (dipakai halaman Berita dan Kategori).
    Variabel: $items (array berita), $active (slug kategori atau null), $withFeatured (bool)
--}}
@php
    $page = \App\Support\Content::newsPage();
    $cats = \App\Support\Content::newsCategories();
    $perPage = 7;
    $all = collect($items);
    $last = max(1, (int) ceil($all->count() / $perPage));
    $current = min($last, max(1, (int) request()->query('page', 1)));
    $slice = $all->forPage($current, $perPage)->values();
    $featured = ($withFeatured ?? false) && $current === 1 ? $slice->first() : null;
    $grid = $featured ? $slice->slice(1)->values() : $slice;
    $fmt = fn ($d) => \Carbon\Carbon::parse($d)->translatedFormat('d F Y');
    $chip = 'rounded-full px-4 py-2 text-sm font-semibold capitalize transition';
@endphp

{{-- Chip kategori --}}
<div class="flex flex-wrap gap-2" role="navigation" aria-label="{{ $page['eyebrow'] }}">
    <a href="{{ lroute('news.index') }}" @if (! $active) aria-current="page" @endif
       class="{{ $chip }} {{ ! $active ? 'bg-brand-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-brand-50 hover:text-brand-700' }}">{{ $page['all'] }}</a>
    @foreach ($cats as $slug => $name)
        <a href="{{ lroute('news.category', ['slug' => $slug]) }}" @if ($active === $slug) aria-current="page" @endif
           class="{{ $chip }} {{ $active === $slug ? 'bg-brand-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-brand-50 hover:text-brand-700' }}">{{ $name }}</a>
    @endforeach
</div>

@if ($all->isEmpty())
    <p class="mt-8 rounded-xl bg-slate-50 p-8 text-center text-base">{{ $page['empty'] }}</p>
@else
    {{-- Berita utama (hanya halaman 1 di daftar semua berita) --}}
    @if ($featured)
        <x-ui.reveal class="mt-8">
            <a href="{{ lroute('news.show', ['slug' => $featured['slug']]) }}"
               class="grid overflow-hidden rounded-2xl bg-white shadow-card transition duration-300 hover:shadow-card-hover md:grid-cols-2">
                <x-ui.img :src="$featured['image']" :alt="$featured['title']" label="Foto berita (dummy)" :lazy="false"
                          class="aspect-[16/10] h-full w-full object-cover md:aspect-auto" />
                <div class="flex flex-col justify-center p-8 md:p-10">
                    <div class="flex items-center gap-3 text-xs">
                        <x-ui.badge tone="accent">{{ $featured['category'] }}</x-ui.badge>
                        <time datetime="{{ $featured['date'] }}" class="font-semibold text-slate-500">{{ $fmt($featured['date']) }}</time>
                    </div>
                    <h2 class="mt-4 text-2xl leading-snug md:text-3xl">{{ $featured['title'] }}</h2>
                    <p class="mt-3 text-base leading-relaxed">{{ $featured['excerpt'] }}</p>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold capitalize text-brand-700">
                        {{ $page['read_more'] }} <x-ui.icon name="arrow-right" class="h-4 w-4" />
                    </span>
                </div>
            </a>
        </x-ui.reveal>
    @endif

    {{-- Grid kartu seragam --}}
    <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($grid as $post)
            <x-ui.reveal :delay="($loop->index % 3) * 100" class="h-full">
                <a href="{{ lroute('news.show', ['slug' => $post['slug']]) }}"
                   class="block h-full overflow-hidden rounded-xl bg-white shadow-card transition duration-300 hover:-translate-y-0.5 hover:shadow-card-hover">
                    <x-ui.img :src="$post['image']" :alt="$post['title']" label="Foto berita (dummy)" class="aspect-[16/10] w-full object-cover" />
                    <div class="p-6">
                        <div class="flex items-center gap-3 text-xs">
                            <x-ui.badge tone="accent">{{ $post['category'] }}</x-ui.badge>
                            <time datetime="{{ $post['date'] }}" class="font-semibold text-slate-500">{{ $fmt($post['date']) }}</time>
                        </div>
                        <h3 class="mt-4 text-lg leading-snug">{{ $post['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed">{{ $post['excerpt'] }}</p>
                    </div>
                </a>
            </x-ui.reveal>
        @endforeach
    </div>

    <x-ui.pagination :current="$current" :last="$last" class="mt-12" />
@endif