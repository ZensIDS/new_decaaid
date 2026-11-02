@extends('layouts.admin')

@section('title', 'Profil Saya')

@section('content')
    <x-admin.page-header title="Profil Saya" description="Nama Anda tampil di topbar dan log aktivitas."
                         :breadcrumbs="[['label' => 'Akun'], ['label' => 'Profil Saya']]" />

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('admin.profile.update') }}" novalidate class="contents"
              x-data="{ name: @js(old('name', $user->name)) }">
            @csrf
            @method('PUT')

            <x-admin.form-section icon="user-circle" title="Data Profil" description="Email dan peran hanya bisa diubah oleh Super Admin." class="lg:col-span-2">
                <x-admin.input name="name" label="Nama lengkap" :value="$user->name" required autocomplete="name" x-model="name" />

                <div>
                    <label for="email-ro" class="block text-sm font-medium text-slate-700">Email</label>
                    <div class="relative mt-1.5">
                        <input id="email-ro" type="email" value="{{ $user->email }}" readonly
                               class="block w-full cursor-not-allowed rounded-xl border-0 bg-slate-100 py-3 pl-4 pr-11 text-sm text-slate-500 ring-1 ring-inset ring-slate-200">
                        <x-admin.icon name="lock" class="pointer-events-none absolute right-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                    </div>
                </div>

                <div class="flex items-center gap-3 border-t border-slate-100 pt-5">
                    <x-admin.button type="submit">Simpan Profil</x-admin.button>
                    <x-admin.button variant="ghost" :href="route('admin.dashboard')">Batal</x-admin.button>
                </div>
            </x-admin.form-section>

            <aside class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                <div class="relative h-24 bg-gradient-to-br from-[#1B3590] via-[#2644B0] to-[#4A6CF0]">
<div class="pointer-events-none absolute inset-0 opacity-[0.12]" aria-hidden="true"
                         style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 20px 20px;"></div>
</div>
<div class="px-6 pb-6 text-center">
<span class="relative -mt-10 mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-white text-3xl font-bold text-[#1B3590] shadow-xl ring-4 ring-white"
                          x-text="(name.trim().charAt(0) || '?').toUpperCase()"></span>
                    <p class="mt-3 truncate text-base font-bold text-slate-900" x-text="name.trim() || 'Nama Anda'"></p>
                    <p class="truncate text-sm text-slate-500">{{ $user->email }}</p>
                    <dl class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-left text-sm">
                        <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Peran</dt>
                            <dd><x-admin.badge :tone="$user->isSuperAdmin() ? 'brand' : 'neutral'">{{ $user->roleLabel() }}</x-admin.badge></dd></div>
                        <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Login terakhir</dt>
                            <dd class="text-right font-medium text-slate-700">{{ $user->last_login_at ? $user->last_login_at->translatedFormat('j M Y, H:i') : '-' }}</dd></div>
                    </dl>
                </div>
                <div class="border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                    <x-admin.button variant="secondary" icon="key" class="w-full" :href="route('admin.password.edit')">Ganti Password</x-admin.button>
                </div>
            </aside>
        </form>
    </div>
@endsection