{{--
    Layout panel admin (BA4 revisi tampilan; badge Admin di dekat logo dihapus): sidebar terang berteks jelas, topbar kaca,
    latar lembut dengan gradasi biru tipis. Halaman: @extends('layouts.admin') + @section('title') + @section('content').
    Halaman tanpa sidebar (Masuk, Ganti Password wajib) memakai layouts.admin-guest.
--}}
@php
    $company = \App\Support\Content::company();
    $user = auth()->user();
    $menu = \App\Support\AdminMenu::for($user);
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="theme-color" content="#1B3590">
    <title>@yield('title', 'Admin') | {{ config('site.name') }}</title>
    @if ($favicon = favicon_url())
        <link rel="icon" href="{{ $favicon }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>.sidebar-scroll{scrollbar-width:thin;scrollbar-color:rgba(255,255,255,.18) transparent}.sidebar-scroll::-webkit-scrollbar{width:6px}.sidebar-scroll::-webkit-scrollbar-thumb{background:rgba(255,255,255,.18);border-radius:9999px}</style>
</head>
<body class="h-full bg-[#F4F6FB] font-sans text-slate-700 antialiased"
      x-data="{ sidebar: false }"
      x-effect="document.body.classList.toggle('overflow-hidden', sidebar)"
      @keydown.escape.window="sidebar = false"
      @resize.window="if (window.innerWidth >= 1024) sidebar = false">

    <a href="#isi" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[70] focus:rounded-xl focus:bg-white focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-[#1B3590] focus:shadow">
        Lewati ke isi
    </a>

    <div x-show="sidebar" x-cloak x-transition.opacity @click="sidebar = false"
         class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden" aria-hidden="true"></div>

    {{-- Sidebar terang: teks gelap berkontras tinggi --}}
    <aside id="sidebar" aria-label="Menu admin"
           :style="sidebar ? 'transform:none' : ''"
           class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-[#0B1437] shadow-[8px_0_32px_-12px_rgba(11,20,55,0.5)] transition-transform duration-200 lg:translate-x-0">

        <div class="flex h-20 shrink-0 items-center justify-between px-6">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center">
                <img src="{{ logo_url($company['logo']['on_dark']) }}" alt="{{ config('site.name') }}" class="h-6 w-auto">
            </a>
            <button type="button" @click="sidebar = false" aria-label="Tutup menu"
                    class="rounded-xl p-2 text-slate-300 hover:bg-white/10 lg:hidden">
                <x-admin.icon name="close" class="h-5 w-5" />
            </button>
        </div>

        <nav id="sidebar-nav" aria-label="Navigasi utama" class="sidebar-scroll relative flex-1 space-y-7 overflow-y-auto px-4 pb-6 pt-2">
            @foreach ($menu as $group)
                <div>
                    @if ($group['title'])
                        <p class="mb-2.5 px-3 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">{{ $group['title'] }}</p>
                    @endif

                    <ul class="space-y-1">
                        @foreach ($group['items'] as $item)
                            @php $active = request()->routeIs($item['active']); @endphp
                            <li>
                                @if ($item['soon'])
                                    <span aria-disabled="true" title="Segera hadir"
                                          class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3 py-2 text-[15px] font-medium text-slate-500">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5 text-slate-600">
                                            <x-admin.icon :name="$item['icon']" class="h-5 w-5" />
                                        </span>
                                        <span class="flex-1 truncate">{{ $item['label'] }}</span>
                                        <span class="rounded-full bg-white/10 px-2 py-0.5 text-[10px] font-semibold text-slate-400">Segera</span>
                                    </span>
                                @else
                                    <a href="{{ $item['url'] }}" @if ($active) aria-current="page" @endif
                                       class="group flex items-center gap-3 rounded-xl px-3 py-2 text-[15px] font-semibold transition
                                              {{ $active
                                                  ? 'bg-gradient-to-r from-[#2B4BC4] to-[#4A6CF0] text-white shadow-lg shadow-black/30'
                                                  : 'text-slate-200 hover:bg-white/10 hover:text-white' }}">
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg transition
                                                     {{ $active ? 'bg-white/20 text-white' : 'bg-white/10 text-slate-300 group-hover:bg-white/15 group-hover:text-white' }}">
                                            <x-admin.icon :name="$item['icon']" class="h-5 w-5" />
                                        </span>
                                        <span class="flex-1 truncate">{{ $item['label'] }}</span>
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>
        {{-- Jalankan sebelum cat pertama: sidebar langsung berada di menu aktif (tidak balik ke atas). --}}
        <script>
            (function () {
                var nav = document.getElementById('sidebar-nav');
                if (! nav) return;
                var active = nav.querySelector('[aria-current="page"]');
                var saved = null;
                try { saved = sessionStorage.getItem('admin.sidebar.scroll'); } catch (e) {}
                if (active) {
                    nav.scrollTop = active.offsetTop - (nav.clientHeight / 2) + (active.offsetHeight / 2);
                } else if (saved !== null) {
                    nav.scrollTop = parseInt(saved, 10) || 0;
                }
                nav.addEventListener('scroll', function () {
                    try { sessionStorage.setItem('admin.sidebar.scroll', nav.scrollTop); } catch (e) {}
                }, { passive: true });
            })();
        </script>

        <div class="shrink-0 p-4">
            <a href="{{ url('/') }}" target="_blank" rel="noopener"
               class="flex items-center justify-between gap-3 rounded-2xl bg-white/10 px-4 py-3.5 text-sm font-semibold text-white ring-1 ring-white/10 transition hover:bg-white/15">
                <span class="flex items-center gap-3">
                    <x-admin.icon name="external" class="h-5 w-5" /> Lihat Situs
                </span>
                <x-admin.icon name="chevron-right" class="h-4 w-4" />
            </a>
        </div>
    </aside>

    <div class="relative flex min-h-full flex-col lg:pl-72">
        {{-- Gradasi lembut di belakang bagian atas --}}
        <div class="pointer-events-none absolute inset-x-0 top-0 h-72 bg-gradient-to-b from-blue-100/70 via-blue-50/30 to-transparent" aria-hidden="true"></div>

        <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-3 bg-white/70 px-4 backdrop-blur-xl sm:px-6 lg:px-8">
            <button type="button" @click="sidebar = true" aria-label="Buka menu" aria-controls="sidebar"
                    class="-ml-2 rounded-xl p-2 text-slate-600 hover:bg-white lg:hidden">
                <x-admin.icon name="menu" class="h-6 w-6" />
            </button>

            <p class="hidden text-sm font-medium text-slate-500 sm:block">{{ now()->translatedFormat('l, j F Y') }}</p>

            <div class="ml-auto" x-data="{ open: false }" @keydown.escape="open = false" @click.outside="open = false">
                <div class="relative">
                    <button type="button" @click="open = ! open" :aria-expanded="open.toString()" aria-haspopup="menu"
                            class="flex items-center gap-3 rounded-full bg-white py-1.5 pl-1.5 pr-3.5 shadow-sm ring-1 ring-slate-900/5 transition hover:shadow-md">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-[#1B3590] to-[#4A6CF0] text-sm font-bold text-white">
                            {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                        </span>
                        <span class="hidden text-left sm:block">
                            <span class="block max-w-[10rem] truncate text-sm font-semibold leading-tight text-slate-900">{{ $user->name }}</span>
                            <span class="block text-xs leading-tight text-slate-500">{{ $user->roleLabel() }}</span>
                        </span>
                        <x-admin.icon name="chevron-down" class="h-4 w-4 text-slate-400" />
                    </button>

                    <div x-show="open" x-cloak x-transition role="menu"
                         class="absolute right-0 mt-3 w-64 origin-top-right overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-slate-900/5">
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 px-5 py-4">
                            <p class="truncate text-sm font-bold text-slate-900">{{ $user->name }}</p>
                            <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                        </div>
                        <div class="p-2">
                            <a href="{{ route('admin.profile.edit') }}" role="menuitem"
                               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                <x-admin.icon name="user-circle" class="h-5 w-5 text-slate-400" /> Profil Saya
                            </a>
                            <a href="{{ route('admin.security.edit') }}" role="menuitem"
                               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                <x-admin.icon name="shield" class="h-5 w-5 text-slate-400" /> Keamanan Akun
                            </a>
                            <a href="{{ route('admin.password.edit') }}" role="menuitem"
                               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                <x-admin.icon name="key" class="h-5 w-5 text-slate-400" /> Ganti Password
                            </a>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" role="menuitem"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-slate-50">
                                    <x-admin.icon name="logout" class="h-5 w-5 text-slate-400" /> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main id="isi" class="relative z-10 flex-1 px-4 py-6 sm:px-6 lg:px-8">
            @if (\App\Support\SystemSettings::maintenanceOn())
                <p class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-800 ring-1 ring-inset ring-red-200">
                    Mode pemeliharaan sedang <strong>menyala</strong>: pengunjung melihat halaman 503. Matikan di Pengaturan Sistem setelah selesai.
                </p>
            @endif
            @yield('content')
        </main>
    </div>

    <x-admin.toast />
</body>
</html>