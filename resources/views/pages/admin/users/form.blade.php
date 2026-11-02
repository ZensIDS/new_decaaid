@extends('layouts.admin')

@php
    $editing = (bool) $user;
    $self = $editing && $user->is(auth()->user());
    $roleInfo = [
        \App\Models\User::ROLE_SUPER_ADMIN => ['Akses penuh', 'Semua modul, pengguna, log aktivitas, dan pengaturan sistem.', 'shield'],
        \App\Models\User::ROLE_ADMIN => ['Kelola konten', 'Semua konten, pesan masuk, dan pengaturan situs. Tanpa pengguna.', 'sliders'],
        \App\Models\User::ROLE_EDITOR => ['Menulis konten', 'Hanya Berita, Proyek, dan Penghargaan. Tidak bisa menghapus.', 'newspaper'],
    ];
    $currentRole = old('role', $editing ? $user->role : \App\Models\User::ROLE_EDITOR);
@endphp

@section('title', $editing ? 'Ubah Pengguna' : 'Tambah Pengguna')

@section('content')
    <x-admin.page-header :title="$editing ? 'Ubah Pengguna' : 'Tambah Pengguna'"
                         :description="$editing ? 'Perbarui data, peran, atau status akun ini.' : 'Pengguna baru memakai password sementara dan wajib menggantinya saat masuk pertama kali.'"
                         :breadcrumbs="[['label' => 'Pengguna', 'url' => route('admin.users.index')], ['label' => $editing ? 'Ubah' : 'Tambah']]" />

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <form method="POST" novalidate class="contents"
              x-data="{ name: @js(old('name', $editing ? $user->name : '')), email: @js(old('email', $editing ? $user->email : '')) }"
              action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="space-y-6 lg:col-span-2 lg:col-start-1 lg:row-start-1">
                <x-admin.form-section icon="user-circle" title="Informasi Akun" description="Nama tampil di panel dan log aktivitas; email dipakai untuk masuk.">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.input name="name" label="Nama lengkap" required autocomplete="off" x-model="name" :value="$editing ? $user->name : null" />
                        <x-admin.input name="email" type="email" label="Email" required autocomplete="off" x-model="email" :value="$editing ? $user->email : null"
                                       hint="Tidak ada email otomatis yang dikirim." />
                    </div>
                </x-admin.form-section>

                <x-admin.form-section icon="shield" title="Peran dan Akses" description="Menentukan menu dan tindakan yang boleh dilakukan.">
                    @if ($self)
                        <div class="flex items-start gap-3 rounded-xl bg-blue-50 px-4 py-3.5 text-sm text-slate-700 ring-1 ring-blue-100" role="note">
                            <x-admin.icon name="lock" class="mt-0.5 h-5 w-5 shrink-0 text-[#1B3590]" />
                            <p>Ini akun Anda sendiri, jadi peran (<strong>{{ $user->roleLabel() }}</strong>) dan status dikunci agar Anda tidak terkunci dari panel. Ubah password lewat menu Ganti Password.</p>
                        </div>
                    @else
                        <fieldset>
                            <legend class="sr-only">Peran</legend>
                            <div class="grid gap-3 sm:grid-cols-3">
                                @foreach (\App\Models\User::ROLES as $value => $label)
                                    <label class="group relative cursor-pointer">
                                        <input type="radio" name="role" value="{{ $value }}" class="peer sr-only" @checked($currentRole === $value) required>
                                        <span class="flex h-full flex-col rounded-2xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200 transition hover:bg-white hover:shadow-md peer-checked:bg-blue-50 peer-checked:ring-2 peer-checked:ring-[#1B3590] peer-checked:[&_.role-icon]:bg-[#1B3590] peer-checked:[&_.role-icon]:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-[#1B3590]">
                                            <span class="role-icon flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-500 shadow-sm transition">
                                                <x-admin.icon :name="$roleInfo[$value][2]" class="h-5 w-5" />
                                            </span>
                                            <span class="mt-3 text-sm font-bold text-slate-900">{{ $label }}</span>
                                            <span class="text-xs font-semibold text-[#1B3590]">{{ $roleInfo[$value][0] }}</span>
                                            <span class="mt-1.5 text-xs leading-relaxed text-slate-500">{{ $roleInfo[$value][1] }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('role')<p class="mt-2 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                        </fieldset>
                    @endif

                    @if ($editing && ! $self)
                        <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                            <x-admin.toggle name="is_active" label="Akun aktif" description="Akun nonaktif langsung dikeluarkan dan tidak bisa masuk." :checked="$user->is_active" />
                        </div>
                    @endif
                </x-admin.form-section>

                @unless ($editing)
                    <x-admin.form-section icon="lock" title="Keamanan" description="Password sementara; pengguna wajib menggantinya saat masuk pertama kali.">
                        <x-admin.password-field name="password" label="Password sementara" required
                                                hint="Minimal 10 karakter, huruf dan angka. Salin sebelum menyimpan; tidak bisa dilihat lagi sesudahnya." />
                    </x-admin.form-section>
                @endunless
            </div>

            {{-- Panel samping: pratinjau + aksi --}}
            <aside class="space-y-6 lg:sticky lg:top-24 lg:col-start-3 lg:row-span-2 lg:row-start-1">
                <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                    <div class="relative h-24 bg-gradient-to-br from-[#1B3590] via-[#2644B0] to-[#4A6CF0]">
<div class="pointer-events-none absolute inset-0 opacity-[0.12]" aria-hidden="true"
                             style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 20px 20px;"></div>
</div>
<div class="px-6 pb-6 text-center">
<span class="relative -mt-10 mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-white text-3xl font-bold text-[#1B3590] shadow-xl ring-4 ring-white"
                              x-text="(name.trim().charAt(0) || '?').toUpperCase()"></span>
                        <p class="mt-3 truncate text-base font-bold text-slate-900" x-text="name.trim() || 'Nama pengguna'"></p>
                        <p class="truncate text-sm text-slate-500" x-text="email.trim() || 'email@contoh.id'"></p>
                        @if ($editing)
                            <dl class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-left text-sm">
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Status</dt>
                                    <dd><x-admin.badge :tone="$user->is_active ? 'success' : 'danger'">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</x-admin.badge></dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Password</dt>
                                    <dd class="font-medium text-slate-700">{{ $user->must_change_password ? 'Sementara' : 'Sudah diganti' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Login terakhir</dt>
                                    <dd class="text-right font-medium text-slate-700">{{ $user->last_login_at ? $user->last_login_at->translatedFormat('j M Y, H:i') : 'Belum pernah' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Dibuat</dt>
                                    <dd class="font-medium text-slate-700">{{ $user->created_at?->translatedFormat('j M Y') ?? '-' }}</dd></div>
                            </dl>
                        @endif
                    </div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <x-admin.button type="submit" class="w-full">{{ $editing ? 'Simpan Perubahan' : 'Buat Pengguna' }}</x-admin.button>
                        <x-admin.button variant="ghost" class="w-full" :href="route('admin.users.index')">Batal</x-admin.button>
                    </div>
                </section>
            </aside>
        </form>

        @if ($editing && ! $self)
            <div class="lg:col-span-2 lg:col-start-1 lg:row-start-2">
                <x-admin.form-section icon="key" title="Atur Ulang Password" description="Untuk pengguna yang lupa password. Ia wajib menggantinya saat masuk berikutnya.">
                    <form method="POST" action="{{ route('admin.users.password', $user) }}" novalidate class="space-y-5">
                        @csrf
                        @method('PUT')
                        <x-admin.password-field name="new_password" label="Password sementara baru" required hint="Minimal 10 karakter, huruf dan angka." />
                        <x-admin.button type="submit" variant="secondary">Simpan Password Sementara</x-admin.button>
                    </form>
                </x-admin.form-section>
            </div>
        @endif
    </div>
@endsection