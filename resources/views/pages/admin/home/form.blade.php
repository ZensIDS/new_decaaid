@extends('layouts.admin')

@section('title', 'Beranda')

@php
    $pair = fn ($v) => ['id' => (string) ($v['id'] ?? ''), 'en' => (string) ($v['en'] ?? '')];

    $state = [];
    foreach ($groups as $section => [, , , $fields]) {
        foreach (array_keys($fields) as $field) {
            $state[$section][$field] = $pair(old("$section.$field", $data[$section][$field] ?? []));
        }
    }
    $state['values']['items'] = collect(old('values.items', $data['values']['items']))
        ->map(fn ($r) => ['title' => $pair($r['title'] ?? []), 'text' => $pair($r['text'] ?? [])])->values()->all();
    $state['community']['points'] = collect(old('community.points', $data['community']['points']))
        ->map(fn ($r) => $pair($r))->values()->all();
    $state['sections'] = collect(old('sections') ?? $sections)
        ->map(fn ($r) => [
            'key'     => (string) $r['key'],
            'label'   => \App\Support\Content::HOME_SECTIONS[$r['key']] ?? (string) $r['key'],
            'visible' => filter_var($r['visible'], FILTER_VALIDATE_BOOLEAN),
        ])->values()->all();

    $enErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.en'));
    $idErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.id') || ! str_contains($k, '.'));
    $startLang = $enErrors && ! $idErrors ? 'en' : 'id';

    $field = 'mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]';
    $label = 'block text-sm font-medium text-slate-700';
    $ghost = 'rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800 disabled:opacity-30 disabled:hover:bg-transparent';
@endphp

