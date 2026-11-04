@extends('layouts.admin-guest')

@section('title', 'Verifikasi 2 Langkah')

@section('content')
    <div class="rounded-3xl bg-white p-8 shadow-[0_20px_50px_-20px_rgba(27,53,144,0.25)] ring-1 ring-slate-900/5 sm:p-10">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Verifikasi 2 Langkah</h1>
        <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Buka aplikasi autentikator di ponsel Anda, lalu masukkan kode 6 digit untuk akun DECAA.ID.</p>

        @if (session('status'))
            <div class="mt-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700 ring-1 ring-emerald-200" role="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.two-factor.store') }}" class="mt-7 space-y-5" novalidate
              x-data="{ busy: false }" @submit="busy = true">
            @csrf

            <x-admin.input name="code" label="Kode verifikasi atau kode pemulihan" required autofocus
                           autocomplete="one-time-code" inputmode="text" autocapitalize="characters" spellcheck="false"
                           hint="Kode 6 digit dari aplikasi. Bila ponsel tidak ada, ketik salah satu kode pemulihan (bentuk XXXXX-XXXXX); tiap kode hanya berlaku sekali." />

            <button type="submit" :disabled="busy"
                    class="w-full rounded-xl bg-gradient-to-r from-[#1B3590] to-[#3558D6] px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-900/25 transition hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1B3590] focus-visible:ring-offset-2 disabled:opacity-60">
                <span x-show="! busy">Verifikasi dan Masuk</span>
                <span x-show="busy" x-cloak>Memproses...</span>
            </button>
        </form>

        <form method="POST" action="{{ route('admin.two-factor.cancel') }}" class="mt-6 border-t border-slate-100 pt-4">
            @csrf
            <button type="submit" class="text-sm text-slate-500 hover:text-slate-700">Kembali ke halaman masuk</button>
        </form>
    </div>
@endsection
