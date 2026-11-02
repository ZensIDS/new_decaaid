@extends('layouts.admin-guest')

@section('title', 'Masuk')

@section('content')
    <div class="rounded-3xl bg-white p-8 shadow-[0_20px_50px_-20px_rgba(27,53,144,0.25)] ring-1 ring-slate-900/5 sm:p-10">
        <h1 class="text-2xl font-bold tracking-tight text-slate-900">Masuk ke Panel Admin</h1>
        <p class="mt-1.5 text-sm text-slate-500">Gunakan akun admin yang diberikan kepada Anda.</p>

        @if (session('status'))
            <div class="mt-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700 ring-1 ring-emerald-200" role="status">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.store') }}" class="mt-7 space-y-5" novalidate
              x-data="{ show: false, busy: false }" @submit="busy = true">
            @csrf

            <x-admin.input name="email" type="email" label="Email" required autofocus autocomplete="username" />

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">
                    Password<span class="text-[#E5352E]" aria-hidden="true"> *</span>
                </label>
                <div class="relative">
                    <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="current-password"
                           @if ($errors->has('password')) aria-invalid="true" @endif
                           class="mt-1.5 block w-full rounded-xl border-0 bg-slate-50 py-3 pl-4 pr-11 text-sm text-slate-900 ring-1 ring-inset focus:ring-2 focus:ring-inset {{ $errors->has('password') ? 'ring-[#E5352E] focus:ring-[#E5352E]' : 'ring-slate-200 focus:bg-white focus:ring-[#1B3590]' }}">
                    <button type="button" @click="show = ! show" :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
                            class="absolute inset-y-0 right-0 mt-1.5 flex w-11 items-center justify-center rounded-r-lg text-slate-400 hover:text-slate-600">
                        <x-admin.icon name="eye" class="h-5 w-5" x-show="! show" />
                        <x-admin.icon name="eye-slash" class="h-5 w-5" x-show="show" x-cloak />
                    </button>
                </div>
                @error('password')
                    <p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-[#1B3590] focus:ring-[#1B3590]">
                Ingat saya di perangkat ini
            </label>

            <button type="submit" :disabled="busy"
                    class="w-full rounded-xl bg-gradient-to-r from-[#1B3590] to-[#3558D6] px-4 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-900/25 transition hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1B3590] focus-visible:ring-offset-2 disabled:opacity-60">
                <span x-show="! busy">Masuk</span>
                <span x-show="busy" x-cloak>Memproses...</span>
            </button>
        </form>
    </div>
@endsection