@extends('layouts.app')

@php
    $ui = \App\Support\Content::ui();
    $page = \App\Support\Content::careersPage();
    $units = \App\Support\Content::units();
    $email = \App\Support\Content::company()['email']['career'];

    $whyIcons = ['briefcase', 'academic-cap', 'heart', 'users'];
@endphp

@section('title', $page['eyebrow'].' | '.config('site.name'))
@section('description', $page['lead'])

@section('content')
    <x-ui.page-header :eyebrow="$page['eyebrow']" :title="$page['title']" :text="$page['lead']"
        :crumbs="[[$ui['home'], lroute('home')], [$page['eyebrow'], null]]" />

    {{-- 1. MENGAPA BERGABUNG ---------------------------------------------- --}}
    <section class="section-y bg-white" aria-labelledby="why-title">
        <div class="container grid gap-12 lg:grid-cols-12 lg:gap-16">
            <x-ui.reveal class="lg:col-span-5">
                <div class="lg:sticky lg:top-28">
                    <p class="eyebrow">{{ $page['eyebrow'] }}</p>
                    <h2 id="why-title" class="mt-3 text-3xl leading-tight md:text-4xl">{{ $page['why_title'] }}</h2>
                    <p class="mt-5 text-base leading-relaxed md:text-lg">{{ $page['why_text'] }}</p>

                    <p class="mt-7 inline-flex items-center gap-2.5 rounded-full bg-brand-50 px-4 py-2 text-sm font-semibold text-brand-800 ring-1 ring-brand-100">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-accent-400 opacity-75"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-accent-500"></span>
                        </span>
                        {{ $page['open_note'] }}
                    </p>

                    <div class="mt-8">
                        <x-ui.button href="#kirim-cv" size="lg" icon="arrow-right">{{ $page['send_cv'] }}</x-ui.button>
                    </div>
                </div>
            </x-ui.reveal>

            <div class="grid gap-5 sm:grid-cols-2 lg:col-span-7">
                @foreach ($page['why'] as $item)
                    <x-ui.reveal :delay="($loop->index % 2) * 100 + intdiv($loop->index, 2) * 80" class="h-full">
                        <article class="group relative isolate flex h-full flex-col overflow-hidden rounded-3xl bg-slate-50 p-7 ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-1 hover:bg-white hover:shadow-card-hover {{ $loop->odd ? '' : 'sm:translate-y-6 sm:hover:translate-y-5' }}">
                            <span class="absolute -right-10 -top-10 -z-10 h-32 w-32 rounded-full bg-brand-100/60 transition duration-500 group-hover:scale-150"></span>
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-brand-700 shadow-card ring-1 ring-slate-200/70 transition duration-300 group-hover:bg-brand-700 group-hover:text-white">
                                <x-ui.icon :name="$whyIcons[$loop->index] ?? 'check'" class="h-6 w-6" />
                            </span>
                            <h3 class="mt-6 text-lg">{{ $item['title'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed md:text-base">{{ $item['text'] }}</p>
                        </article>
                    </x-ui.reveal>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 2. BIDANG ---------------------------------------------------------- --}}
    <section class="section-y bg-slate-50" aria-labelledby="fields-title">
        <div class="container">
            <x-ui.reveal>
                <x-ui.section-title id="fields-title" :title="$page['fields_title']" :text="$page['fields_text']" align="center" />
            </x-ui.reveal>

            <ul class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($units as $unit)
                    <li>
                        <x-ui.reveal :delay="($loop->index % 4) * 80" class="h-full">
                            <div class="group flex h-full items-center gap-4 rounded-2xl bg-white p-4 shadow-card ring-1 ring-slate-200/60 transition duration-300 hover:-translate-y-0.5 hover:shadow-card-hover">
                                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-slate-50 p-2 ring-1 ring-slate-200/70">
                                    <x-ui.img :src="$unit['logo']" :alt="$unit['name']" :label="null"
                                              class="max-h-full max-w-full object-contain" />
                                </span>
                                <span class="text-sm font-bold leading-snug text-ink">{{ $unit['name'] }}</span>
                            </div>
                        </x-ui.reveal>
                    </li>
                @endforeach

                {{-- Pelengkap grid: lamaran umum --}}
                <li>
                    <x-ui.reveal :delay="(count($units) % 4) * 80" class="h-full">
                        <a href="#kirim-cv"
                           class="group flex h-full items-center gap-4 rounded-2xl bg-gradient-to-br from-brand-700 to-brand-900 p-4 text-white shadow-card transition duration-300 hover:-translate-y-0.5 hover:shadow-card-hover">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-white/10 text-accent-400 ring-1 ring-white/20">
                                <x-ui.icon name="sparkles" class="h-6 w-6" />
                            </span>
                            <span class="flex-1 text-sm font-bold leading-snug">{{ $page['field_other'] }}</span>
                            <x-ui.icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1" />
                        </a>
                    </x-ui.reveal>
                </li>
            </ul>
        </div>
    </section>

    {{-- 3. PROSES SELEKSI -------------------------------------------------- --}}
    <section class="section-y bg-white" aria-labelledby="process-title">
        <div class="container">
            <x-ui.reveal>
                <x-ui.section-title id="process-title" :title="$page['process_title']" :text="$page['process_text']" align="center" />
            </x-ui.reveal>

            <ol class="mt-14 grid gap-10 md:grid-cols-4 md:gap-6">
                @foreach ($page['process'] as $step)
                    <li class="relative">
                        <x-ui.reveal :delay="$loop->index * 120" class="h-full">
                            <div class="relative pl-20 md:pl-0 md:text-center">
                                {{-- Garis penghubung: vertikal (mobile) / horizontal (desktop) --}}
                                @if (! $loop->last)
                                    <span class="absolute left-7 top-16 -bottom-10 w-px bg-gradient-to-b from-brand-300 to-brand-100 md:hidden" aria-hidden="true"></span>
                                    <span class="absolute left-[calc(50%+2.25rem)] right-[calc(-50%+0.75rem)] top-7 hidden h-px border-t-2 border-dashed border-brand-200 md:block" aria-hidden="true"></span>
                                @endif

                                <span class="absolute left-0 top-0 flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 text-lg font-extrabold text-white shadow-lg shadow-brand-700/25 ring-4 ring-white md:static md:mx-auto">
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <h3 class="text-lg md:mt-6">{{ $step['title'] }}</h3>
                                <p class="mt-2 text-sm leading-relaxed md:text-base">{{ $step['text'] }}</p>
                            </div>
                        </x-ui.reveal>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- 4. KIRIM CV: subjek email mengikuti bidang yang dipilih ------------ --}}
    <section id="kirim-cv" class="relative isolate overflow-hidden bg-brand-950 section-y text-white" aria-labelledby="cv-title"
             x-data="{ field: @js($page['field_other']), prefix: @js($page['subject_prefix']), email: @js($email), copied: false,
                       get subject() { return this.prefix + this.field },
                       get href() { return 'mailto:' + this.email + '?subject=' + encodeURIComponent(this.subject) } }">
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-brand-900 via-brand-950 to-brand-950"></div>
        <div class="absolute inset-0 -z-10 opacity-60 [background-image:radial-gradient(rgba(255,255,255,0.14)_1px,transparent_1px)] [background-size:26px_26px] [mask-image:radial-gradient(ellipse_at_15%_30%,#000,transparent_70%)]"></div>
        <div class="absolute -right-32 -top-32 -z-10 h-[30rem] w-[30rem] rounded-full bg-brand-600/30 blur-3xl"></div>
        <div class="absolute -bottom-40 left-1/4 -z-10 h-80 w-80 rounded-full bg-accent-500/10 blur-3xl"></div>

        <div class="container grid items-center gap-12 lg:grid-cols-12 lg:gap-16">
            <x-ui.reveal class="lg:col-span-5">
                <p class="eyebrow inline-flex items-center gap-3 text-accent-400">
                    <span class="h-0.5 w-8 rounded-full bg-accent-500"></span>{{ $page['eyebrow'] }}
                </p>
                <h2 id="cv-title" class="mt-4 text-3xl leading-tight text-white md:text-4xl">{{ $page['cv_title'] }}</h2>
                <p class="mt-5 text-base leading-relaxed text-brand-100 md:text-lg">{{ $page['cv_text'] }}</p>

                <div class="mt-8 flex items-center gap-4 rounded-2xl bg-white/[0.06] p-4 ring-1 ring-white/10 backdrop-blur">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/10 text-accent-400 ring-1 ring-white/15">
                        <x-ui.icon name="mail" class="h-6 w-6" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-widest text-brand-200">{{ $page['send_to'] }}</p>
                        <p class="truncate font-semibold text-white">{{ $email }}</p>
                    </div>
                </div>
            </x-ui.reveal>

            {{-- Kartu pengisian --}}
            <x-ui.reveal :delay="150" class="lg:col-span-7">
                <div class="rounded-[2rem] bg-white p-6 shadow-card-hover ring-1 ring-white/20 sm:p-9">
                    <fieldset>
                        <legend class="text-sm font-bold text-ink">{{ $page['field_label'] }}</legend>

                        <div class="mt-4 flex flex-wrap gap-2.5">
                            @foreach ($units as $unit)
                                <label class="cursor-pointer">
                                    <input type="radio" name="field" value="{{ $unit['name'] }}" x-model="field" class="peer sr-only">
                                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-brand-300 hover:text-brand-700 peer-checked:border-brand-700 peer-checked:bg-brand-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand-500 peer-focus-visible:ring-offset-2">{{ $unit['name'] }}</span>
                                </label>
                            @endforeach
                            <label class="cursor-pointer">
                                <input type="radio" name="field" value="{{ $page['field_other'] }}" x-model="field" class="peer sr-only" checked>
                                <span class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-brand-300 hover:text-brand-700 peer-checked:border-brand-700 peer-checked:bg-brand-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand-500 peer-focus-visible:ring-offset-2">{{ $page['field_other'] }}</span>
                            </label>
                        </div>
                    </fieldset>

                    {{-- Pratinjau subjek email --}}
                    <div class="mt-7 rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/70">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ $page['subject_label'] }}</p>
                        <p class="mt-1.5 break-words font-semibold text-ink" x-text="subject">{{ $page['subject_prefix'].$page['field_other'] }}</p>
                    </div>

                    <div class="mt-7 flex flex-wrap gap-3">
                        <a :href="href" href="mailto:{{ $email }}"
                           class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-700 px-6 py-3.5 text-base font-semibold capitalize text-white shadow-lg shadow-brand-700/25 transition hover:-translate-y-0.5 hover:bg-brand-800">
                            <x-ui.icon name="paper-airplane" class="h-5 w-5" /> {{ $page['send_cv'] }}
                        </a>
                        <button type="button"
                                @click="navigator.clipboard.writeText(email).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-6 py-3.5 text-base font-semibold text-ink transition hover:border-brand-500 hover:bg-brand-50 hover:text-brand-800">
                            <x-ui.icon name="copy" class="h-5 w-5" x-show="!copied" />
                            <x-ui.icon name="check" class="h-5 w-5 text-emerald-600" x-show="copied" x-cloak />
                            <span x-text="copied ? @js($page['copied']) : @js($page['copy_email'])">{{ $page['copy_email'] }}</span>
                        </button>
                    </div>
                </div>
            </x-ui.reveal>
        </div>
    </section>
@endsection