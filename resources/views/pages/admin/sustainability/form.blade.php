@extends('layouts.admin')

@section('title', 'Keberlanjutan')

@php
    // Isi awal: dari old() bila ada galat validasi, selain itu dari data tersimpan / teks contoh.
    $pairs = fn ($v) => ['id' => (string) ($v['id'] ?? ''), 'en' => (string) ($v['en'] ?? '')];
    $items = fn ($list) => collect($list)->map(fn ($r) => ['title' => $pairs($r['title'] ?? []), 'text' => $pairs($r['text'] ?? [])])->values()->all();

    $state = [
        'about_title'    => $pairs(old('about_title', $data['about_title'])),
        'about'          => collect(old('about', $data['about']))->map($pairs)->values()->all(),
        'pillars_title'  => $pairs(old('pillars_title', $data['pillars_title'])),
        'pillars'        => $items(old('pillars', $data['pillars'])),
        'programs_title' => $pairs(old('programs_title', $data['programs_title'])),
        'programs'       => $items(old('programs', $data['programs'])),
        'join_title'     => $pairs(old('join_title', $data['join_title'])),
        'join_text'      => $pairs(old('join_text', $data['join_text'])),
    ];

    $enErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.en'));
    $idErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.id') || ! str_contains($k, '.'));
    $startLang = $enErrors && ! $idErrors ? 'en' : 'id';

    $field = 'mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]';
    $label = 'block text-sm font-medium text-slate-700';
    $ghost = 'rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800 disabled:opacity-30 disabled:hover:bg-transparent';
@endphp

