{{--
    Layout admin tanpa sidebar: Masuk dan Ganti Password wajib.
    Layar lebar: panel merek bergradasi di kiri, formulir di kanan. HP: hanya formulir.
--}}
@php $company = \App\Support\Content::company(); @endphp
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="theme-color" content="#1B3590">
    <title>@yield('title', 'Admin') | {{ config('site.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-[#F4F6FB] font-sans text-slate-700 antialiased">
    <div class="flex min-h-full">

        <aside class="relative hidden w-[48%] max-w-3xl overflow-hidden bg-gradient-to-br from-[#1B3590] via-[#2644B0] to-[#4A6CF0] lg:flex lg:flex-col lg:justify-between lg:p-14" aria-hidden="true">
            <div class="pointer-events-none absolute -right-28 -top-28 h-[28rem] w-[28rem] rounded-full bg-white/10 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-40 -left-24 h-[30rem] w-[30rem] rounded-full bg-[#E5352E]/25 blur-3xl"></div>
            <div class="pointer-events-none absolute inset-0 opacity-[0.12]"
                 style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 24px 24px;"></div>

            <img src="{{ logo_url($company['logo']['on_dark']) }}" alt="" class="relative h-10 w-auto self-start">

            <div class="relative">
                <p class="inline-flex rounded-full bg-white/15 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-widest text-white ring-1 ring-white/25 backdrop-blur">Panel Admin</p>
                <p class="mt-6 text-4xl font-bold leading-tight tracking-tight text-white">
                    Kelola konten {{ config('site.name') }} dengan mudah dan aman.
                </p>

                <ul class="mt-10 space-y-3.5">
                    @foreach ([
                        ['newspaper', 'Berita dan portofolio dalam satu tempat'],
                        ['translate', 'Dwibahasa Indonesia dan Inggris'],
                        ['shield', 'Akses aman dengan peran pengguna'],
                    ] as [$icon, $text])
                        <li class="flex items-center gap-3.5 text-[15px] font-medium text-white/90">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/25 backdrop-blur">
                                <x-admin.icon :name="$icon" class="h-5 w-5 text-white" />
                            </span>
                            {{ $text }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <p class="relative text-xs text-white/60">&copy; {{ date('Y') }} {{ config('site.name') }}</p>
        </aside>

        <main class="relative flex flex-1 items-center justify-center overflow-hidden px-4 py-10 sm:px-8">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-64 bg-gradient-to-b from-blue-100/70 to-transparent lg:hidden" aria-hidden="true"></div>

            <div class="relative w-full max-w-md">
                <img src="{{ logo_url($company['logo']['on_light']) }}" alt="{{ config('site.name') }}" class="mx-auto mb-8 h-9 w-auto lg:hidden">

                @yield('content')

                <p class="mt-8 text-center text-xs text-slate-400 lg:hidden">&copy; {{ date('Y') }} {{ config('site.name') }}</p>
            </div>
        </main>
    </div>
</body>
</html>