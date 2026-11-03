@extends('layouts.admin')

@section('title', 'Karir')

@php
    $pairs_ = $pairs;
    $pair = fn ($v) => ['id' => (string) ($v['id'] ?? ''), 'en' => (string) ($v['en'] ?? '')];
    $items = fn ($list) => collect($list)->map(fn ($r) => ['title' => $pair($r['title'] ?? []), 'text' => $pair($r['text'] ?? [])])->values()->all();

    $state = ['cv_email' => (string) old('cv_email', $data['cv_email'] ?? '')];
    foreach (array_keys($pairs_) as $k) {
        $state[$k] = $pair(old($k, $data[$k]));
    }
    foreach (array_keys($lists) as $l) {
        $state[$l] = $items(old($l, $data[$l]));
    }

    $enErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.en'));
    $idErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.id') || ! str_contains($k, '.'));
    $startLang = $enErrors && ! $idErrors ? 'en' : 'id';

    $field = 'mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]';
    $label = 'block text-sm font-medium text-slate-700';
    $ghost = 'rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800 disabled:opacity-30 disabled:hover:bg-transparent';

    $sections = [
        ['icon' => 'briefcase', 'title' => 'Judul dan Pengantar', 'desc' => 'Teks di bagian atas halaman dan catatan lamaran terbuka.', 'keys' => ['title', 'lead', 'open_note']],
        ['icon' => 'heart', 'title' => 'Mengapa Bergabung', 'desc' => 'Judul, teks pengantar, dan butir alasan (maksimal 6, tampil sebagai kartu).', 'keys' => ['why_title', 'why_text'], 'list' => 'why', 'noun' => 'alasan'],
        ['icon' => 'check', 'title' => 'Proses Seleksi', 'desc' => 'Judul, teks pengantar, dan langkah seleksi (maksimal 6, tampil bernomor).', 'keys' => ['process_title', 'process_text'], 'list' => 'process', 'noun' => 'langkah'],
        ['icon' => 'mail', 'title' => 'Kirim CV', 'desc' => 'Bagian gelap di bawah halaman, tempat pelamar memilih bidang lalu mengirim email.', 'keys' => ['cv_title', 'cv_text'], 'email' => true],
    ];
@endphp

