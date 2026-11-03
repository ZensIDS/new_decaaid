@extends('layouts.admin')

@section('title', 'Pengaturan Situs')

@php
    $pair = fn ($v) => ['id' => (string) ($v['id'] ?? ''), 'en' => (string) ($v['en'] ?? '')];

    $state = [
        'tagline'         => $pair(old('tagline', $data['tagline'])),
        'phone'           => (string) old('phone', $data['phone']),
        'whatsapp_number' => (string) old('whatsapp_number', $data['whatsapp_number']),
        'social'          => collect(array_keys($socials))->mapWithKeys(fn ($n) => [$n => (string) old("social.$n", $data['social'][$n] ?? '')])->all(),
        'offices'         => collect(old('offices', $data['offices']))->map(fn ($o) => [
            'type'    => $pair($o['type'] ?? []),
            'city'    => (string) ($o['city'] ?? ''),
            'address' => $pair($o['address'] ?? []),
        ])->values()->all(),
    ];

    $enErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.en'));
    $idErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.id') || ! str_contains($k, '.'));
    $startLang = $enErrors && ! $idErrors ? 'en' : 'id';

    $field = 'mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]';
    $label = 'block text-sm font-medium text-slate-700';
    $ghost = 'rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800 disabled:opacity-30 disabled:hover:bg-transparent';
@endphp

