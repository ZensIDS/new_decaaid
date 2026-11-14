@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    {{-- Sambutan --}}
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#1B3590] via-[#2644B0] to-[#4A6CF0] px-6 py-8 text-white shadow-xl shadow-blue-900/15 sm:px-10 sm:py-10">
        <div class="pointer-events-none absolute -right-16 -top-20 h-72 w-72 rounded-full bg-white/10 blur-xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-24 right-40 h-64 w-64 rounded-full bg-[#E5352E]/25 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.1]" aria-hidden="true"
             style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 24px 24px;"></div>

        <div class="relative flex flex-wrap items-end justify-between gap-6">
            <div class="min-w-0">
                <p class="text-sm font-medium text-blue-100">Selamat datang kembali</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-white sm:text-4xl">{{ auth()->user()->name }}</h1>
                <p class="mt-3 text-sm text-blue-100">
                    @if (auth()->user()->last_login_at)
                        Login terakhir: {{ auth()->user()->last_login_at->translatedFormat('j F Y, H:i') }}
                    @else
                        Selamat bekerja di panel admin {{ config('site.name') }}.
                    @endif
                </p>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-sm font-semibold text-white ring-1 ring-white/25 backdrop-blur">
                <x-admin.icon name="shield" class="h-5 w-5" /> {{ auth()->user()->roleLabel() }}
            </span>
        </div>
    </section>

    {{-- Ringkasan --}}
    <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <x-admin.stat-card :label="$stat['label']" :value="$stat['value']" :icon="$stat['icon']"
                               :tone="$stat['tone']" :note="$stat['note']" />
        @endforeach
    </div>

    {{-- Aktivitas Terbaru: selebar halaman --}}
    <x-admin.card class="mt-6" title="Aktivitas Terbaru" description="Siapa mengubah apa, dibaca dari log aktivitas." flush>
        @if (auth()->user()->isSuperAdmin())
            <x-slot name="actions">
                <a href="{{ route('admin.logs.index') }}" class="text-sm font-semibold text-[#1B3590] hover:underline">Lihat Semua</a>
            </x-slot>
        @endif

        @if (count($activities))
            <ul class="mt-2 divide-y divide-slate-100">
                @foreach ($activities as $activity)
                    <li class="flex items-center gap-4 px-6 py-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-50 to-indigo-100 text-sm font-bold text-[#1B3590]">
                            {{ mb_strtoupper(mb_substr($activity['who'], 0, 1)) }}
                        </span>
                        <p class="min-w-0 flex-1 text-sm text-slate-600">
                            <span class="font-semibold text-slate-900">{{ $activity['who'] }}</span> {{ $activity['what'] }}
                        </p>
                        <time datetime="{{ $activity['at']->toIso8601String() }}" title="{{ $activity['at']->translatedFormat('j F Y, H:i') }}"
                              class="shrink-0 text-right text-xs font-medium text-slate-400">
                            <span class="hidden sm:inline">{{ $activity['at']->translatedFormat('j M Y, H:i') }} &middot; </span>{{ $activity['at']->diffForHumans() }}
                        </time>
                    </li>
                @endforeach
            </ul>
        @else
            <x-admin.empty icon="clock" title="Belum ada aktivitas" text="Perubahan yang dilakukan pengguna admin akan tercatat di sini." class="m-5" />
        @endif
    </x-admin.card>
@endsection