@section('content')
    <x-admin.page-header title="Keberlanjutan" description="Isi halaman Keberlanjutan (Komunitas Peduli Digital): tentang, fokus, program, dan ajakan bergabung. Judul halaman dan label tombol tetap dari sistem."
                         :breadcrumbs="[['label' => 'Konten'], ['label' => 'Keberlanjutan']]" />

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
        <form method="POST" novalidate action="{{ route('admin.sustainability.update') }}" class="contents"
              x-data="{
                  lang: @js($startLang),
                  f: @js($state),
                  add(list, blank) { if (this.f[list].length < blank.max) this.f[list].push(JSON.parse(JSON.stringify(blank.row))); },
                  del(list, i) { this.f[list].splice(i, 1); },
                  move(list, i, d) { const a = this.f[list], j = i + d; if (j < 0 || j >= a.length) return; [a[i], a[j]] = [a[j], a[i]]; },
              }">
            @csrf @method('PUT')

            <div class="space-y-6 lg:col-span-2 lg:col-start-1 lg:row-start-1">

                {{-- Tab bahasa (berlaku untuk semua kartu di bawah) --}}
                <div class="flex items-center gap-1 rounded-xl bg-white p-1 shadow-sm ring-1 ring-slate-900/5" role="tablist" aria-label="Bahasa isi">
                    <button type="button" role="tab" :aria-selected="(lang === 'id').toString()" @click="lang = 'id'"
                            :class="lang === 'id' ? 'bg-blue-50 text-[#1B3590] shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                            class="flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition">Indonesia (ID)</button>
                    <button type="button" role="tab" :aria-selected="(lang === 'en').toString()" @click="lang = 'en'"
                            :class="lang === 'en' ? 'bg-blue-50 text-[#1B3590] shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                            class="flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition">English (EN)</button>
                </div>
                <p class="-mt-3 px-1 text-xs text-slate-500" x-show="lang === 'en'" x-cloak>Bahasa Inggris boleh dikosongkan; pengunjung EN akan melihat teks Indonesia untuk bagian yang kosong.</p>

                {{-- Tentang --}}
                <x-admin.form-section icon="book" title="Tentang Komunitas" description="Judul dan satu atau dua paragraf pengantar.">
                    @foreach (['id' => 'Judul', 'en' => 'Title'] as $l => $t)
                        <div x-show="lang === '{{ $l }}'" @if ($l === 'en') x-cloak @endif>
                            <label for="about_title_{{ $l }}" class="{{ $label }}">{{ $t }}@if ($l === 'id') <span class="text-[#E5352E]">*</span>@endif</label>
                            <input id="about_title_{{ $l }}" name="about_title[{{ $l }}]" x-model="f.about_title.{{ $l }}" maxlength="120" class="{{ $field }}">
                            @error('about_title.'.$l)<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                        </div>
                    @endforeach

                    <template x-for="(p, i) in f.about" :key="i">
                        <div>
                            <label class="{{ $label }}" x-text="'Paragraf ' + (i + 1) + (lang === 'id' ? ' (ID)' : ' (EN)')"></label>
                            <textarea rows="4" maxlength="700" :name="'about[' + i + '][' + lang + ']'" x-model="p[lang]" class="{{ $field }}"></textarea>
                            {{-- Bahasa yang tidak aktif tetap ikut terkirim --}}
                            <input type="hidden" :name="'about[' + i + '][' + (lang === 'id' ? 'en' : 'id') + ']'" :value="p[lang === 'id' ? 'en' : 'id']">
                            <button type="button" x-show="f.about.length > 1" @click="del('about', i)" class="mt-1 text-xs font-semibold text-[#E5352E] hover:underline">Hapus paragraf ini</button>
                        </div>
                    </template>
                    <button type="button" x-show="f.about.length < 2" @click="add('about', { max: 2, row: { id: '', en: '' } })" class="text-sm font-semibold text-[#1B3590] hover:underline">+ Tambah paragraf</button>
                    @error('about')<p class="text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                    @foreach ($errors->get('about.*') as $msgs)<p class="text-sm text-[#E5352E]">{{ $msgs[0] }}</p>@endforeach
                </x-admin.form-section>

                {{-- Fokus & Program (dua repeater, markup sama) --}}
                @foreach ([
                    'pillars'  => ['icon' => 'heart', 'title' => 'Fokus Kami', 'desc' => 'Butir fokus komunitas (maksimal 6). Tampil sebagai kartu.', 'noun' => 'fokus', 'max' => 6, 'heading' => 'pillars_title'],
                    'programs' => ['icon' => 'academic', 'title' => 'Program Kegiatan', 'desc' => 'Daftar program (maksimal 8). Tampil di bagian gelap halaman.', 'noun' => 'program', 'max' => 8, 'heading' => 'programs_title'],
                ] as $list => $c)
                    <x-admin.form-section :icon="$c['icon']" :title="$c['title']" :description="$c['desc']">
                        @foreach (['id' => 'Judul bagian', 'en' => 'Section title'] as $l => $t)
                            <div x-show="lang === '{{ $l }}'" @if ($l === 'en') x-cloak @endif>
                                <label for="{{ $c['heading'] }}_{{ $l }}" class="{{ $label }}">{{ $t }}@if ($l === 'id') <span class="text-[#E5352E]">*</span>@endif</label>
                                <input id="{{ $c['heading'] }}_{{ $l }}" name="{{ $c['heading'] }}[{{ $l }}]" x-model="f.{{ $c['heading'] }}.{{ $l }}" maxlength="120" class="{{ $field }}">
                                @error($c['heading'].'.'.$l)<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                            </div>
                        @endforeach

                        <div class="space-y-4">
                            <template x-for="(it, i) in f.{{ $list }}" :key="i">
                                <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-sm font-semibold text-slate-700" x-text="'{{ ucfirst($c['noun']) }} ' + (i + 1)"></p>
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

                        <button type="button" x-show="f.{{ $list }}.length < {{ $c['max'] }}"
                                @click="add('{{ $list }}', { max: {{ $c['max'] }}, row: { title: { id: '', en: '' }, text: { id: '', en: '' } } })"
                                class="text-sm font-semibold text-[#1B3590] hover:underline">+ Tambah {{ $c['noun'] }}</button>
                        @error($list)<p class="text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                        @foreach ($errors->get($list.'.*') as $msgs)<p class="text-sm text-[#E5352E]">{{ $msgs[0] }}</p>@endforeach
                    </x-admin.form-section>
                @endforeach

                {{-- Ajakan --}}
                <x-admin.form-section icon="mail" title="Ajakan Bergabung" description="Bagian penutup di bawah halaman. Tombol (Hubungi Komunitas, Lihat Kabar Komunitas) tetap dari sistem.">
                    @foreach (['id' => ['Judul', 'Teks'], 'en' => ['Title', 'Text']] as $l => [$t1, $t2])
                        <div x-show="lang === '{{ $l }}'" @if ($l === 'en') x-cloak @endif class="space-y-5">
                            <div>
                                <label for="join_title_{{ $l }}" class="{{ $label }}">{{ $t1 }}@if ($l === 'id') <span class="text-[#E5352E]">*</span>@endif</label>
                                <input id="join_title_{{ $l }}" name="join_title[{{ $l }}]" x-model="f.join_title.{{ $l }}" maxlength="120" class="{{ $field }}">
                                @error('join_title.'.$l)<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="join_text_{{ $l }}" class="{{ $label }}">{{ $t2 }}@if ($l === 'id') <span class="text-[#E5352E]">*</span>@endif</label>
                                <textarea id="join_text_{{ $l }}" rows="3" name="join_text[{{ $l }}]" x-model="f.join_text.{{ $l }}" maxlength="400" class="{{ $field }}"></textarea>
                                @error('join_text.'.$l)<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    @endforeach
                </x-admin.form-section>
            </div>

            {{-- Panel samping --}}
            <aside class="space-y-6 lg:sticky lg:top-24 lg:col-start-3 lg:row-span-3 lg:row-start-1">
                <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                    <div class="px-6 pb-5 pt-6">
                        <div class="flex items-center gap-2">
                            <x-admin.badge :tone="$row ? 'success' : 'dummy'">{{ $row ? 'Isi dari admin' : 'Teks contoh' }}</x-admin.badge>
                        </div>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Fokus</dt><dd class="font-medium text-slate-700" x-text="f.pillars.length + ' butir'"></dd></div>
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Program</dt><dd class="font-medium text-slate-700" x-text="f.programs.length + ' butir'"></dd></div>
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
                        <x-admin.button variant="secondary" class="w-full" icon="external" target="_blank" rel="noopener" :href="lroute('community', [], 'id')">Lihat Di Situs</x-admin.button>
                    </div>
                </section>

                @if ($row)
                    <button type="button" @click="$dispatch('open-modal', { name: 'reset', action: '{{ route('admin.sustainability.reset') }}' })"
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