@section('content')
    <x-admin.page-header title="Beranda" description="Teks setiap bagian Beranda serta tampil/sembunyi dan urutannya. Foto, proyek unggulan, brand, logo klien, penghargaan, dan berita diatur di modulnya masing-masing."
                         :breadcrumbs="[['label' => 'Konten'], ['label' => 'Beranda']]" />

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
        <form method="POST" novalidate action="{{ route('admin.home.update') }}" class="contents"
              x-data="{
                  lang: @js($startLang),
                  f: @js($state),
                  addValue() { if (this.f.values.items.length < {{ $maxValues }}) this.f.values.items.push({ title: { id: '', en: '' }, text: { id: '', en: '' } }); },
                  addPoint() { if (this.f.community.points.length < {{ $maxPoints }}) this.f.community.points.push({ id: '', en: '' }); },
                  del(arr, i) { arr.splice(i, 1); },
                  move(arr, i, d) { const j = i + d; if (j < 0 || j >= arr.length) return; [arr[i], arr[j]] = [arr[j], arr[i]]; },
                  get shown() { return this.f.sections.filter(s => s.visible).length; },
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
                <p class="-mt-3 px-1 text-xs text-slate-500" x-show="lang === 'en'" x-cloak>Bahasa Inggris boleh dikosongkan; pengunjung EN akan melihat teks Indonesia untuk isian yang kosong.</p>

                {{-- Susunan section --}}
                <x-admin.form-section icon="squares" title="Susunan Section" description="Atur urutan dan tampil/sembunyikan bagian di antara hero (paling atas) dan ajakan Hubungi Kami (paling bawah). Warna latar tiap bagian tetap, jadi dua bagian berlatar sama bisa bersebelahan.">
                    <ul class="space-y-2">
                        <template x-for="(s, i) in f.sections" :key="s.key">
                            <li class="flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-3 ring-1 ring-inset ring-slate-200" :class="s.visible ? '' : 'opacity-60'">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white text-xs font-bold text-slate-500 ring-1 ring-inset ring-slate-200" x-text="i + 1"></span>
                                <span class="min-w-0 flex-1 truncate text-sm font-semibold text-slate-800" x-text="s.label"></span>
                                <span x-show="!s.visible" x-cloak class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-600">Tersembunyi</span>
                                <input type="hidden" :name="'sections[' + i + '][key]'" :value="s.key">
                                <input type="hidden" :name="'sections[' + i + '][visible]'" :value="s.visible ? 1 : 0">
                                <div class="flex shrink-0 items-center">
                                    <button type="button" class="{{ $ghost }}" :disabled="i === 0" @click="move(f.sections, i, -1)" :aria-label="'Naikkan ' + s.label"><x-admin.icon name="arrow-left" class="h-4 w-4 rotate-90" /></button>
                                    <button type="button" class="{{ $ghost }}" :disabled="i === f.sections.length - 1" @click="move(f.sections, i, 1)" :aria-label="'Turunkan ' + s.label"><x-admin.icon name="arrow-right" class="h-4 w-4 rotate-90" /></button>
                                    <button type="button" class="{{ $ghost }}" @click="s.visible = !s.visible" :title="s.visible ? 'Sembunyikan' : 'Tampilkan'" :aria-label="(s.visible ? 'Sembunyikan ' : 'Tampilkan ') + s.label">
                                        <span x-show="s.visible"><x-admin.icon name="eye" class="h-5 w-5" /></span>
                                        <span x-show="!s.visible" x-cloak><x-admin.icon name="eye-slash" class="h-5 w-5" /></span>
                                    </button>
                                </div>
                            </li>
                        </template>
                    </ul>
                    @error('sections')<p class="text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                    @foreach ($errors->get('sections.*') as $msgs)<p class="text-sm text-[#E5352E]">{{ $msgs[0] }}</p>@endforeach
                </x-admin.form-section>

                @foreach ($groups as $section => [$title, $icon, $desc, $fields])
                    <x-admin.form-section :icon="$icon" :title="$title" :description="$desc">
                        @foreach ($fields as $key => [$lbl, $max, $area, $required])
                            <div>
                                <label class="{{ $label }}"><span>{{ $lbl }}</span> <span x-text="lang === 'id' ? '(ID)' : '(EN)'"></span>@if ($required)<span x-show="lang === 'id'" class="text-[#E5352E]"> *</span>@endif</label>
                                @foreach (['id', 'en'] as $l)
                                    <div x-show="lang === '{{ $l }}'" @if ($l === 'en') x-cloak @endif>
                                        @if ($area)
                                            <textarea rows="3" maxlength="{{ $max }}" name="{{ $section }}[{{ $key }}][{{ $l }}]" x-model="f.{{ $section }}.{{ $key }}.{{ $l }}" class="{{ $field }}"></textarea>
                                        @else
                                            <input maxlength="{{ $max }}" name="{{ $section }}[{{ $key }}][{{ $l }}]" x-model="f.{{ $section }}.{{ $key }}.{{ $l }}" class="{{ $field }}">
                                        @endif
                                        @error($section.'.'.$key.'.'.$l)<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                                    </div>
                                @endforeach
                                @if (! $required)<p class="mt-1.5 text-xs text-slate-500">Boleh dikosongkan; label tidak tampil bila kosong.</p>@endif
                            </div>
                        @endforeach

                        @if ($section === 'values')
                            <div class="space-y-4">
                                <template x-for="(it, i) in f.values.items" :key="i">
                                    <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-sm font-semibold text-slate-700" x-text="'Nilai ' + (i + 1)"></p>
                                            <div class="flex items-center">
                                                <button type="button" class="{{ $ghost }}" :disabled="i === 0" @click="move(f.values.items, i, -1)" aria-label="Naikkan"><x-admin.icon name="arrow-left" class="h-4 w-4 rotate-90" /></button>
                                                <button type="button" class="{{ $ghost }}" :disabled="i === f.values.items.length - 1" @click="move(f.values.items, i, 1)" aria-label="Turunkan"><x-admin.icon name="arrow-right" class="h-4 w-4 rotate-90" /></button>
                                                <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-[#E5352E]" @click="del(f.values.items, i)" aria-label="Hapus butir"><x-admin.icon name="trash" class="h-4 w-4" /></button>
                                            </div>
                                        </div>
                                        <div class="mt-2 space-y-3">
                                            <div>
                                                <label class="{{ $label }}" x-text="lang === 'id' ? 'Judul (ID)' : 'Title (EN)'"></label>
                                                <input maxlength="100" :name="'values[items][' + i + '][title][' + lang + ']'" x-model="it.title[lang]" class="{{ $field }}">
                                            </div>
                                            <div>
                                                <label class="{{ $label }}" x-text="lang === 'id' ? 'Teks (ID)' : 'Text (EN)'"></label>
                                                <textarea rows="2" maxlength="300" :name="'values[items][' + i + '][text][' + lang + ']'" x-model="it.text[lang]" class="{{ $field }}"></textarea>
                                            </div>
                                            <input type="hidden" :name="'values[items][' + i + '][title][' + (lang === 'id' ? 'en' : 'id') + ']'" :value="it.title[lang === 'id' ? 'en' : 'id']">
                                            <input type="hidden" :name="'values[items][' + i + '][text][' + (lang === 'id' ? 'en' : 'id') + ']'" :value="it.text[lang === 'id' ? 'en' : 'id']">
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <button type="button" x-show="f.values.items.length < {{ $maxValues }}" @click="addValue()" class="text-sm font-semibold text-[#1B3590] hover:underline">+ Tambah nilai</button>
                            @error('values.items')<p class="text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                            @foreach ($errors->get('values.items.*') as $msgs)<p class="text-sm text-[#E5352E]">{{ $msgs[0] }}</p>@endforeach
                        @endif
                    </x-admin.form-section>
                @endforeach

                {{-- Poin Keberlanjutan (daftar centang di section Keberlanjutan) --}}
                {{-- Foto (Media Library) --}}
                <x-admin.form-section icon="photo" title="Foto Beranda" description="Pilih dari Media Library atau unggah baru. Yang dikosongkan memakai foto bawaan situs (contoh).">
                    <div class="grid gap-6 sm:grid-cols-2">
                        @foreach ($slots as $slot => [$slotTitle, $slotHint, $slotRatio])
                            <x-admin.media-picker :name="'images['.$slot.']'" :value="$images[$slot] ?? ''" :label="$slotTitle" :hint="$slotHint" :ratio="$slotRatio" />
                        @endforeach
                    </div>
                </x-admin.form-section>

                <x-admin.form-section icon="heart" title="Keberlanjutan: Poin Centang" description="Daftar poin (1 sampai 6) di section Keberlanjutan. Nama komunitas, ringkasan, dan logo diatur di modul Keberlanjutan; foto kegiatan tetap di kode.">
                    <div class="space-y-3">
                        <template x-for="(p, i) in f.community.points" :key="i">
                            <div class="flex items-start gap-2">
                                <div class="flex-1">
                                    <input maxlength="120" :name="'community[points][' + i + '][' + lang + ']'" x-model="p[lang]" :placeholder="lang === 'id' ? 'Poin (ID)' : 'Point (EN)'" class="{{ $field }} !mt-0">
                                    <input type="hidden" :name="'community[points][' + i + '][' + (lang === 'id' ? 'en' : 'id') + ']'" :value="p[lang === 'id' ? 'en' : 'id']">
                                </div>
                                <button type="button" class="{{ $ghost }}" :disabled="i === 0" @click="move(f.community.points, i, -1)" aria-label="Naikkan"><x-admin.icon name="arrow-left" class="h-4 w-4 rotate-90" /></button>
                                <button type="button" class="{{ $ghost }}" :disabled="i === f.community.points.length - 1" @click="move(f.community.points, i, 1)" aria-label="Turunkan"><x-admin.icon name="arrow-right" class="h-4 w-4 rotate-90" /></button>
                                <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-[#E5352E]" @click="del(f.community.points, i)" aria-label="Hapus poin"><x-admin.icon name="trash" class="h-4 w-4" /></button>
                            </div>
                        </template>
                    </div>
                    <button type="button" x-show="f.community.points.length < {{ $maxPoints }}" @click="addPoint()" class="text-sm font-semibold text-[#1B3590] hover:underline">+ Tambah poin</button>
                    @error('community.points')<p class="text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                    @foreach ($errors->get('community.points.*') as $msgs)<p class="text-sm text-[#E5352E]">{{ $msgs[0] }}</p>@endforeach
                </x-admin.form-section>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:col-start-3 lg:row-span-3 lg:row-start-1">
                <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                    <div class="px-6 pb-5 pt-6">
                        <x-admin.badge :tone="$row ? 'success' : 'dummy'">{{ $row ? 'Isi dari admin' : 'Teks contoh' }}</x-admin.badge>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Section tampil</dt><dd class="font-medium text-slate-700" x-text="shown + ' dari ' + f.sections.length"></dd></div>
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Nilai perusahaan</dt><dd class="font-medium text-slate-700" x-text="f.values.items.length + ' butir'"></dd></div>
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Poin Keberlanjutan</dt><dd class="font-medium text-slate-700" x-text="f.community.points.length + ' poin'"></dd></div>
                            @if ($row)
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah oleh</dt><dd class="text-right font-medium text-slate-700">{{ $row->editor?->name ?? '-' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah</dt><dd class="font-medium text-slate-700">{{ $row->updated_at?->translatedFormat('j M Y, H:i') }}</dd></div>
                            @endif
                        </dl>
                        @unless ($row)
                            <p class="mt-4 text-xs leading-relaxed text-slate-500">Situs sekarang memakai teks contoh. Setelah disimpan, isi dari formulir ini yang tampil.</p>
                        @endunless
                        <p class="mt-4 rounded-xl bg-slate-50 px-3 py-2.5 text-xs leading-relaxed text-slate-500 ring-1 ring-inset ring-slate-200">Section yang disembunyikan hanya hilang dari Beranda; halaman terkait (Portofolio, Klien &amp; Mitra, dan lain-lain) tetap bisa dibuka.</p>
                    </div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <x-admin.button type="submit" class="w-full">Simpan Perubahan</x-admin.button>
                        <x-admin.button variant="secondary" class="w-full" icon="external" target="_blank" rel="noopener" :href="lroute('home', [], 'id')">Lihat Di Situs</x-admin.button>
                    </div>
                </section>

                @if ($row)
                    <button type="button" @click="$dispatch('open-modal', { name: 'reset', action: '{{ route('admin.home.reset') }}' })"
                            class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-[#E5352E] hover:bg-red-50">
                        <x-admin.icon name="trash" class="h-4 w-4" /> Kembalikan Ke Teks Contoh
                    </button>
                @endif
            </aside>
        </form>
    </div>

    @if ($row)
        <x-admin.confirm name="reset" title="Kembalikan ke Teks Contoh?" method="DELETE" button="Ya, Kembalikan"
                         message="Isi dan susunan section yang sudah Anda simpan dihapus, lalu Beranda memakai teks contoh dan urutan bawaan lagi. Tindakan ini tidak bisa dibatalkan." />
    @endif
@endsection
