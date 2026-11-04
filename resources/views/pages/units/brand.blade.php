@extends('layouts.app')

@php
    $ui = \App\Support\Content::ui();
    $page = \App\Support\Content::unitsPage();
    $others = array_values(array_filter($unit['brands'], fn ($b) => $b['slug'] !== $brand['slug']));
    $lead = $brand['description'] ? \Illuminate\Support\Str::limit($brand['description'], 160) : $unit['name'];
@endphp

@section('title', (($brand['seo_title'] ?? '') !== '' ? $brand['seo_title'] : $brand['name'].' | '.$unit['name']).' | '.config('site.name'))
@section('description', ($brand['seo_description'] ?? '') !== '' ? $brand['seo_description'] : $lead)

@section('content')
    <x-ui.page-header :eyebrow="$unit['name']" :title="$brand['name']" :text="$lead"
        :crumbs="[
            [$ui['home'], lroute('home')],
            [$page['eyebrow'], lroute('units.index')],
            [$unit['name'], lroute('units.show', ['slug' => $unit['slug']])],
            [$brand['name'], null],
        ]" />

    {{-- Profil satu perusahaan/brand. Bagian yang belum ada isinya (deskripsi, sosmed, tautan) tidak ditampilkan. --}}
    <section class="section-y bg-white" aria-labelledby="brand-title">
        <div class="container">
            <article class="grid items-center gap-8 rounded-3xl bg-slate-50 p-6 ring-1 ring-slate-200/70 sm:p-8 lg:grid-cols-12 lg:gap-12 lg:p-12">
                <div class="lg:col-span-5">
                    <x-ui.reveal>
                        <div class="flex aspect-[4/3] items-center justify-center rounded-2xl bg-white p-8 shadow-card ring-1 ring-slate-200/70">
                            <x-ui.img :src="$brand['logo']" :alt="$brand['name']" :label="$brand['name']" :lazy="false" class="h-full w-full object-contain" />
                        </div>
                    </x-ui.reveal>
                </div>

                <div class="lg:col-span-7">
                    <x-ui.reveal :delay="100">
                        <p class="eyebrow inline-flex items-center gap-3">
                            <span class="h-0.5 w-8 rounded-full bg-accent-500"></span>
                            {{ $page['brand_pillar'] }} {{ $unit['name'] }}
                        </p>
                        <h2 id="brand-title" class="mt-4 text-3xl leading-tight md:text-4xl">{{ $page['brand_about'] }} {{ $brand['name'] }}</h2>

                        @if ($brand['description'])
                            <p class="mt-5 text-base leading-relaxed md:text-lg">{{ $brand['description'] }}</p>
                        @else
                            <p class="mt-5 text-base leading-relaxed md:text-lg">{{ $page['brand_empty'] }}</p>
                        @endif

                        {{-- Daftar fokus/layanan (opsional, mis. Training & Research Centre) --}}
                        @if (! empty($brand['services']['items']))
                            <h3 class="mt-7 text-lg font-bold text-ink">{{ $brand['services']['title'] }}</h3>
                            <ul class="mt-4 space-y-3">
                                @foreach ($brand['services']['items'] as $svc)
                                    <li class="flex items-start gap-3">
                                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-700 ring-1 ring-brand-100">
                                            <x-ui.icon name="check" class="h-3.5 w-3.5" />
                                        </span>
                                        <p class="text-base leading-relaxed"><span class="font-semibold text-ink">{{ $svc['title'] }}:</span> {{ $svc['text'] }}</p>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        {{-- Info kanal resmi: TikTok, Instagram, Website. Yang belum diisi tampil "-". --}}
                        @php
                            $short = fn (?string $u) => $u ? rtrim(preg_replace('#^https?://(www\.)?#i', '', $u), '/') : null;
                            $handle = fn (?string $u, string $base) => $u ? (preg_match('#^https?://#i', $u) ? $u : $base.ltrim($u, '@/')) : null;
                            $channels = [
                                ['label' => 'TikTok', 'icon' => 'tiktok', 'social' => true, 'href' => $handle($brand['tiktok'], 'https://www.tiktok.com/@')],
                                ['label' => 'Instagram', 'icon' => 'instagram', 'social' => true, 'href' => $handle($brand['instagram'], 'https://www.instagram.com/')],
                                ['label' => 'Website', 'icon' => 'globe', 'social' => false, 'href' => $brand['url']],
                            ];
                        @endphp
                        <dl class="mt-8 divide-y divide-slate-200/80 overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200/70">
                            @foreach ($channels as $ch)
                                <div class="flex items-center gap-4 px-5 py-4">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 ring-1 ring-brand-100" aria-hidden="true">
                                        @if ($ch['social'])
                                            <x-ui.social-icon :name="$ch['icon']" class="h-5 w-5" />
                                        @else
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9S14.5 18.4 12 21c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3z"/></svg>
                                        @endif
                                    </span>
                                    <dt class="w-24 shrink-0 text-sm font-semibold text-ink sm:w-28">{{ $ch['label'] }}</dt>
                                    <dd class="min-w-0 flex-1 truncate text-sm">
                                        @if ($ch['href'])
                                            <a href="{{ $ch['href'] }}" target="_blank" rel="noopener"
                                               class="group inline-flex max-w-full items-center gap-2 font-semibold text-brand-700 transition hover:text-brand-900">
                                                <span class="truncate">{{ $short($ch['href']) }}</span>
                                                <x-ui.icon name="arrow-up-right" class="h-4 w-4 shrink-0 transition duration-300 group-hover:-translate-y-0.5 group-hover:translate-x-0.5" />
                                            </a>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>

                        <div class="mt-8">
                            <x-ui.button :href="lroute('units.index')" variant="outline" icon="arrow-right">{{ $page['back_to_pillars'] }}</x-ui.button>
                        </div>
                    </x-ui.reveal>
                </div>
            </article>
        </div>
    </section>

    {{-- Brand lain di pilar yang sama --}}
    @if ($others)
        <section class="section-y bg-slate-50" aria-labelledby="others-title">
            <div class="container">
                <x-ui.reveal>
                    <p class="eyebrow inline-flex items-center gap-3">
                        <span class="h-0.5 w-8 rounded-full bg-accent-500"></span>
                        {{ $unit['name'] }}
                    </p>
                    <h2 id="others-title" class="mt-4 text-3xl leading-tight md:text-4xl">{{ $page['brand_others_title'] }}</h2>
                </x-ui.reveal>

                <ul class="mt-10 grid grid-cols-2 gap-4 lg:grid-cols-4">
                    @foreach ($others as $other)
                        <li>
                            <x-ui.reveal :delay="($loop->index % 4) * 60" class="h-full">
                                <a href="{{ lroute('units.brand', ['slug' => $unit['slug'], 'brand' => $other['slug']]) }}"
                                   class="group flex h-full flex-col items-center gap-4 rounded-2xl bg-white p-5 text-center shadow-card ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-1 hover:shadow-card-hover hover:ring-brand-200">
                                    <x-ui.img :src="$other['logo']" :alt="$other['name']" :label="$other['name']" class="h-20 w-full rounded-lg object-contain" />
                                    <span class="text-sm font-semibold leading-snug text-ink transition group-hover:text-brand-700">{{ $other['name'] }}</span>
                                </a>
                            </x-ui.reveal>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <x-ui.cta-band />
@endsection