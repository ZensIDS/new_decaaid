@extends('layouts.admin')

@section('title', 'Pengaturan Sistem')

@php
    $m = $data['maintenance'];
    $a = $data['analytics'];
    $v = $data['verification'];

    $state = [
        'enabled'  => (bool) (int) old('maintenance_enabled', $m['enabled'] ? 1 : 0),
        'secret'   => (string) old('maintenance_secret', $m['secret']),
        'provider' => (string) old('analytics_provider', $a['provider']),
    ];

    $field = 'mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]';
    $label = 'block text-sm font-medium text-slate-700';
@endphp

@section('content')
    <x-admin.page-header title="Pengaturan Sistem" description="Mode pemeliharaan, analitik, dan kode verifikasi mesin pencari. Hanya Super Admin."
                         :breadcrumbs="[['label' => 'Sistem'], ['label' => 'Pengaturan Sistem']]" />

    @unless ($available)
        <p class="mb-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-inset ring-amber-200">
            Tabel penyimpanan belum ada. Jalankan <code>php artisan migrate</code> dulu; sebelum itu perubahan belum bisa disimpan.
        </p>
    @endunless

    @if ($errors->any())
        <p class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-inset ring-red-200">Ada isian yang belum benar. Periksa pesan di bawah.</p>
    @endif

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <form method="POST" novalidate action="{{ route('admin.system.update') }}" class="contents"
              x-data="{
                  f: @js($state),
                  gen() { const a = new Uint8Array(12); crypto.getRandomValues(a); this.f.secret = 'buka-' + Array.from(a, b => (b % 36).toString(36)).join(''); },
                  copy() {
                      const url = location.origin + '/' + this.f.secret.trim();
                      const ok = () => this.$dispatch('toast', { type: 'success', text: 'Tautan akses disalin.' });
                      const fallback = () => {
                          const t = document.createElement('textarea');
                          t.value = url; t.style.position = 'fixed'; t.style.opacity = '0';
                          document.body.appendChild(t); t.select();
                          let done = false;
                          try { done = document.execCommand('copy'); } catch (e) {}
                          document.body.removeChild(t);
                          done ? ok() : this.$dispatch('toast', { type: 'error', text: 'Gagal menyalin. Salin manual dari kotak di atas.' });
                      };
                      if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(url).then(ok, fallback); } else { fallback(); }
                  },
              }">
            @csrf @method('PUT')

            <div class="space-y-6 lg:col-span-2 lg:col-start-1 lg:row-start-1">

                <x-admin.form-section icon="warning" title="Mode Pemeliharaan" description="Saat menyala, pengunjung melihat halaman 503 (sedang dalam pemeliharaan). Admin yang sudah masuk dan alamat /admin tetap bisa membuka situs.">
                    <x-admin.toggle name="maintenance_enabled" label="Nyalakan mode pemeliharaan" description="Matikan lagi setelah selesai; situs tidak bisa diakses pengunjung selama menyala." :checked="$state['enabled']" />
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach (['id' => 'Indonesia (ID)', 'en' => 'Inggris (EN)'] as $loc => $locName)
                            <div class="space-y-3">
                                <div>
                                    <label for="mt-{{ $loc }}" class="{{ $label }}">Judul halaman, {{ $locName }}</label>
                                    <input id="mt-{{ $loc }}" name="maintenance_title[{{ $loc }}]" maxlength="80" value="{{ old('maintenance_title.'.$loc, $m['title'][$loc] ?? '') }}"
                                           placeholder="{{ $loc === 'id' ? 'Sedang dalam pemeliharaan' : 'Under maintenance' }}" class="{{ $field }}">
                                    @error('maintenance_title.'.$loc)<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="mm-{{ $loc }}" class="{{ $label }}">Pesan, {{ $locName }}</label>
                                    <textarea id="mm-{{ $loc }}" name="maintenance_message[{{ $loc }}]" rows="3" maxlength="400"
                                              placeholder="{{ $loc === 'id' ? 'Kami sedang meningkatkan situs. Kembali sekitar pukul 14.00 WIB.' : 'We are improving the site. Back around 14:00 WIB.' }}" class="{{ $field }}">{{ old('maintenance_message.'.$loc, $m['message'][$loc] ?? '') }}</textarea>
                                    @error('maintenance_message.'.$loc)<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        @endforeach
                        <p class="text-xs text-slate-500 sm:col-span-2">Kosongkan untuk memakai teks bawaan. Bila EN kosong, versi Inggris memakai teks bawaan Inggris (bukan teks ID).</p>
                    </div>
                    <div>
                        <label for="mt-ips" class="{{ $label }}">Pengecualian alamat IP (opsional)</label>
                        <textarea id="mt-ips" name="maintenance_ips" rows="3" placeholder="{{ "mis.\n203.0.113.10\n2001:db8::1" }}" class="{{ $field }} font-mono">{{ old('maintenance_ips', implode("\n", $m['ips'] ?? [])) }}</textarea>
                        <p class="mt-1.5 text-xs text-slate-500">Satu alamat per baris (maks. 20). Alamat ini bisa membuka situs tanpa login atau tautan rahasia, cocok untuk kantor dengan IP tetap. IP kamu saat ini: <span class="font-mono">{{ request()->ip() }}</span>. Hati-hati: IP bersama (mis. jaringan seluler) berarti semua orang di jaringan itu ikut lolos.</p>
                        @error('maintenance_ips')<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label for="secret" class="{{ $label }}">Kata rahasia tautan akses</label>
                            <div class="flex gap-2">
                                <input id="secret" name="maintenance_secret" x-model="f.secret" maxlength="60" autocomplete="off" placeholder="mis. buka-a1b2c3d4e5f6" class="{{ $field }}">
                                <button type="button" @click="gen()" class="mt-1.5 shrink-0 rounded-xl px-4 text-sm font-semibold text-[#1B3590] ring-1 ring-inset ring-slate-200 hover:bg-blue-50">Buat Otomatis</button>
                            </div>
                            @error('maintenance_secret')<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4 text-sm ring-1 ring-inset ring-slate-200" x-show="f.secret.trim()" x-cloak>
                            <p class="text-slate-500">Tim yang belum login bisa membuka situs lewat tautan ini (berlaku 12 jam di perangkat itu):</p>
                            <p class="mt-1.5 break-all font-mono text-[13px] text-slate-800" x-text="location.origin + '/' + f.secret.trim()"></p>
                            <button type="button" @click="copy()" class="mt-2 text-sm font-semibold text-[#1B3590] hover:underline">Salin Tautan</button>
                            <p class="mt-2 text-xs text-slate-500">Jaga kerahasiaannya. Mengganti kata rahasia langsung mencabut akses semua tautan lama.</p>
                        </div>
                    </div>
                </x-admin.form-section>

                <x-admin.form-section icon="academic" title="Analitik" description="Skrip pelacak hanya dimuat di lingkungan production (tidak di staging atau lokal).">
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($providers as $key => [$name, $hint])
                            <label class="cursor-pointer">
                                <input type="radio" name="analytics_provider" value="{{ $key }}" class="peer sr-only" x-model="f.provider" @checked($state['provider'] === $key)>
                                <span class="flex h-full flex-col rounded-2xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200 transition hover:bg-white hover:shadow-md peer-checked:bg-blue-50 peer-checked:ring-2 peer-checked:ring-[#1B3590]">
                                    <span class="text-sm font-semibold text-slate-800">{{ $name }}</span>
                                    <span class="mt-1 text-xs text-slate-500">{{ $hint }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <div x-show="f.provider !== 'none'" x-cloak class="space-y-4">
                        <div>
                            <label for="aid" class="{{ $label }}"><span x-text="f.provider === 'ga4' ? 'ID pengukuran' : (f.provider === 'plausible' ? 'Nama domain' : 'ID situs (UUID)')"></span> <span class="text-[#E5352E]">*</span></label>
                            <input id="aid" name="analytics_id" value="{{ old('analytics_id', $a['id']) }}" maxlength="100" autocomplete="off" class="{{ $field }}">
                            @error('analytics_id')<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                        </div>
                        <div x-show="f.provider !== 'ga4'">
                            <label for="asu" class="{{ $label }}">Alamat skrip (opsional)</label>
                            <input id="asu" type="url" name="analytics_script_url" value="{{ old('analytics_script_url', $a['script_url']) }}" maxlength="255" placeholder="https://" class="{{ $field }}">
                            <p class="mt-1.5 text-xs text-slate-500">Kosongkan untuk layanan cloud bawaan. Isi bila Plausible/Umami dipasang di server sendiri.</p>
                            @error('analytics_script_url')<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    @unless ($production)
                        <p class="text-xs text-slate-500">Lingkungan ini bukan production, jadi skrip belum dimuat meskipun sudah diisi.</p>
                    @endunless
                </x-admin.form-section>

                <x-admin.form-section icon="link" title="Verifikasi Mesin Pencari" description="Isi hanya nilai content dari tag meta verifikasi (bukan tag lengkap). Tag meta dipasang di semua halaman publik.">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="vg" class="{{ $label }}">Google Search Console</label>
                            <input id="vg" name="verification_google" value="{{ old('verification_google', $v['google']) }}" maxlength="100" autocomplete="off" class="{{ $field }}">
                            @error('verification_google')<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="vb" class="{{ $label }}">Bing Webmaster</label>
                            <input id="vb" name="verification_bing" value="{{ old('verification_bing', $v['bing']) }}" maxlength="100" autocomplete="off" class="{{ $field }}">
                            @error('verification_bing')<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </x-admin.form-section>

                <x-admin.form-section icon="mail" title="Email Pengirim" description="Hanya informasi. Diatur di berkas .env, bukan di sini (K31).">
                    <dl class="space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Alamat pengirim</dt><dd class="font-medium text-slate-700">{{ $fromEmail ?: '-' }}</dd></div>
                        <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Metode kirim</dt><dd class="font-medium text-slate-700">{{ $mailer ?: '-' }}</dd></div>
                    </dl>
                    <p class="text-xs text-slate-500">Metode <code>log</code> berarti email hanya dicatat di <code>storage/logs</code>, belum benar-benar terkirim.</p>
                </x-admin.form-section>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:col-start-3 lg:row-span-3 lg:row-start-1">
                <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                    <div class="px-6 pb-5 pt-6">
                        <x-admin.badge :tone="$m['enabled'] ? 'danger' : 'success'">{{ $m['enabled'] ? 'Pemeliharaan Menyala' : 'Situs Normal' }}</x-admin.badge>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Analitik</dt><dd class="font-medium text-slate-700" x-text="({none: 'Tidak ada', ga4: 'GA4', plausible: 'Plausible', umami: 'Umami'})[f.provider]"></dd></div>
                            @if ($row)
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah oleh</dt><dd class="text-right font-medium text-slate-700">{{ $row->editor?->name ?? '-' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah</dt><dd class="font-medium text-slate-700">{{ $row->updated_at?->translatedFormat('j M Y, H:i') }}</dd></div>
                            @endif
                        </dl>
                        <p class="mt-4 text-xs leading-relaxed text-slate-500">Noindex untuk staging tetap diatur di <code>.env</code> (<code>SITE_NOINDEX</code>), tidak di sini.</p>
                    </div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <x-admin.button type="submit" class="w-full">Simpan Perubahan</x-admin.button>
                    </div>
                </section>
            </aside>
        </form>
    </div>
@endsection
