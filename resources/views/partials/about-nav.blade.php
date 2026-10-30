{{-- Tab navigasi antar halaman "Tentang Kami". Butuh variabel $pageKey (dari PageController).
     Pil melayang yang menumpuk di tepi bawah hero, lalu MENEMPEL di bawah header saat di-scroll
     (header sticky h-20 = 5rem, z-40; nav ini z-30 agar tidak tenggelam di bawah header).
     Di layar sempit: tab bisa digeser, ada panah kiri/kanan (muncul hanya bila ada tab tersembunyi),
     dan tab aktif otomatis ditaruh di tengah saat halaman dibuka. --}}
@php
    $aboutMenu = \App\Support\Content::nav()[0];
    $en = app()->getLocale() === 'en';
    $labelLeft = $en ? 'Scroll tabs left' : 'Geser tab ke kiri';
    $labelRight = $en ? 'Scroll tabs right' : 'Geser tab ke kanan';
@endphp

<nav aria-label="{{ $aboutMenu['label'] }}" class="pointer-events-none sticky top-[5.75rem] z-30 -mt-7 md:-mt-8"
     x-data="{
        left: false,
        right: false,
        update() {
            const el = this.$refs.rail;
            this.left = el.scrollLeft > 4;
            this.right = el.scrollLeft + el.clientWidth < el.scrollWidth - 4;
        },
        go(dir) {
            const el = this.$refs.rail;
            el.scrollBy({ left: dir * el.clientWidth * 0.6, behavior: 'smooth' });
        },
     }"
     x-init="$nextTick(() => {
        const el = $refs.rail;
        const active = el.querySelector('[aria-current=page]');
        if (active) { el.scrollLeft = active.offsetLeft - (el.clientWidth - active.offsetWidth) / 2; }
        update();
     })"
     @resize.window.debounce.150ms="update()">
    <div class="container">
        <div class="pointer-events-auto relative mx-auto w-fit max-w-full rounded-full border border-slate-200/80 bg-white shadow-card-hover">

            {{-- Panah kiri --}}
            <div x-cloak x-show="left" x-transition.opacity.duration.200ms
                 class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center rounded-l-full bg-gradient-to-r from-white from-60% to-transparent py-1.5 pl-1.5 pr-5">
                <button type="button" @click="go(-1)" aria-label="{{ $labelLeft }}"
                        class="pointer-events-auto flex h-9 w-9 items-center justify-center rounded-full bg-white text-brand-700 shadow-card ring-1 ring-slate-200 transition hover:bg-brand-50">
                    <x-ui.icon name="chevron-left" class="h-5 w-5" />
                </button>
            </div>

            <ul x-ref="rail" @scroll.passive="update()"
                class="relative flex gap-1 overflow-x-auto scroll-smooth p-1.5 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                @foreach ($aboutMenu['children'] as $item)
                    @php $active = $item['route'] === ($pageKey ?? null); @endphp
                    <li class="shrink-0">
                        <a href="{{ lroute($item['route']) }}"
                           @if ($active) aria-current="page" @endif
                           class="block whitespace-nowrap rounded-full px-4 py-2.5 text-sm font-semibold transition md:px-6 {{ $active ? 'bg-brand-700 text-white shadow-sm' : 'text-slate-600 hover:bg-brand-50 hover:text-brand-700' }}">
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>

            {{-- Panah kanan --}}
            <div x-cloak x-show="right" x-transition.opacity.duration.200ms
                 class="pointer-events-none absolute inset-y-0 right-0 z-10 flex items-center rounded-r-full bg-gradient-to-l from-white from-60% to-transparent py-1.5 pl-5 pr-1.5">
                <button type="button" @click="go(1)" aria-label="{{ $labelRight }}"
                        class="pointer-events-auto flex h-9 w-9 items-center justify-center rounded-full bg-white text-brand-700 shadow-card ring-1 ring-slate-200 transition hover:bg-brand-50">
                    <x-ui.icon name="chevron-right" class="h-5 w-5" />
                </button>
            </div>
        </div>
    </div>
</nav>