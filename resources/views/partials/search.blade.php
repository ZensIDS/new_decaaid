{{-- Overlay pencarian (tampilan). Fungsi pencarian penuh di Tahap B4. Memakai state `search` dari header. --}}
@php $units = \App\Support\Content::units(); @endphp

<div x-cloak x-show="search" x-transition.opacity.duration.200ms x-focustrap
     x-effect="if (search) $nextTick(() => $refs.q && $refs.q.focus())"
     class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="{{ t('nav.search_title') }}">
    <div class="absolute inset-0 bg-brand-950/70" @click="search = false"></div>

    <div class="relative mx-auto mt-[10vh] w-full max-w-2xl px-4">
        <form method="GET" action="{{ lroute('search') }}" role="search" class="rounded-2xl bg-white p-2.5 shadow-card-hover">
            <div class="flex items-center gap-2">
                <x-ui.icon name="search" class="ml-3 h-5 w-5 shrink-0 text-slate-400" />
                <input x-ref="q" type="search" name="q" maxlength="100" autocomplete="off"
                       placeholder="{{ t('nav.search_placeholder') }}"
                       aria-label="{{ t('nav.search_title') }}"
                       class="min-w-0 flex-1 border-0 bg-transparent py-3 text-base text-ink placeholder:text-slate-400 focus:outline-none focus:ring-0">
                <button type="button" @click="search = false" class="rounded-lg p-2.5 text-slate-500 transition hover:bg-slate-100">
                    <span class="sr-only">{{ t('nav.menu_close') }}</span>
                    <x-ui.icon name="close" class="h-5 w-5" />
                </button>
            </div>
        </form>

        <div class="mt-3 rounded-2xl bg-white/95 p-5">
            <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ t('nav.search_quick') }}</p>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($units as $unit)
                    <a href="{{ lroute('units.show', ['slug' => $unit['slug']]) }}"
                       class="rounded-full bg-brand-50 px-3.5 py-1.5 text-sm font-semibold text-brand-700 transition hover:bg-brand-100">
                        {{ $unit['name'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>