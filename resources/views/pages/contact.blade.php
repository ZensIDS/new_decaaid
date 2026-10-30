@extends('layouts.app')

@php
    $ui = \App\Support\Content::ui();
    $page = \App\Support\Content::contactPage();
    $company = \App\Support\Content::company();
    $selected = array_key_exists(request()->query('subject'), $page['subjects']) ? request()->query('subject') : 'general';

    $chip = 'inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-brand-300 hover:text-brand-700 peer-checked:border-brand-700 peer-checked:bg-brand-700 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-brand-500 peer-focus-visible:ring-offset-2';
    $row = 'group flex items-center gap-4 rounded-2xl bg-white/[0.07] p-4 ring-1 ring-white/10 transition duration-300 hover:bg-white/[0.14] hover:ring-white/25';
    $iconBox = 'flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/10 text-accent-400 ring-1 ring-white/15 transition duration-300 group-hover:bg-accent-500 group-hover:text-white';
    $rowLabel = 'block text-xs font-semibold uppercase tracking-widest text-brand-200';
@endphp

@section('title', $page['eyebrow'].' | '.config('site.name'))
@section('description', $page['lead'])

@section('content')
    <x-ui.page-header :eyebrow="$page['eyebrow']" :title="$page['title']" :text="$page['lead']"
        :crumbs="[[$ui['home'], lroute('home')], [$page['eyebrow'], null]]" />

    {{-- 1. KARTU KONTAK + FORM (menumpuk ke atas hero) ---------------------- --}}
    <section class="relative z-10 flow-root bg-slate-50 pb-16 md:pb-24" aria-label="{{ $page['form_title'] }}">
        <div class="container -mt-10 md:-mt-16 lg:-mt-20">
            <div class="grid overflow-hidden rounded-[2rem] bg-white shadow-card-hover ring-1 ring-slate-200/70 lg:grid-cols-12">

                {{-- Panel kiri: kanal resmi --}}
                <aside class="relative isolate overflow-hidden bg-gradient-to-br from-brand-700 via-brand-800 to-brand-950 p-8 text-white md:p-10 lg:col-span-5">
                    <div class="absolute -right-20 -top-20 -z-10 h-72 w-72 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="absolute -bottom-28 -left-16 -z-10 h-72 w-72 rounded-full bg-accent-500/25 blur-3xl"></div>
                    <div class="absolute inset-0 -z-10 opacity-50 [background-image:radial-gradient(rgba(255,255,255,0.16)_1px,transparent_1px)] [background-size:24px_24px] [mask-image:linear-gradient(to_bottom_right,#000,transparent_70%)]"></div>
                    <div class="absolute -bottom-10 -right-10 -z-10 h-44 w-44 rounded-full border border-white/10"></div>

                    <h2 class="text-2xl text-white md:text-3xl">{{ $page['channels_title'] }}</h2>
                    <p class="mt-3 text-base leading-relaxed text-brand-100">{{ $page['channels_text'] }}</p>

                    <ul class="mt-8 space-y-3.5">
                        <li>
                            <a href="mailto:{{ $company['email']['general'] }}" class="{{ $row }}">
                                <span class="{{ $iconBox }}"><x-ui.icon name="mail" class="h-5 w-5" /></span>
                                <span class="min-w-0">
                                    <span class="{{ $rowLabel }}">{{ $page['email'] }}</span>
                                    <span class="block truncate font-semibold text-white">{{ $company['email']['general'] }}</span>
                                </span>
                            </a>
                        </li>
                        {{-- DUMMY: nomor telepon --}}
                        <li>
                            <a href="tel:{{ preg_replace('/[^+\d]/', '', $company['phone']) }}" class="{{ $row }}">
                                <span class="{{ $iconBox }}"><x-ui.icon name="phone" class="h-5 w-5" /></span>
                                <span class="min-w-0">
                                    <span class="{{ $rowLabel }}">{{ $ui['home'] === 'Home' ? 'Phone' : 'Telepon' }}</span>
                                    <span class="block truncate font-semibold text-white">{{ $company['phone'] }}</span>
                                </span>
                            </a>
                        </li>
                        {{-- DUMMY: nomor WhatsApp --}}
                        <li>
                            <a href="{{ $company['whatsapp']['url'] }}" target="_blank" rel="noopener noreferrer" class="{{ $row }}">
                                <span class="{{ $iconBox }}"><x-ui.social-icon name="whatsapp" class="h-5 w-5" /></span>
                                <span class="min-w-0 flex-1">
                                    <span class="{{ $rowLabel }}">{{ $page['whatsapp'] }}</span>
                                    <span class="block truncate font-semibold text-white">{{ $company['whatsapp']['label'] }}</span>
                                </span>
                                <x-ui.icon name="arrow-up-right" class="h-4 w-4 shrink-0 text-brand-200 transition group-hover:text-white" />
                            </a>
                        </li>
                    </ul>

                    {{-- Sosmed (DUMMY: URL diisi setelah desain di-ACC) --}}
                    <div class="mt-10 border-t border-white/10 pt-7">
                        <p class="{{ $rowLabel }}">{{ t('footer.follow') }}</p>
                        <ul class="mt-4 flex flex-wrap gap-3">
                            @foreach (['instagram' => 'Instagram', 'facebook' => 'Facebook', 'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'linkedin' => 'LinkedIn'] as $net => $netName)
                                @php $url = $company['social'][$net] ?? '#'; @endphp
                                <li>
                                    <a href="{{ $url }}" aria-label="{{ $netName }}" title="{{ $netName }}"
                                       class="flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-brand-100 ring-1 ring-white/15 transition duration-300 hover:-translate-y-0.5 hover:bg-white hover:text-brand-900"
                                       @if ($url !== '#') target="_blank" rel="noopener noreferrer" @endif>
                                        <x-ui.social-icon :name="$net" class="h-[1.15rem] w-[1.15rem]" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </aside>

                {{-- Panel kanan: form --}}
                <div class="p-6 sm:p-8 md:p-12 lg:col-span-7"
                     x-data="contactForm({
                        subject: @js($selected),
                        subjects: @js($page['subjects']),
                        required: @js($page['err_required']),
                        invalid: @js($page['err_email']),
                        to: @js($company['email']['general']),
                     })">

                    {{-- Keadaan: mengisi --}}
                    <div x-show="!sent">
                        <h2 class="text-2xl md:text-3xl">{{ $page['form_title'] }}</h2>
                        <p class="mt-2 text-sm leading-relaxed md:text-base">{{ $page['form_text'] }}</p>

                        <form class="mt-8 grid gap-5 md:grid-cols-2" x-ref="form" @submit.prevent="submit()" novalidate>
                            @csrf
                            {{-- Honeypot anti-spam (aktifkan validasinya di B4) --}}
                            <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

                            <x-ui.float-field name="name" :label="$page['name']" icon="user" required autocomplete="name" />
                            <x-ui.float-field name="email" type="email" :label="$page['email']" icon="mail" required autocomplete="email" />

                            <div class="md:col-span-2">
                                <x-ui.float-field name="phone" type="tel" :label="$page['phone']" icon="phone" autocomplete="tel" />
                            </div>

                            <fieldset class="md:col-span-2">
                                <legend class="text-sm font-bold text-ink">{{ $page['subject'] }}
                                    <span class="font-medium text-slate-500">&middot; {{ $page['subject_hint'] }}</span>
                                </legend>
                                <div class="mt-3 flex flex-wrap gap-2.5">
                                    @foreach ($page['subjects'] as $key => $label)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="subject" value="{{ $key }}" x-model="values.subject" class="peer sr-only">
                                            <span class="{{ $chip }}">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>

                            <div class="md:col-span-2">
                                <x-ui.float-field name="message" type="textarea" :label="$page['message']" icon="chat" required maxlength="1000" />
                                <p class="mt-1.5 pr-1 text-right text-xs tabular-nums text-slate-400">
                                    <span x-text="values.message.length">0</span> / 1000
                                </p>
                            </div>

                            <div class="flex flex-col gap-4 md:col-span-2 md:flex-row md:items-center md:justify-between">
                                <p class="text-xs leading-relaxed text-slate-500 md:max-w-xs">
                                    {{ $page['consent'] }}
                                    <a href="{{ lroute('privacy') }}" class="font-semibold text-brand-700 underline-offset-4 hover:underline">{{ t('footer.privacy') }}</a>.
                                </p>

                                <button type="submit" :disabled="sending"
                                        class="group inline-flex items-center justify-center gap-2.5 rounded-2xl bg-gradient-to-r from-brand-700 to-brand-800 px-8 py-4 text-base font-semibold capitalize text-white shadow-lg shadow-brand-700/30 transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-brand-700/30 disabled:cursor-wait disabled:opacity-80 disabled:hover:translate-y-0">
                                    <svg x-show="sending" x-cloak class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"></circle>
                                        <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                                    </svg>
                                    <span x-text="sending ? @js($page['sending']) : @js($page['send'])">{{ $page['send'] }}</span>
                                    <x-ui.icon name="paper-airplane" class="h-5 w-5 transition duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" x-show="!sending" />
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- Keadaan: selesai (DUMMY: pengiriman server diaktifkan di Tahap B4) --}}
                    <div x-show="sent" x-cloak x-transition.opacity.duration.400ms
                         class="flex min-h-[26rem] flex-col items-center justify-center text-center" role="status">
                        <span class="relative flex h-20 w-20 items-center justify-center rounded-full bg-brand-50 text-brand-700 ring-8 ring-brand-50/60">
                            <x-ui.icon name="check-circle" class="h-10 w-10" />
                        </span>
                        <h2 class="mt-7 text-2xl md:text-3xl">{{ $page['success_title'] }}</h2>
                        <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed md:text-base">{{ $page['preview_note'] }}</p>

                        <div class="mt-8 flex flex-wrap justify-center gap-3">
                            <a :href="mailto"
                               class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-700 px-6 py-3.5 text-base font-semibold capitalize text-white shadow-lg shadow-brand-700/25 transition hover:-translate-y-0.5 hover:bg-brand-800">
                                <x-ui.icon name="mail" class="h-5 w-5" /> {{ $page['via_email'] }}
                            </a>
                            <button type="button" @click="again()"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 px-6 py-3.5 text-base font-semibold text-ink transition hover:border-brand-500 hover:bg-brand-50 hover:text-brand-800">
                                {{ $page['write_again'] }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. KANTOR ----------------------------------------------------------- --}}
    <section class="section-y bg-white" aria-labelledby="offices-title">
        <div class="container">
            <x-ui.reveal>
                <x-ui.section-title id="offices-title" :title="$page['offices_title']" :text="$page['offices_text']" align="center" />
            </x-ui.reveal>

            <div class="mt-12 grid gap-6 md:grid-cols-3">
                @foreach ($company['offices'] as $office)
                    @php $hq = $loop->first; @endphp
                    <x-ui.reveal :delay="$loop->index * 100" class="h-full">
                        <article class="group relative isolate flex h-full flex-col overflow-hidden rounded-3xl p-8 transition duration-300 hover:-translate-y-1 {{ $hq ? 'bg-gradient-to-br from-brand-700 to-brand-950 text-white shadow-card-hover' : 'bg-slate-50 ring-1 ring-slate-200/70 hover:bg-white hover:shadow-card-hover' }}">
                            <span class="absolute -right-4 -top-8 -z-10 select-none text-[9rem] font-extrabold leading-none {{ $hq ? 'text-white/[0.07]' : 'text-slate-200/70 transition duration-500 group-hover:text-brand-100' }}"
                                  aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            @if ($hq)
                                <span class="absolute -bottom-20 -left-10 -z-10 h-56 w-56 rounded-full bg-accent-500/20 blur-3xl"></span>
                            @endif

                            <span class="inline-flex w-fit items-center rounded-full px-3 py-1 text-xs font-semibold {{ $hq ? 'bg-white/15 text-white ring-1 ring-white/20' : 'bg-brand-50 text-brand-700' }}">{{ $office['type'] }}</span>
                            <h3 class="mt-5 text-2xl {{ $hq ? 'text-white' : '' }}">{{ $office['city'] }}</h3>

                            <p class="mt-3 flex flex-1 gap-2.5 text-base leading-relaxed {{ $hq ? 'text-brand-100' : '' }}">
                                <x-ui.icon name="map-pin" class="mt-1 h-4 w-4 shrink-0 {{ $hq ? 'text-accent-400' : 'text-brand-700' }}" />
                                <span>{{ $office['address'] }}</span>
                            </p>

                            <div class="mt-7">
                                <x-ui.button :href="'https://www.google.com/maps/search/?api=1&query='.rawurlencode($office['address'])"
                                             :variant="$hq ? 'light' : 'outline'" size="sm" target="_blank" rel="noopener noreferrer" icon="arrow-up-right">
                                    {{ $page['open_map'] }}
                                </x-ui.button>
                            </div>
                        </article>
                    </x-ui.reveal>
                @endforeach
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('contactForm', (cfg) => ({
                values: { name: '', email: '', phone: '', subject: cfg.subject, message: '' },
                touched: {},
                sending: false,
                sent: false,

                get errors() {
                    const e = {};
                    if (! this.values.name.trim()) e.name = cfg.required;
                    if (! this.values.email.trim()) e.email = cfg.required;
                    else if (! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.values.email.trim())) e.email = cfg.invalid;
                    if (! this.values.message.trim()) e.message = cfg.required;
                    return e;
                },

                show(k) { return !! (this.touched[k] && this.errors[k]); },
                touch(k) { this.touched[k] = true; },

                submit() {
                    this.touched = { name: true, email: true, message: true };
                    const first = Object.keys(this.errors)[0];
                    if (first) {
                        this.$refs.form.elements[first]?.focus();
                        return;
                    }
                    this.sending = true;
                    // DUMMY (Tahap B4): ganti dengan POST ke server (fetch + CSRF), lalu set sent = true bila sukses.
                    setTimeout(() => { this.sending = false; this.sent = true; }, 700);
                },

                // Cadangan sementara: buka aplikasi email dengan isi pesan terisi otomatis.
                get mailto() {
                    const v = this.values;
                    const body = [v.message.trim(), '', v.name.trim(), v.email.trim(), v.phone.trim()].join('\n').trim();
                    return 'mailto:' + cfg.to + '?subject=' + encodeURIComponent(cfg.subjects[v.subject] || '') + '&body=' + encodeURIComponent(body);
                },

                again() {
                    this.values = { name: '', email: '', phone: '', subject: cfg.subject, message: '' };
                    this.touched = {};
                    this.sent = false;
                },
            }));
        });
    </script>
@endpush