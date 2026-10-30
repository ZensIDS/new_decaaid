@php
    $company = \App\Support\Content::company();
    $nav = \App\Support\Content::nav();
    $units = \App\Support\Content::units();

    // Halaman aktif: "units.show" => grup "units"
    $current = \Illuminate\Support\Str::after(request()->route()?->getName() ?? '', '.');
    $group = \Illuminate\Support\Str::before($current, '.');
    $isActive = function (array $item) use ($group) {
        $routes = array_merge([$item['route']], array_column($item['children'] ?? [], 'route'));
        foreach ($routes as $r) {
            if (\Illuminate\Support\Str::before($r, '.') === $group) {
                return true;
            }
        }

        return false;
    };
@endphp

{{-- "contents": pembungkus state Alpine tanpa memengaruhi layout (agar sticky tetap jalan) --}}
<div class="contents"
     x-data="{ scrolled: false, mobile: false, search: false, open: null }"
     x-init="scrolled = window.scrollY > 8;
             $watch('mobile', v => $nextTick(() => (v ? $refs.mClose : $refs.mOpen)?.focus()));
             $watch('search', v => { if (! v) $nextTick(() => $refs.sOpen?.focus()) })"
     x-effect="document.documentElement.classList.toggle('overflow-hidden', mobile || search)"
     @scroll.window.passive="scrolled = window.scrollY > 8"
     @keydown.escape.window="open = null; mobile = false; search = false">

    <header class="sticky top-0 z-40 border-b border-slate-100 transition duration-300"
            :class="scrolled ? 'bg-white/95 shadow-card backdrop-blur' : 'bg-white'">
        <div class="container flex h-20 items-center justify-between gap-6">

            {{-- Logo --}}
            <a href="{{ lroute('home') }}" class="flex shrink-0 items-center" aria-label="{{ $company['name'] }}">
                <img src="{{ logo_url($company['logo']['on_light']) }}" alt="{{ $company['name'] }}"
                     class="h-7 w-auto lg:h-8"
                     onerror="this.hidden=true;this.nextElementSibling.hidden=false">
                <span hidden class="text-xl font-extrabold text-brand-700">{{ $company['name'] }}</span>
            </a>

            {{-- Menu desktop --}}
            <nav class="hidden lg:block" aria-label="{{ t('nav.main') }}" @click.outside="open = null">
                <ul class="flex h-20 items-center gap-0.5">
                    @foreach ($nav as $i => $item)
                        @php
                            $has = ! empty($item['children']);
                            $mega = $item['route'] === 'units.index';
                            $active = $isActive($item);
                        @endphp
                        <li class="flex h-20 items-center {{ $mega ? '' : 'relative' }}"
                            @if ($has) @mouseenter="open = {{ $i }}" @mouseleave="open = null" @endif>
                            <div class="flex items-center rounded-lg transition {{ $active ? 'bg-brand-50' : '' }}">
                                <a href="{{ lroute($item['route'], $item['params'] ?? []) }}"
                                   @if ($current === $item['route']) aria-current="page" @endif
                                   class="rounded-lg py-2 text-sm font-semibold transition {{ $has ? 'pl-3 pr-1' : 'px-3' }} {{ $active ? 'text-brand-700' : 'text-ink hover:text-brand-700' }}">
                                    {{ $item['label'] }}
                                </a>
                                @if ($has)
                                    <button type="button" class="rounded-lg p-1.5 pr-2 text-slate-500 transition hover:text-brand-700"
                                            :aria-expanded="open === {{ $i }}"
                                            @click="open = open === {{ $i }} ? null : {{ $i }}">
                                        <span class="sr-only">{{ t('nav.submenu') }}: {{ $item['label'] }}</span>
                                        <x-ui.icon name="chevron-down" class="h-4 w-4 transition" ::class="open === {{ $i }} && 'rotate-180'" />
                                    </button>
                                @endif
                            </div>

                            @if ($has && $mega)
                                {{-- Mega menu Unit Bisnis (logo unit) --}}
                                <div x-cloak x-show="open === {{ $i }}" x-transition.opacity.duration.150ms
                                     class="absolute inset-x-0 top-full border-t border-slate-100 bg-white shadow-card-hover">
                                    <div class="container py-8">
                                        <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                                            @foreach ($item['children'] as $child)
                                                <a href="{{ lroute($child['route'], $child['params'] ?? []) }}"
                                                   class="flex items-center gap-3 rounded-xl p-3 transition hover:bg-brand-50">
                                                    <x-ui.img :src="$child['logo']" :alt="$child['label']"
                                                              class="h-12 w-16 shrink-0 rounded-lg bg-slate-50 object-contain" />
                                                    <span class="text-sm font-semibold text-ink">{{ $child['label'] }}</span>
                                                </a>
                                            @endforeach
                                        </div>
                                        <div class="mt-5 border-t border-slate-100 pt-4">
                                            <x-ui.button :href="lroute($item['route'])" variant="link" size="sm" icon="arrow-right">
                                                {{ t('nav.all_units') }}
                                            </x-ui.button>
                                        </div>
                                    </div>
                                </div>
                            @elseif ($has)
                                {{-- Dropdown biasa --}}
                                <div x-cloak x-show="open === {{ $i }}" x-transition.opacity.duration.150ms
                                     class="absolute left-0 top-full w-64 rounded-b-xl border border-t-0 border-slate-100 bg-white p-2 shadow-card-hover">
                                    @foreach ($item['children'] as $child)
                                        <a href="{{ lroute($child['route'], $child['params'] ?? []) }}"
                                           class="block rounded-lg px-3 py-2.5 text-sm font-semibold text-ink transition hover:bg-brand-50 hover:text-brand-700">
                                            {{ $child['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>

            {{-- Kanan: pencarian + bahasa + hamburger --}}
            <div class="flex items-center gap-2">
                <button type="button" @click="search = true" x-ref="sOpen"
                        aria-haspopup="dialog" :aria-expanded="search"
                        class="rounded-lg p-2.5 text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">
                    <span class="sr-only">{{ t('nav.search') }}</span>
                    <x-ui.icon name="search" class="h-5 w-5" />
                </button>

                <div class="hidden lg:block">@include('partials.lang-switch')</div>

                <button type="button" @click="mobile = true" x-ref="mOpen"
                        aria-haspopup="dialog" aria-controls="mobile-menu" :aria-expanded="mobile"
                        class="rounded-lg p-2.5 text-slate-600 transition hover:bg-brand-50 hover:text-brand-700 lg:hidden">
                    <span class="sr-only">{{ t('nav.menu_open') }}</span>
                    <x-ui.icon name="menu" class="h-6 w-6" />
                </button>
            </div>
        </div>
    </header>

    {{-- Drawer menu mobile (di luar <header> karena backdrop-blur membuat elemen fixed ikut terkurung) --}}
    <div x-cloak x-show="mobile" x-transition.opacity.duration.200ms x-focustrap id="mobile-menu"
         class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="{{ t('nav.menu') }}">
        <div class="absolute inset-0 bg-brand-950/60" @click="mobile = false"></div>

        <div class="absolute inset-y-0 right-0 flex w-full max-w-sm flex-col bg-white shadow-card-hover">
            <div class="flex h-20 shrink-0 items-center justify-between border-b border-slate-100 px-5">
                <a href="{{ lroute('home') }}" aria-label="{{ $company['name'] }}">
                    <img src="{{ logo_url($company['logo']['on_light']) }}" alt="{{ $company['name'] }}" class="h-7 w-auto"
                         onerror="this.hidden=true;this.nextElementSibling.hidden=false">
                    <span hidden class="text-xl font-extrabold text-brand-700">{{ $company['name'] }}</span>
                </a>
                <button type="button" @click="mobile = false" x-ref="mClose" class="rounded-lg p-2.5 text-slate-600 transition hover:bg-slate-100">
                    <span class="sr-only">{{ t('nav.menu_close') }}</span>
                    <x-ui.icon name="close" class="h-6 w-6" />
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-4" aria-label="{{ t('nav.main') }}">
                <ul class="space-y-1">
                    @foreach ($nav as $item)
                        @php $has = ! empty($item['children']); $active = $isActive($item); @endphp
                        <li x-data="{ o: {{ $active && $has ? 'true' : 'false' }} }">
                            <div class="flex items-center rounded-lg {{ $active ? 'bg-brand-50' : '' }}">
                                <a href="{{ lroute($item['route'], $item['params'] ?? []) }}"
                                   @if ($current === $item['route']) aria-current="page" @endif
                                   class="flex-1 rounded-lg px-3 py-3 text-base font-semibold {{ $active ? 'text-brand-700' : 'text-ink' }}">
                                    {{ $item['label'] }}
                                </a>
                                @if ($has)
                                    <button type="button" @click="o = ! o" :aria-expanded="o" class="rounded-lg p-3 text-slate-500">
                                        <span class="sr-only">{{ t('nav.submenu') }}: {{ $item['label'] }}</span>
                                        <x-ui.icon name="chevron-down" class="h-5 w-5 transition" ::class="o && 'rotate-180'" />
                                    </button>
                                @endif
                            </div>
                            @if ($has)
                                <ul x-cloak x-show="o" x-collapse class="ml-3 border-l border-slate-200 pl-2">
                                    @foreach ($item['children'] as $child)
                                        <li>
                                            <a href="{{ lroute($child['route'], $child['params'] ?? []) }}"
                                               class="block rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-brand-50 hover:text-brand-700">
                                                {{ $child['label'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="shrink-0 space-y-4 border-t border-slate-100 p-5">
                <x-ui.button :href="lroute('contact')" class="w-full">{{ t('nav.contact_us') }}</x-ui.button>
                <div class="flex items-center justify-between text-sm font-semibold text-slate-500">
                    <span>{{ t('nav.language') }}</span>
                    @include('partials.lang-switch')
                </div>
            </div>
        </div>
    </div>

    @include('partials.search')
</div>