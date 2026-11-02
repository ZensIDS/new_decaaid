@extends($forced ? 'layouts.admin-guest' : 'layouts.admin')

@section('title', 'Ganti Password')

@section('content')
    @unless ($forced)
        <x-admin.page-header title="Ganti Password" description="Gunakan password yang kuat dan tidak dipakai di tempat lain."
                             :breadcrumbs="[['label' => 'Akun'], ['label' => 'Ganti Password']]" />
    @endunless

    <div @class([
        'rounded-3xl bg-white p-8 shadow-[0_20px_50px_-20px_rgba(27,53,144,0.25)] ring-1 ring-slate-900/5 sm:p-10' => $forced,
        'max-w-xl rounded-2xl bg-white p-6 shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(16,24,40,0.12)] ring-1 ring-slate-900/5 sm:p-8' => ! $forced,
    ])>
        @if ($forced)
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Buat Password Baru</h1>
            <div class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-amber-200" role="alert">
                Anda memakai password sementara. Buat password baru sebelum melanjutkan.
            </div>
        @endif

        <form method="POST" action="{{ route('admin.password.update') }}" class="{{ $forced ? 'mt-6' : '' }} space-y-5" novalidate>
            @csrf
            @method('PUT')

            <x-admin.input name="current_password" type="password" label="Password saat ini" required autocomplete="current-password" />
            <x-admin.input name="password" type="password" label="Password baru" required autocomplete="new-password"
                           hint="Minimal 10 karakter, mengandung huruf dan angka, dan berbeda dari password lama." />
            <x-admin.input name="password_confirmation" type="password" label="Ulangi password baru" required autocomplete="new-password" />

            <div class="flex items-center gap-3 pt-1">
                <x-admin.button type="submit">Simpan Password</x-admin.button>

                @unless ($forced)
                    <x-admin.button variant="ghost" :href="route('admin.dashboard')">Batal</x-admin.button>
                @endunless
            </div>
        </form>

        @if ($forced)
            <form method="POST" action="{{ route('admin.logout') }}" class="mt-6 border-t border-slate-100 pt-4">
                @csrf
                <button type="submit" class="text-sm text-slate-500 hover:text-slate-700">Keluar</button>
            </form>
        @endif
    </div>
@endsection