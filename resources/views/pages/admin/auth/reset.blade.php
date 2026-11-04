@extends('layouts.admin-guest')

@section('title', 'Buat Password Baru')

@section('content')
    <div class="rounded-3xl bg-white p-8 shadow-[0_20px_50px_-20px_rgba(27,53,144,0.25)] ring-1 ring-slate-900/5 sm:p-10">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Buat Password Baru</h1>
        <p class="mt-1.5 text-sm text-slate-500">Gunakan password yang kuat dan tidak dipakai di tempat lain.</p>

        <form method="POST" action="{{ route('admin.reset.store') }}" class="mt-7 space-y-5" novalidate
              x-data="{ busy: false }" @submit="busy = true">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <x-admin.input name="email" type="email" label="Email" :value="$email" required autocomplete="username" />
            <x-admin.input name="password" type="password" label="Password baru" required autofocus autocomplete="new-password"
                           hint="Minimal 10 karakter, mengandung huruf dan angka." />
            <x-admin.input name="password_confirmation" type="password" label="Ulangi password baru" required autocomplete="new-password" />

            <button type="submit" :disabled="busy"
                    class="w-full rounded-xl bg-gradient-to-r from-[#1B3590] to-[#3558D6] px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-900/25 transition hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1B3590] focus-visible:ring-offset-2 disabled:opacity-60">
                <span x-show="! busy">Simpan Password Baru</span>
                <span x-show="busy" x-cloak>Memproses...</span>
            </button>
        </form>

        <a href="{{ route('admin.forgot.show') }}" class="mt-6 inline-block border-t border-slate-100 pt-4 text-sm text-slate-500 hover:text-slate-700">Minta tautan baru</a>
    </div>
@endsection
