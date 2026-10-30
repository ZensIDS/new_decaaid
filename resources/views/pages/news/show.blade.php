@extends('layouts.app')

@php
    $post = $post ?? null;
    abort_if(! $post, 404);

    $ui = \App\Support\Content::ui();
    $page = \App\Support\Content::newsPage();
    $fmt = fn ($d) => \Carbon\Carbon::parse($d)->translatedFormat('d F Y');

    $sorted = collect(\App\Support\Content::news())->sortByDesc('date')->values();
    $related = $sorted->where('slug', '!=', $post['slug'])
        ->sortByDesc(fn ($p) => $p['category_slug'] === $post['category_slug'])->take(3)->values();

    // Berita lebih baru / lebih lama (urut tanggal)
    $i = (int) $sorted->search(fn ($p) => $p['slug'] === $post['slug']);
    $newer = $i > 0 ? $sorted->get($i - 1) : null;
    $older = $sorted->get($i + 1);

    // Perkiraan waktu baca (±200 kata per menit)
    $words = str_word_count(strip_tags($post['excerpt'].' '.collect($post['body'])->map(fn ($b) => is_array($b) ? $b['h'] : $b)->implode(' ')));
    $minutes = max(1, (int) ceil($words / 200));

    $url = url()->current();
    $text = $post['title'];
    $shares = [
        ['WhatsApp', 'whatsapp', 'https://wa.me/?text='.rawurlencode($text.' '.$url)],
        ['Facebook', 'facebook', 'https://www.facebook.com/sharer/sharer.php?u='.rawurlencode($url)],
        ['X', 'x', 'https://twitter.com/intent/tweet?url='.rawurlencode($url).'&text='.rawurlencode($text)],
        ['LinkedIn', 'linkedin', 'https://www.linkedin.com/sharing/share-offsite/?url='.rawurlencode($url)],
    ];
    $categoryUrl = lroute('news.category', ['slug' => $post['category_slug']]);
@endphp

@section('title', $post['title'].' | '.$page['eyebrow'].' | '.config('site.name'))
@section('description', $post['excerpt'])

