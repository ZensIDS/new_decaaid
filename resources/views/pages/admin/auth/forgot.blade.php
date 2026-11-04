@extends('layouts.admin-guest')

@section('title', 'Lupa Password')

@section('content')
    <div class="rounded-3xl bg-white p-8 shadow-[0_20px_50px_-20px_rgba(27,53,144,0.25)] ring-1 ring-slate-900/5 sm:p-10">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Lupa Password</h1>
        <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Masukkan email akun admin Anda. Kami mengirim tautan untuk membuat password baru.</p>

        @if (session('status'))
            <div class="mt-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm leading-relaxed text-emerald-700 ring-1 ring-emerald-200" role="status">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.forgot.send') }}" class="mt-7 space-y-5" novalidate
              x-data="{ busy: false }" @submit="busy = true">
            @csrf
            <x-admin.input name="email" type="email" label="Email" required autofocus autocomplete="username" />

            <button type="submit" :disabled="busy"
                    class="w-full rounded-xl bg-gradient-to-r from-[#1B3590] to-[#3558D6] px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-900/25 transition hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1B3590] focus-visible:ring-offset-2 disabled:opacity-60">
                <span x-show="! busy">Kirim Tautan</span>
                <span x-show="busy" x-cloak>Memproses...</span>
            </button>
        </form>

        <p class="mt-6 border-t border-slate-100 pt-4 text-sm text-slate-500">
            Tidak menerima email? Hubungi Super Admin; ia bisa mengatur password sementara untuk Anda.
        </p>
        <a href="{{ route('admin.login') }}" class="mt-3 inline-block text-sm font-semibold text-[#1B3590] hover:underline">Kembali ke halaman masuk</a>
    </div>
@endsection
