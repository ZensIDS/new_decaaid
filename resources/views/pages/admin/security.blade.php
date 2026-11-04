@extends($forced ? 'layouts.admin-guest' : 'layouts.admin')

@section('title', 'Keamanan Akun')

@php
    $card = $forced
        ? 'rounded-3xl bg-white p-8 shadow-[0_20px_50px_-20px_rgba(27,53,144,0.25)] ring-1 ring-slate-900/5 sm:p-10'
        : 'max-w-2xl rounded-2xl bg-white p-6 shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(16,24,40,0.12)] ring-1 ring-slate-900/5 sm:p-8';
    $primary = 'rounded-xl bg-gradient-to-r from-[#1B3590] to-[#3558D6] px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-900/25 transition hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1B3590] focus-visible:ring-offset-2';
    $field = 'mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]';
@endphp

@section('content')
    @unless ($forced)
        <x-admin.page-header title="Keamanan Akun" description="Verifikasi 2 langkah menambah kode dari ponsel Anda saat masuk, sehingga password yang bocor saja tidak cukup untuk membuka akun."
                             :breadcrumbs="[['label' => 'Akun'], ['label' => 'Keamanan Akun']]" />
    @endunless

    <div class="{{ $card }} space-y-6"
         x-data="{
             copied: false,
             copy(text) {
                 const done = () => { this.copied = true; setTimeout(() => this.copied = false, 2000); };
                 if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(text).then(done, () => this.fallback(text, done)); } else { this.fallback(text, done); }
             },
             fallback(text, done) {
                 const t = document.createElement('textarea');
                 t.value = text; t.style.position = 'fixed'; t.style.opacity = '0';
                 document.body.appendChild(t); t.select();
                 try { if (document.execCommand('copy')) done(); } catch (e) {}
                 document.body.removeChild(t);
             },
             download(text) {
                 const a = document.createElement('a');
                 a.href = URL.createObjectURL(new Blob([text], { type: 'text/plain' }));
                 a.download = 'kode-pemulihan-decaa.txt';
                 document.body.appendChild(a); a.click(); document.body.removeChild(a);
             },
         }">

        @if ($forced)
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Aktifkan Verifikasi 2 Langkah</h1>
            <div class="rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-amber-200" role="alert">
                Peran {{ $user->roleLabel() }} wajib memakai verifikasi 2 langkah. Selesaikan penyiapan ini untuk membuka panel admin.
            </div>
        @endif

        @if (session('status') && ! $codes)
            <div class="rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700 ring-1 ring-emerald-200" role="status">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-red-200" role="alert">{{ session('error') }}</div>
        @endif

        {{-- Kode pemulihan: tampil sekali setelah dibuat --}}
        @if ($codes)
            @php $text = implode("\n", $codes); @endphp
            <div class="rounded-2xl bg-emerald-50 p-5 ring-1 ring-emerald-200">
                <h2 class="text-base font-bold text-emerald-900">Simpan kode pemulihan Anda sekarang</h2>
                <p class="mt-1 text-sm leading-relaxed text-emerald-800">Kode ini dipakai bila ponsel hilang atau rusak. Tiap kode hanya berlaku sekali dan <strong>tidak akan ditampilkan lagi</strong> setelah halaman ini ditutup. Simpan di tempat aman (pengelola password atau dicetak), jangan di ponsel yang sama.</p>
                <ul class="mt-4 grid grid-cols-2 gap-2 font-mono text-sm font-semibold tracking-wider text-slate-900">
                    @foreach ($codes as $c)
                        <li class="rounded-lg bg-white px-3 py-2 text-center ring-1 ring-inset ring-emerald-200">{{ $c }}</li>
                    @endforeach
                </ul>
                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <button type="button" @click="copy(@js($text))" class="rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                        <span x-show="! copied">Salin semua</span><span x-show="copied" x-cloak>Tersalin</span>
                    </button>
                    <button type="button" @click="download(@js($text))" class="rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">Unduh .txt</button>
                    @if ($forced === false && ! $user->must_change_password)
                        <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold text-[#1B3590] hover:underline">Sudah saya simpan, ke Dashboard</a>
                    @endif
                </div>
            </div>
        @endif

        @if ($user->hasTwoFactor())
            {{-- ===== Sudah aktif ===== --}}
            <div class="flex flex-wrap items-center gap-3">
                <x-admin.badge tone="success">Verifikasi 2 langkah aktif</x-admin.badge>
                <span class="text-sm text-slate-500">sejak {{ $user->two_factor_confirmed_at?->translatedFormat('j M Y, H:i') }}</span>
            </div>

            <div class="rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-600 ring-1 ring-inset ring-slate-200">
                Kode pemulihan tersisa: <strong class="text-slate-900">{{ $left }}</strong>
                @if ($left <= 2)
                    <span class="ml-1 font-semibold text-amber-700">Hampir habis, buat kode baru di bawah.</span>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.security.recovery') }}" novalidate class="space-y-3 border-t border-slate-100 pt-6">
                @csrf
                <h2 class="text-base font-bold text-slate-900">Buat ulang kode pemulihan</h2>
                <p class="text-sm text-slate-500">Kode lama langsung tidak berlaku. Masukkan password Anda untuk melanjutkan.</p>
                <div>
                    <label for="rec_pw" class="block text-sm font-medium text-slate-700">Password saat ini</label>
                    <input id="rec_pw" type="password" name="current_password" autocomplete="current-password" class="{{ $field }}" required>
                    @if ($errors->recovery->has('current_password'))<p class="mt-1.5 text-sm text-[#E5352E]">{{ $errors->recovery->first('current_password') }}</p>@endif
                </div>
                <button type="submit" class="rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">Buat Kode Baru</button>
            </form>

            <div class="border-t border-slate-100 pt-6">
                <h2 class="text-base font-bold text-slate-900">Nonaktifkan verifikasi 2 langkah</h2>
                @if ($user->requiresTwoFactor())
                    <p class="mt-1 text-sm text-slate-500">Peran {{ $user->roleLabel() }} wajib memakai verifikasi 2 langkah, jadi tidak bisa dinonaktifkan. Bila ganti ponsel, minta Super Admin mengatur ulang lalu siapkan lagi.</p>
                @else
                    <form method="POST" action="{{ route('admin.security.disable') }}" novalidate class="mt-3 space-y-3"
                          onsubmit="return confirm('Nonaktifkan verifikasi 2 langkah? Akun Anda hanya dilindungi password.')">
                        @csrf
                        @method('DELETE')
                        <div>
                            <label for="dis_pw" class="block text-sm font-medium text-slate-700">Password saat ini</label>
                            <input id="dis_pw" type="password" name="current_password" autocomplete="current-password" class="{{ $field }}" required>
                            @if ($errors->disable->has('current_password'))<p class="mt-1.5 text-sm text-[#E5352E]">{{ $errors->disable->first('current_password') }}</p>@endif
                        </div>
                        <button type="submit" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-[#E5352E] ring-1 ring-inset ring-red-200 hover:bg-red-50">Nonaktifkan</button>
                    </form>
                @endif
            </div>

        @elseif ($setup)
            {{-- ===== Penyiapan berjalan ===== --}}
            <ol class="space-y-6">
                <li>
                    <h2 class="text-base font-bold text-slate-900">1. Pasang aplikasi autentikator</h2>
                    <p class="mt-1 text-sm leading-relaxed text-slate-500">Di ponsel, pasang salah satu: Google Authenticator, Microsoft Authenticator, Authy, atau pengelola password yang mendukung kode 2 langkah (1Password, Bitwarden).</p>
                </li>
                <li>
                    <h2 class="text-base font-bold text-slate-900">2. Tambahkan akun DECAA.ID</h2>
                    <p class="mt-1 text-sm leading-relaxed text-slate-500">Di aplikasi pilih <em>Tambah akun</em>, lalu <em>Masukkan kunci penyiapan</em> (bukan pindai). Isi nama akun dengan email Anda, kunci di bawah, dan jenis <em>Berbasis waktu</em>.</p>
                    <div class="mt-3 flex flex-wrap items-center gap-3 rounded-xl bg-slate-50 px-4 py-3 ring-1 ring-inset ring-slate-200">
                        <code class="select-all break-all font-mono text-base font-semibold tracking-wider text-slate-900">{{ $setup['formatted'] }}</code>
                        <button type="button" @click="copy(@js($setup['secret']))" class="rounded-lg bg-white px-3 py-1.5 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50">
                            <span x-show="! copied">Salin kunci</span><span x-show="copied" x-cloak>Tersalin</span>
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Membuka halaman ini dari ponsel yang sama? <a href="{{ $setup['uri'] }}" class="font-semibold text-[#1B3590] hover:underline">Buka langsung di aplikasi autentikator</a>.</p>
                </li>
                <li>
                    <h2 class="text-base font-bold text-slate-900">3. Masukkan kode untuk memastikan</h2>
                    <form method="POST" action="{{ route('admin.security.confirm') }}" novalidate class="mt-2 space-y-3" x-data="{ busy: false }" @submit="busy = true">
                        @csrf
                        <x-admin.input name="code" label="Kode 6 digit dari aplikasi" required autofocus autocomplete="one-time-code" inputmode="numeric" maxlength="7" />
                        <div class="flex flex-wrap items-center gap-3">
                            <button type="submit" :disabled="busy" class="{{ $primary }} disabled:opacity-60">Aktifkan</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('admin.security.cancel') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="text-sm text-slate-500 hover:text-slate-700">Batalkan penyiapan</button>
                    </form>
                </li>
            </ol>

        @else
            {{-- ===== Belum aktif ===== --}}
            <div class="flex flex-wrap items-center gap-3">
                <x-admin.badge tone="warning">Belum aktif</x-admin.badge>
                @unless ($user->requiresTwoFactor())<span class="text-sm text-slate-500">Opsional untuk peran Editor, tetapi disarankan.</span>@endunless
            </div>
            <p class="text-sm leading-relaxed text-slate-600">Setelah aktif, setiap kali masuk Anda memasukkan password lalu kode 6 digit yang berubah tiap 30 detik di aplikasi autentikator di ponsel. Anda juga mendapat kode pemulihan untuk keadaan darurat. Penyiapan sekitar 2 menit.</p>
            <form method="POST" action="{{ route('admin.security.start') }}">
                @csrf
                <button type="submit" class="{{ $primary }}">Mulai Penyiapan</button>
            </form>
        @endif

        @if ($forced)
            <form method="POST" action="{{ route('admin.logout') }}" class="border-t border-slate-100 pt-4">
                @csrf
                <button type="submit" class="text-sm text-slate-500 hover:text-slate-700">Keluar</button>
            </form>
        @endif
    </div>
@endsection
