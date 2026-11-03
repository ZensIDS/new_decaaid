@extends('layouts.admin')

@section('title', 'Kebijakan Privasi')

@php
    $pair = fn ($v) => ['id' => (string) ($v['id'] ?? ''), 'en' => (string) ($v['en'] ?? '')];

    $state = [
        'lead'     => $pair(old('lead', $data['lead'])),
        'updated'  => (string) old('updated', $data['updated']),
        'sections' => collect(old('sections', $data['sections']))->map(fn ($r) => ['title' => $pair($r['title'] ?? []), 'text' => $pair($r['text'] ?? [])])->values()->all(),
    ];

    $enErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.en'));
    $idErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.id') || ! str_contains($k, '.'));
    $startLang = $enErrors && ! $idErrors ? 'en' : 'id';

    $field = 'mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]';
    $label = 'block text-sm font-medium text-slate-700';
    $ghost = 'rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800 disabled:opacity-30 disabled:hover:bg-transparent';
@endphp

@section('content')
    <x-admin.page-header title="Kebijakan Privasi" description="Isi halaman Kebijakan Privasi. Daftar isi dibuat otomatis dari judul bagian."
                         :breadcrumbs="[['label' => 'Konten'], ['label' => 'Halaman Legal'], ['label' => 'Kebijakan Privasi']]" />

    @unless ($available)
        <p class="mb-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-inset ring-amber-200">
            Tabel penyimpanan belum ada. Jalankan <code>php artisan migrate</code> dulu; sebelum itu perubahan belum bisa disimpan.
        </p>
    @endunless

    @unless ($row)
        <p class="mb-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-inset ring-amber-200">
            Teks di bawah masih <strong>draf contoh</strong> dan wajib ditinjau pihak hukum sebelum situs go-live.
        </p>
    @endunless

    @if ($errors->any())
        <p class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-inset ring-red-200">
            Ada isian yang belum benar. Periksa pesan di bawah, termasuk di tab bahasa lain.
        </p>
    @endif

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <form method="POST" novalidate action="{{ route('admin.pages.privacy.update') }}" class="contents"
              x-data="{
                  lang: @js($startLang),
                  f: @js($state),
                  add() { if (this.f.sections.length < {{ $max }}) this.f.sections.push({ title: { id: '', en: '' }, text: { id: '', en: '' } }); },
                  del(i) { this.f.sections.splice(i, 1); },
                  move(i, d) { const a = this.f.sections, j = i + d; if (j < 0 || j >= a.length) return; [a[i], a[j]] = [a[j], a[i]]; },
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

                <x-admin.form-section icon="document" title="Pengantar dan Tanggal" description="Kalimat di bawah judul halaman dan tanggal pembaruan terakhir.">
                    @foreach (['id' => 'Kalimat pengantar (ID)', 'en' => 'Introduction (EN)'] as $l => $t)
                        <div x-show="lang === '{{ $l }}'" @if ($l === 'en') x-cloak @endif>
                            <label for="lead_{{ $l }}" class="{{ $label }}">{{ $t }}@if ($l === 'id') <span class="text-[#E5352E]">*</span>@endif</label>
                            <textarea id="lead_{{ $l }}" rows="3" maxlength="400" name="lead[{{ $l }}]" x-model="f.lead.{{ $l }}" class="{{ $field }}"></textarea>
                            @error('lead.'.$l)<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                        </div>
                    @endforeach

                    <div class="max-w-xs">
                        <label for="updated" class="{{ $label }}">Terakhir diperbarui <span class="text-[#E5352E]">*</span></label>
                        <input id="updated" type="date" name="updated" x-model="f.updated" max="{{ now()->toDateString() }}" class="{{ $field }}">
                        <p class="mt-1.5 text-xs text-slate-500">Ubah tanggal ini setiap kali isi kebijakan berubah.</p>
                        @error('updated')<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                    </div>
                </x-admin.form-section>

                <x-admin.form-section icon="book" title="Bagian Kebijakan" description="Maksimal {{ $max }} bagian. Teks biasa: baris baru menjadi paragraf baru.">
                    <div class="space-y-4">
                        <template x-for="(s, i) in f.sections" :key="i">
                            <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-semibold text-slate-700" x-text="'Bagian ' + (i + 1)"></p>
                                    <div class="flex items-center">
                                        <button type="button" class="{{ $ghost }}" :disabled="i === 0" @click="move(i, -1)" aria-label="Naikkan"><x-admin.icon name="arrow-left" class="h-4 w-4 rotate-90" /></button>
                                        <button type="button" class="{{ $ghost }}" :disabled="i === f.sections.length - 1" @click="move(i, 1)" aria-label="Turunkan"><x-admin.icon name="arrow-right" class="h-4 w-4 rotate-90" /></button>
                                        <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-[#E5352E]" @click="del(i)" aria-label="Hapus bagian"><x-admin.icon name="trash" class="h-4 w-4" /></button>
                                    </div>
                                </div>
                                <div class="mt-2 space-y-3">
                                    <div>
                                        <label class="{{ $label }}" x-text="lang === 'id' ? 'Judul bagian (ID)' : 'Section title (EN)'"></label>
                                        <input maxlength="100" :name="'sections[' + i + '][title][' + lang + ']'" x-model="s.title[lang]" class="{{ $field }}">
                                    </div>
                                    <div>
                                        <label class="{{ $label }}" x-text="lang === 'id' ? 'Teks (ID)' : 'Text (EN)'"></label>
                                        <textarea rows="5" maxlength="4000" :name="'sections[' + i + '][text][' + lang + ']'" x-model="s.text[lang]" class="{{ $field }}"></textarea>
                                    </div>
                                    <input type="hidden" :name="'sections[' + i + '][title][' + (lang === 'id' ? 'en' : 'id') + ']'" :value="s.title[lang === 'id' ? 'en' : 'id']">
                                    <input type="hidden" :name="'sections[' + i + '][text][' + (lang === 'id' ? 'en' : 'id') + ']'" :value="s.text[lang === 'id' ? 'en' : 'id']">
                                </div>
                            </div>
                        </template>
                    </div>
                    <button type="button" x-show="f.sections.length < {{ $max }}" @click="add()" class="text-sm font-semibold text-[#1B3590] hover:underline">+ Tambah bagian</button>
                    @error('sections')<p class="text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                    @foreach ($errors->get('sections.*') as $msgs)<p class="text-sm text-[#E5352E]">{{ $msgs[0] }}</p>@endforeach
                </x-admin.form-section>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:col-start-3 lg:row-span-3 lg:row-start-1">
                <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                    <div class="px-6 pb-5 pt-6">
                        <x-admin.badge :tone="$row ? 'success' : 'dummy'">{{ $row ? 'Isi dari admin' : 'Draf contoh' }}</x-admin.badge>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Bagian</dt><dd class="font-medium text-slate-700" x-text="f.sections.length + ' bagian'"></dd></div>
                            @if ($row)
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah oleh</dt><dd class="text-right font-medium text-slate-700">{{ $row->editor?->name ?? '-' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah</dt><dd class="font-medium text-slate-700">{{ $row->updated_at?->translatedFormat('j M Y, H:i') }}</dd></div>
                            @endif
                        </dl>
                    </div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <x-admin.button type="submit" class="w-full">Simpan Perubahan</x-admin.button>
                        <x-admin.button variant="secondary" class="w-full" icon="external" target="_blank" rel="noopener" :href="lroute('privacy', [], 'id')">Lihat Di Situs</x-admin.button>
                    </div>
                </section>

                @if ($row)
                    <button type="button" @click="$dispatch('open-modal', { name: 'reset', action: '{{ route('admin.pages.privacy.reset') }}' })"
                            class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-[#E5352E] hover:bg-red-50">
                        <x-admin.icon name="trash" class="h-4 w-4" /> Kembalikan Ke Draf Contoh
                    </button>
                @endif
            </aside>
        </form>
    </div>

    @if ($row)
        <x-admin.confirm name="reset" title="Kembalikan ke Draf Contoh?" method="DELETE" button="Ya, Kembalikan"
                         message="Isi yang sudah Anda simpan dihapus dan situs memakai draf contoh bawaan lagi. Tindakan ini tidak bisa dibatalkan." />
    @endif
@endsection