@section('content')
    <x-admin.page-header title="Karir" description="Isi halaman Karir. Daftar bidang yang diminati diambil otomatis dari brand di Pilar & Brand, jadi tidak diatur di sini."
                         :breadcrumbs="[['label' => 'Konten'], ['label' => 'Karir']]" />

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
        <form method="POST" novalidate action="{{ route('admin.careers.update') }}" class="contents"
              x-data="{
                  lang: @js($startLang),
                  f: @js($state),
                  add(list, max) { if (this.f[list].length < max) this.f[list].push({ title: { id: '', en: '' }, text: { id: '', en: '' } }); },
                  del(list, i) { this.f[list].splice(i, 1); },
                  move(list, i, d) { const a = this.f[list], j = i + d; if (j < 0 || j >= a.length) return; [a[i], a[j]] = [a[j], a[i]]; },
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

                @foreach ($sections as $sec)
                    <x-admin.form-section :icon="$sec['icon']" :title="$sec['title']" :description="$sec['desc']">
                        @foreach ($sec['keys'] as $key)
                            @php [$lbl, $max, $area] = $pairs[$key]; @endphp
                            <div>
                                <label class="{{ $label }}"><span>{{ $lbl }}</span> <span x-text="lang === 'id' ? '(ID)' : '(EN)'"></span><span x-show="lang === 'id'" class="text-[#E5352E]"> *</span></label>
                                @foreach (['id', 'en'] as $l)
                                    <div x-show="lang === '{{ $l }}'" @if ($l === 'en') x-cloak @endif>
                                        @if ($area)
                                            <textarea rows="3" maxlength="{{ $max }}" name="{{ $key }}[{{ $l }}]" x-model="f.{{ $key }}.{{ $l }}" class="{{ $field }}"></textarea>
                                        @else
                                            <input maxlength="{{ $max }}" name="{{ $key }}[{{ $l }}]" x-model="f.{{ $key }}.{{ $l }}" class="{{ $field }}">
                                        @endif
                                        @error($key.'.'.$l)<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                        @if (! empty($sec['list']))
                            @php $list = $sec['list']; $maxItems = $lists[$list]; @endphp
                            <div class="space-y-4">
                                <template x-for="(it, i) in f.{{ $list }}" :key="i">
                                    <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-sm font-semibold text-slate-700" x-text="'{{ ucfirst($sec['noun']) }} ' + (i + 1)"></p>
                                            <div class="flex items-center">
                                                <button type="button" class="{{ $ghost }}" :disabled="i === 0" @click="move('{{ $list }}', i, -1)" aria-label="Naikkan"><x-admin.icon name="arrow-left" class="h-4 w-4 rotate-90" /></button>
                                                <button type="button" class="{{ $ghost }}" :disabled="i === f.{{ $list }}.length - 1" @click="move('{{ $list }}', i, 1)" aria-label="Turunkan"><x-admin.icon name="arrow-right" class="h-4 w-4 rotate-90" /></button>
                                                <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-[#E5352E]" @click="del('{{ $list }}', i)" aria-label="Hapus butir"><x-admin.icon name="trash" class="h-4 w-4" /></button>
                                            </div>
                                        </div>
                                        <div class="mt-2 space-y-3">
                                            <div>
                                                <label class="{{ $label }}" x-text="lang === 'id' ? 'Judul (ID)' : 'Title (EN)'"></label>
                                                <input maxlength="100" :name="'{{ $list }}[' + i + '][title][' + lang + ']'" x-model="it.title[lang]" class="{{ $field }}">
                                            </div>
                                            <div>
                                                <label class="{{ $label }}" x-text="lang === 'id' ? 'Teks (ID)' : 'Text (EN)'"></label>
                                                <textarea rows="2" maxlength="300" :name="'{{ $list }}[' + i + '][text][' + lang + ']'" x-model="it.text[lang]" class="{{ $field }}"></textarea>
                                            </div>
                                            <input type="hidden" :name="'{{ $list }}[' + i + '][title][' + (lang === 'id' ? 'en' : 'id') + ']'" :value="it.title[lang === 'id' ? 'en' : 'id']">
                                            <input type="hidden" :name="'{{ $list }}[' + i + '][text][' + (lang === 'id' ? 'en' : 'id') + ']'" :value="it.text[lang === 'id' ? 'en' : 'id']">
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <button type="button" x-show="f.{{ $list }}.length < {{ $maxItems }}" @click="add('{{ $list }}', {{ $maxItems }})"
                                    class="text-sm font-semibold text-[#1B3590] hover:underline">+ Tambah {{ $sec['noun'] }}</button>
                            @error($list)<p class="text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                            @foreach ($errors->get($list.'.*') as $msgs)<p class="text-sm text-[#E5352E]">{{ $msgs[0] }}</p>@endforeach
                        @endif

                        @if (! empty($sec['email']))
                            <div>
                                <label for="cv_email" class="{{ $label }}">Email tujuan CV (opsional)</label>
                                <input id="cv_email" type="email" name="cv_email" x-model="f.cv_email" maxlength="120" placeholder="{{ $defaultEmail }}" class="{{ $field }}">
                                <p class="mt-1.5 text-xs text-slate-500">Kosongkan untuk memakai alamat karir bawaan situs ({{ $defaultEmail }}). Pastikan kotak masuknya benar-benar ada.</p>
                                @error('cv_email')<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                            </div>
                        @endif
                    </x-admin.form-section>
                @endforeach
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:col-start-3 lg:row-span-3 lg:row-start-1">
                <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                    <div class="px-6 pb-5 pt-6">
                        <x-admin.badge :tone="$row ? 'success' : 'dummy'">{{ $row ? 'Isi dari admin' : 'Teks contoh' }}</x-admin.badge>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Alasan bergabung</dt><dd class="font-medium text-slate-700" x-text="f.why.length + ' butir'"></dd></div>
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Langkah seleksi</dt><dd class="font-medium text-slate-700" x-text="f.process.length + ' langkah'"></dd></div>
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Email CV</dt><dd class="truncate text-right font-medium text-slate-700" x-text="f.cv_email.trim() || '{{ $defaultEmail }}'"></dd></div>
                            @if ($row)
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah oleh</dt><dd class="text-right font-medium text-slate-700">{{ $row->editor?->name ?? '-' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah</dt><dd class="font-medium text-slate-700">{{ $row->updated_at?->translatedFormat('j M Y, H:i') }}</dd></div>
                            @endif
                        </dl>
                        @unless ($row)
                            <p class="mt-4 text-xs leading-relaxed text-slate-500">Situs sekarang memakai teks contoh. Setelah disimpan, isi dari formulir ini yang tampil.</p>
                        @endunless
                    </div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <x-admin.button type="submit" class="w-full">Simpan Perubahan</x-admin.button>
                        <x-admin.button variant="secondary" class="w-full" icon="external" target="_blank" rel="noopener" :href="lroute('careers', [], 'id')">Lihat Di Situs</x-admin.button>
                    </div>
                </section>

                @if ($row)
                    <button type="button" @click="$dispatch('open-modal', { name: 'reset', action: '{{ route('admin.careers.reset') }}' })"
                            class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-[#E5352E] hover:bg-red-50">
                        <x-admin.icon name="trash" class="h-4 w-4" /> Kembalikan Ke Teks Contoh
                    </button>
                @endif
            </aside>
        </form>
    </div>

    @if ($row)
        <x-admin.confirm name="reset" title="Kembalikan ke Teks Contoh?" method="DELETE" button="Ya, Kembalikan"
                         message="Isi yang sudah Anda simpan dihapus dan situs memakai teks contoh bawaan lagi. Tindakan ini tidak bisa dibatalkan." />
    @endif
@endsection