@section('content')
<div x-data="{
        p: 0,
        calc() {
            const el = this.$refs.body;
            if (! el) return;
            const r = el.getBoundingClientRect();
            const total = r.height - window.innerHeight * 0.4;
            this.p = Math.min(100, Math.max(0, ((window.innerHeight * 0.3 - r.top) / total) * 100));
        },
     }"
     x-init="calc()" @scroll.window.passive="calc()" @resize.window.passive="calc()">

    {{-- Progres baca --}}
    <div class="pointer-events-none fixed inset-x-0 top-0 z-[60] h-1" aria-hidden="true">
        <div class="h-full bg-gradient-to-r from-brand-500 to-accent-500" :style="`width: ${p}%`"></div>
    </div>

    {{-- Hero artikel -------------------------------------------------------- --}}
    <section class="relative isolate overflow-hidden bg-brand-950 pb-28 pt-12 text-white md:pb-40 md:pt-16" aria-labelledby="post-title">
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-brand-900 via-brand-950 to-brand-950"></div>
        <div class="absolute inset-0 -z-10 opacity-60 [background-image:radial-gradient(rgba(255,255,255,0.15)_1px,transparent_1px)] [background-size:26px_26px] [mask-image:radial-gradient(ellipse_at_20%_30%,#000,transparent_70%)]"></div>
        <div class="absolute -right-28 -top-32 -z-10 h-96 w-96 rounded-full bg-brand-600/35 blur-3xl"></div>
        <div class="absolute -bottom-40 left-1/3 -z-10 h-72 w-72 rounded-full bg-accent-500/10 blur-3xl"></div>
        <div class="absolute right-[12%] top-12 -z-10 hidden h-44 w-44 rounded-full border border-white/10 lg:block"></div>

        <div class="container">
            <x-ui.breadcrumb :light="true" class="animate-fade-up"
                :items="[[$ui['home'], lroute('home')], [$page['eyebrow'], lroute('news.index')], [$post['category'], $categoryUrl]]" />

            <div class="mx-auto mt-10 max-w-4xl text-center">
                <a href="{{ $categoryUrl }}" class="animate-fade-up inline-flex items-center gap-2.5 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-brand-100 backdrop-blur transition hover:bg-white/10"
                   style="animation-delay: 80ms">
                    <span class="h-1.5 w-1.5 rounded-full bg-accent-400"></span>
                    {{ $post['category'] }}
                </a>

                <h1 id="post-title" class="animate-fade-up mt-6 text-3xl font-extrabold leading-[1.15] text-white sm:text-4xl md:text-5xl"
                    style="animation-delay: 160ms">{{ $post['title'] }}</h1>

                <div class="animate-fade-up mt-7 flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-sm font-medium text-brand-100"
                     style="animation-delay: 240ms">
                    <time datetime="{{ $post['date'] }}">{{ $fmt($post['date']) }}</time>
                    <span class="h-1 w-1 rounded-full bg-white/40" aria-hidden="true"></span>
                    <span>{{ $minutes }} {{ t('news.min_read') }}</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Foto utama (menumpuk di tepi bawah hero). DUMMY: ganti dengan foto berita asli --}}
    <div class="container relative z-10 -mt-20 md:-mt-32">
        <div class="animate-fade-up mx-auto max-w-5xl" style="animation-delay: 300ms">
            <x-ui.img :src="$post['image']" :alt="$post['title']" label="Foto berita (dummy)" :lazy="false"
                      class="aspect-[16/9] w-full rounded-3xl object-cover shadow-card-hover ring-1 ring-slate-200/60 md:rounded-[2rem]" />
        </div>
    </div>

    {{-- Isi artikel ----------------------------------------------------------- --}}
    <article class="bg-white pb-16 pt-12 md:pb-24 md:pt-16">
        <div class="container">
            <div class="mx-auto grid max-w-5xl gap-10 lg:grid-cols-[3.5rem_minmax(0,1fr)] lg:gap-14">

                {{-- Rel bagikan (desktop) --}}
                <aside class="hidden lg:block" aria-label="{{ $page['share'] }}">
                    <div class="sticky top-28 flex flex-col items-center gap-3" x-data="{ copied: false, link: @js($url) }">
                        <span class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-400">{{ $page['share'] }}</span>
                        @foreach ($shares as [$name, $short, $href])
                            <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $name }}" title="{{ $name }}"
                               class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-50 text-slate-600 ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-0.5 hover:bg-brand-700 hover:text-white hover:ring-brand-700"><x-ui.social-icon :name="$short" class="h-[1.1rem] w-[1.1rem]" /></a>
                        @endforeach
                        <button type="button" :title="copied ? @js($page['copied']) : @js($page['copy_link'])"
                                :aria-label="copied ? @js($page['copied']) : @js($page['copy_link'])"
                                @click="navigator.clipboard.writeText(link).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                                :class="copied ? 'bg-accent-500 text-white ring-accent-500' : 'bg-slate-50 text-slate-600 ring-slate-200/70 hover:bg-brand-700 hover:text-white hover:ring-brand-700'"
                                class="flex h-11 w-11 items-center justify-center rounded-full ring-1 transition duration-300 hover:-translate-y-0.5">
                            <svg x-show="! copied" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M10 13a5 5 0 0 0 7.07 0l3-3a5 5 0 0 0-7.07-7.07l-1.5 1.5"/>
                                <path d="M14 11a5 5 0 0 0-7.07 0l-3 3a5 5 0 0 0 7.07 7.07l1.5-1.5"/>
                            </svg>
                            <x-ui.icon name="check" class="h-4 w-4" x-show="copied" x-cloak />
                        </button>
                    </div>
                </aside>

                <div x-ref="body" class="min-w-0">
                    {{-- Tipografi baca: lebar terbatas, baris longgar --}}
                    <div class="max-w-3xl space-y-6 text-lg leading-8 text-slate-700">
                        <p class="border-l-4 border-accent-500 pl-5 text-xl font-medium leading-9 text-ink md:text-2xl md:leading-10">{{ $post['excerpt'] }}</p>

                        @foreach ($post['body'] as $block)
                            @if (is_array($block))
                                <h2 class="flex items-center gap-3 pt-6 text-2xl text-ink md:text-3xl">
                                    <span class="h-7 w-1 shrink-0 rounded-full bg-gradient-to-b from-brand-600 to-accent-500"></span>
                                    {{ $block['h'] }}
                                </h2>
                            @else
                                <p>{{ $block }}</p>
                            @endif
                        @endforeach
                    </div>

                    {{-- Bagikan (semua layar) --}}
                    <div class="mt-12 max-w-3xl rounded-2xl bg-slate-50 p-6 ring-1 ring-slate-200/70 sm:p-7"
                         x-data="{ copied: false, link: @js($url) }">
                        <p class="text-sm font-semibold text-ink">{{ $page['share'] }}</p>
                        <div class="mt-4 flex flex-wrap gap-2.5">
                            @foreach ($shares as [$name, $short, $href])
                                <a href="{{ $href }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-brand-800 shadow-sm ring-1 ring-slate-200/70 transition hover:bg-brand-700 hover:text-white hover:ring-brand-700">
                                    <x-ui.social-icon :name="$short" class="h-4 w-4" />
                                    {{ $name }}
                                </a>
                            @endforeach
                            <button type="button"
                                    @click="navigator.clipboard.writeText(link).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                                    :class="copied ? 'bg-accent-500 text-white' : 'bg-brand-700 text-white hover:bg-brand-800'"
                                    class="rounded-full px-4 py-2 text-sm font-semibold capitalize transition">
                                <span x-text="copied ? @js($page['copied']) : @js($page['copy_link'])">{{ $page['copy_link'] }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- Sebelumnya / berikutnya --}}
                    @if ($newer || $older)
                        <nav class="mt-8 grid max-w-3xl gap-4 sm:grid-cols-2" aria-label="{{ $page['eyebrow'] }}">
                            @foreach ([['older', $older, 'chevron-left'], ['newer', $newer, 'chevron-right']] as [$dir, $item, $icon])
                                @if ($item)
                                    <a href="{{ lroute('news.show', ['slug' => $item['slug']]) }}"
                                       class="group flex flex-col rounded-2xl bg-white p-5 shadow-card ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-1 hover:shadow-card-hover {{ $dir === 'newer' ? 'sm:col-start-2 sm:text-right' : '' }}">
                                        <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-accent-500 {{ $dir === 'newer' ? 'sm:flex-row-reverse sm:self-end' : '' }}">
                                            <x-ui.icon :name="$icon" class="h-4 w-4" />
                                            {{ t('news.'.$dir) }}
                                        </span>
                                        <span class="mt-2 text-base font-bold leading-snug text-ink transition group-hover:text-brand-700">{{ $item['title'] }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </nav>
                    @endif

                    <div class="mt-8">
                        <x-ui.button :href="lroute('news.index')" variant="outline" icon="arrow-right">{{ $page['back'] }}</x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    </article>

    {{-- Berita terkait --------------------------------------------------------- --}}
    <section class="section-y bg-slate-50" aria-labelledby="related-title">
        <div class="container">
            <x-ui.reveal>
                <p class="eyebrow inline-flex items-center gap-3">
                    <span class="h-0.5 w-8 rounded-full bg-accent-500"></span>
                    {{ t('news.keep_reading') }}
                </p>
                <h2 id="related-title" class="mt-4 text-3xl leading-tight md:text-4xl">{{ $page['related'] }}</h2>
            </x-ui.reveal>

            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ($related as $item)
                    <x-ui.reveal :delay="$loop->index * 100" class="h-full">
                        <a href="{{ lroute('news.show', ['slug' => $item['slug']]) }}"
                           class="group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-card ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-1 hover:shadow-card-hover">
                            <div class="relative overflow-hidden">
                                <x-ui.img :src="$item['image']" :alt="$item['title']" label="Foto berita (dummy)"
                                          class="aspect-[16/10] w-full object-cover transition duration-700 group-hover:scale-105" />
                                <span class="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-brand-800 shadow-sm backdrop-blur">{{ $item['category'] }}</span>
                            </div>
                            <div class="flex flex-1 flex-col p-6">
                                <time datetime="{{ $item['date'] }}" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $fmt($item['date']) }}</time>
                                <h3 class="mt-2 flex-1 text-lg leading-snug transition group-hover:text-brand-700">{{ $item['title'] }}</h3>
                                <span class="mt-5 inline-flex items-center gap-2 text-sm font-semibold capitalize text-brand-700">
                                    {{ $page['read_more'] }}
                                    <x-ui.icon name="arrow-right" class="h-4 w-4 transition duration-300 group-hover:translate-x-1" />
                                </span>
                            </div>
                        </a>
                    </x-ui.reveal>
                @endforeach
            </div>
        </div>
    </section>

    <x-ui.cta-band />
</div>
@endsection