@section('content')
    <x-admin.page-header title="Pengaturan Situs" description="Slogan, kontak, sosial media, dan daftar kantor yang tampil di footer, halaman Kontak, dan Profil Perusahaan."
                         :breadcrumbs="[['label' => 'Situs'], ['label' => 'Pengaturan Situs']]" />

    @unless ($available)
        <p class="mb-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-inset ring-amber-200">
            Tabel penyimpanan belum ada. Jalankan <code>php artisan migrate</code> dulu; sebelum itu perubahan belum bisa disimpan.
        </p>
    @endunless

    @if ($errors->any())
        <p class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-inset ring-red-200">
            Ada isian yang belum benar. Periksa pesan di bawah, termasuk di tab bahasa lain.
        </p>
    @endif

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <form method="POST" novalidate action="{{ route('admin.settings.update') }}" class="contents"
              x-data="{
                  lang: @js($startLang),
                  f: @js($state),
                  max: {{ $maxOffices }},
                  add() { if (this.f.offices.length < this.max) this.f.offices.push({ type: { id: '', en: '' }, city: '', address: { id: '', en: '' } }); },
                  del(i) { this.f.offices.splice(i, 1); },
                  move(i, d) { const a = this.f.offices, j = i + d; if (j < 0 || j >= a.length) return; [a[i], a[j]] = [a[j], a[i]]; },
              }">
            @csrf @method('PUT')

            <div class="space-y-6 lg:col-span-2 lg:col-start-1 lg:row-start-1">

                <div class="flex items-center gap-1 rounded-xl bg-white p-1 shadow-sm ring-1 ring-slate-900/5" role="tablist" aria-label="Bahasa isi">
                    <button type="button" role="tab" :aria-selected="(lang === 'id').toString()" @click="lang = 'id'"
                            :class="lang === 'id' ? 'bg-blue-50 text-[#1B3590] shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                            class="flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition">Indonesia (ID)</button>
                    <button type="button" role="tab" :aria-selected="(lang === 'en').toString()" @click="lang = 'en'"
                            :class="lang === 'en' ? 'bg-blue-50 text-[#1B3590] shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                            class="flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition">English (EN)</button>
                </div>
                <p class="-mt-3 px-1 text-xs text-slate-500">Tab bahasa berlaku untuk slogan dan daftar kantor. Bahasa Inggris boleh dikosongkan; pengunjung EN akan melihat teks Indonesia.</p>

                <x-admin.form-section icon="sliders" title="Identitas" description="Slogan di footer, hero, dan deskripsi meta halaman tanpa deskripsi sendiri.">
                    <div>
                        <label class="{{ $label }}"><span>Slogan</span> <span x-text="lang === 'id' ? '(ID)' : '(EN)'"></span><span x-show="lang === 'id'" class="text-[#E5352E]"> *</span></label>
                        @foreach (['id', 'en'] as $l)
                            <div x-show="lang === '{{ $l }}'" @if ($l === 'en') x-cloak @endif>
                                <input maxlength="160" name="tagline[{{ $l }}]" x-model="f.tagline.{{ $l }}" class="{{ $field }}">
                                @error('tagline.'.$l)<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>
                </x-admin.form-section>

                <x-admin.form-section icon="mail" title="Kontak" description="Nomor yang tampil di footer dan halaman Kontak. Email umum tetap info@decaa.id (tujuan formulir Kontak) dan tidak diatur di sini.">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="phone" class="{{ $label }}">Telepon kantor</label>
                            <input id="phone" name="phone" x-model="f.phone" maxlength="30" inputmode="tel" placeholder="{{ $sample['phone'] }}" class="{{ $field }}">
                            @error('phone')<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="wa" class="{{ $label }}">Nomor WhatsApp</label>
                            <input id="wa" name="whatsapp_number" x-model="f.whatsapp_number" maxlength="30" inputmode="tel" placeholder="{{ $sample['whatsapp'] }}" class="{{ $field }}">
                            @error('whatsapp_number')<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <p class="text-xs leading-relaxed text-slate-500">Kosongkan untuk memakai nomor contoh (placeholder abu-abu). Tautan WhatsApp dibuat otomatis dari nomor; awalan 0 diganti kode negara 62.</p>
                </x-admin.form-section>

                <x-admin.form-section icon="link" title="Sosial Media" description="Alamat lengkap diawali https://. Yang dikosongkan memakai tautan contoh (ikon tetap tampil tetapi belum mengarah ke mana pun).">
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($socials as $net => $name)
                            <div>
                                <label for="social-{{ $net }}" class="{{ $label }}">{{ $name }}</label>
                                <input id="social-{{ $net }}" type="url" name="social[{{ $net }}]" x-model="f.social.{{ $net }}" maxlength="255" placeholder="https://" class="{{ $field }}">
                                @error('social.'.$net)<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>
                </x-admin.form-section>

                <x-admin.form-section icon="home" title="Kantor" description="Urutan pertama dianggap kantor pusat (kartu besar di Profil Perusahaan dan halaman Kontak). Maksimal {{ $maxOffices }} kantor.">
                    <div class="space-y-4">
                        <template x-for="(o, i) in f.offices" :key="i">
                            <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold text-slate-700" x-text="'Kantor ' + (i + 1) + (i === 0 ? ' (pusat)' : '')"></p>
                                    <div class="flex items-center">
                                        <button type="button" class="{{ $ghost }}" :disabled="i === 0" @click="move(i, -1)" aria-label="Naikkan"><x-admin.icon name="arrow-left" class="h-4 w-4 rotate-90" /></button>
                                        <button type="button" class="{{ $ghost }}" :disabled="i === f.offices.length - 1" @click="move(i, 1)" aria-label="Turunkan"><x-admin.icon name="arrow-right" class="h-4 w-4 rotate-90" /></button>
                                        <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-[#E5352E]" @click="del(i)" aria-label="Hapus kantor"><x-admin.icon name="trash" class="h-4 w-4" /></button>
                                    </div>
                                </div>
                                <div class="mt-2 space-y-3">
                                    <div>
                                        <label class="{{ $label }}">Kota</label>
                                        <input maxlength="80" :name="'offices[' + i + '][city]'" x-model="o.city" class="{{ $field }}">
                                    </div>
                                    <div>
                                        <label class="{{ $label }}" x-text="lang === 'id' ? 'Jenis kantor (ID), mis. Kantor Pusat' : 'Office type (EN), e.g. Headquarters'"></label>
                                        <input maxlength="60" :name="'offices[' + i + '][type][' + lang + ']'" x-model="o.type[lang]" class="{{ $field }}">
                                    </div>
                                    <div>
                                        <label class="{{ $label }}" x-text="lang === 'id' ? 'Alamat (ID)' : 'Address (EN)'"></label>
                                        <textarea rows="2" maxlength="250" :name="'offices[' + i + '][address][' + lang + ']'" x-model="o.address[lang]" class="{{ $field }}"></textarea>
                                    </div>
                                    <input type="hidden" :name="'offices[' + i + '][type][' + (lang === 'id' ? 'en' : 'id') + ']'" :value="o.type[lang === 'id' ? 'en' : 'id']">
                                    <input type="hidden" :name="'offices[' + i + '][address][' + (lang === 'id' ? 'en' : 'id') + ']'" :value="o.address[lang === 'id' ? 'en' : 'id']">
                                </div>
                            </div>
                        </template>
                    </div>
                    <button type="button" x-show="f.offices.length < max" @click="add()" class="text-sm font-semibold text-[#1B3590] hover:underline">+ Tambah kantor</button>
                    @error('offices')<p class="text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                    @foreach ($errors->get('offices.*') as $msgs)<p class="text-sm text-[#E5352E]">{{ $msgs[0] }}</p>@endforeach
                </x-admin.form-section>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:col-start-3 lg:row-span-3 lg:row-start-1">
                <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                    <div class="px-6 pb-5 pt-6">
                        <x-admin.badge :tone="$row ? 'success' : 'dummy'">{{ $row ? 'Isi dari admin' : 'Data contoh' }}</x-admin.badge>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Kantor</dt><dd class="font-medium text-slate-700" x-text="f.offices.length + ' lokasi'"></dd></div>
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Telepon</dt><dd class="truncate text-right font-medium text-slate-700" x-text="f.phone.trim() || '{{ $sample['phone'] }} (contoh)'"></dd></div>
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">WhatsApp</dt><dd class="truncate text-right font-medium text-slate-700" x-text="f.whatsapp_number.trim() || '{{ $sample['whatsapp'] }} (contoh)'"></dd></div>
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Sosmed terisi</dt><dd class="font-medium text-slate-700" x-text="Object.values(f.social).filter(v => v.trim()).length + ' dari {{ count($socials) }}'"></dd></div>
                            @if ($row)
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah oleh</dt><dd class="text-right font-medium text-slate-700">{{ $row->editor?->name ?? '-' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah</dt><dd class="font-medium text-slate-700">{{ $row->updated_at?->translatedFormat('j M Y, H:i') }}</dd></div>
                            @endif
                        </dl>
                        @unless ($row)
                            <p class="mt-4 text-xs leading-relaxed text-slate-500">Situs sekarang memakai data contoh. Setelah disimpan, isi dari formulir ini yang tampil.</p>
                        @endunless
                    </div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <x-admin.button type="submit" class="w-full">Simpan Perubahan</x-admin.button>
                        <x-admin.button variant="secondary" class="w-full" icon="external" target="_blank" rel="noopener" :href="lroute('contact', [], 'id')">Lihat Di Situs</x-admin.button>
                    </div>
                </section>

                @if ($row)
                    <button type="button" @click="$dispatch('open-modal', { name: 'reset', action: '{{ route('admin.settings.reset') }}' })"
                            class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-[#E5352E] hover:bg-red-50">
                        <x-admin.icon name="trash" class="h-4 w-4" /> Kembalikan Ke Data Contoh
                    </button>
                @endif
            </aside>
        </form>
    </div>

    @if ($row)
        <x-admin.confirm name="reset" title="Kembalikan ke Data Contoh?" method="DELETE" button="Ya, Kembalikan"
                         message="Isi yang sudah Anda simpan dihapus dan situs memakai data contoh bawaan lagi (telepon dan sosmed contoh). Tindakan ini tidak bisa dibatalkan." />
    @endif
@endsection
