@extends('layouts.admin')

@section('title', 'Tentang Kami')

@php
    // Keadaan awal form: isi lama setelah validasi gagal, atau data dari controller.
    $state = [
        'profile' => old('profile', $form['profile']),
        'vision'  => old('vision', $form['vision']),
        'journey' => old('journey', $form['journey']),
    ];

    $enErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.en'));
    $idErrors = collect($errors->keys())->contains(fn ($k) => ! str_ends_with($k, '.en'));
    $startLang = $enErrors && ! $idErrors ? 'en' : 'id';

    $field = 'mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]';
    $label = 'block text-sm font-medium text-slate-700';
    $ghost = 'rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800 disabled:opacity-30 disabled:hover:bg-transparent';

    /** "profile.chairman.name" + "id" => "profile[chairman][name][id]" */
    $nm = function (string $path, ?string $lang = null) {
        $parts = explode('.', $path);
        $out = array_shift($parts);
        foreach ($parts as $part) {
            $out .= '[' . $part . ']';
        }

        return $lang ? $out . '[' . $lang . ']' : $out;
    };

    // Kelompok kartu. 'f' = isian tunggal; 'list' = daftar yang bisa ditambah, dihapus, dan diurutkan.
    //  f   : [path, label, maks, textarea?, pasangan ID/EN? (false = teks biasa)]
    //  list: [path, noun, maks (kunci $max), jenis: text | titletext | milestone, label isian, maks karakter]
    $sections = [
        ['icon' => 'book', 'title' => 'Profil Perusahaan', 'desc' => 'Halaman Tentang Kami > Profil Perusahaan: kalimat pengantar di bagian atas dan bagian "Siapa kami".', 'items' => [
            ['f', 'profile.lead', 'Kalimat pengantar', 300, true, true],
            ['f', 'profile.story_title', 'Judul "Siapa kami"', 120, false, true],
            ['list', 'profile.story', 'paragraf', 'profile.story', 'text', 'Paragraf', 800],
        ]],
        ['icon' => 'user-circle', 'title' => 'Chairman Message', 'desc' => 'Pesan Chairman di halaman Profil Perusahaan. Foto dipilih dari Media Library; bila kosong, situs memakai berkas bawaan public/img/chairman.jpg.', 'items' => [
            ['f', 'profile.chairman.title', 'Judul bagian', 120, false, true],
            ['f', 'profile.chairman.name', 'Nama Chairman', 80, false, false],
            ['f', 'profile.chairman.role', 'Jabatan', 80, false, true],
            ['f', 'profile.chairman.headline', 'Kalimat utama', 200, true, true],
            ['list', 'profile.chairman.message', 'paragraf', 'chairman.message', 'text', 'Paragraf pesan', 1000],
        ]],
        ['icon' => 'eye', 'title' => 'Visi', 'desc' => 'Halaman Tentang Kami > Visi & Misi: pengantar dan pernyataan visi.', 'items' => [
            ['f', 'vision.lead', 'Kalimat pengantar', 300, true, true],
            ['f', 'vision.vision', 'Pernyataan visi', 400, true, true],
        ]],
        ['icon' => 'check-circle', 'title' => 'Misi', 'desc' => 'Butir misi (maksimal 8). Urutan di sini = urutan tampil.', 'items' => [
            ['list', 'vision.missions', 'misi', 'vision.missions', 'text', 'Butir misi', 300],
        ]],
        ['icon' => 'heart', 'title' => 'Nilai-Nilai', 'desc' => 'Judul bagian dan nilai perusahaan (maksimal 8, masing-masing judul + keterangan singkat).', 'items' => [
            ['f', 'vision.values_title', 'Judul bagian', 120, false, true],
            ['list', 'vision.values', 'nilai', 'vision.values', 'titletext', 'Nilai', 0],
        ]],
        ['icon' => 'clock', 'title' => 'Perjalanan Kami: Pengantar', 'desc' => 'Teks di bagian atas halaman Perjalanan Kami.', 'items' => [
            ['f', 'journey.lead', 'Kalimat pengantar', 300, true, true],
            ['f', 'journey.intro_title', 'Judul pengantar', 200, false, true],
            ['f', 'journey.intro_text', 'Teks pengantar', 300, true, true],
        ]],
        ['icon' => 'clock', 'title' => 'Perjalanan Kami: Lini Waktu', 'desc' => 'Peristiwa per tahun (maksimal 20). Tampil sesuai urutan di sini, kiri-kanan bergantian otomatis. Foto tiap peristiwa memakai berkas public/img/journey/{tahun}.jpg; bila belum ada tampil placeholder.', 'items' => [
            ['list', 'journey.milestones', 'peristiwa', 'journey.milestones', 'milestone', 'Peristiwa', 0],
        ]],
        ['icon' => 'check-circle', 'title' => 'Perjalanan Kami: Penutup', 'desc' => 'Teks penutup di bawah lini waktu.', 'items' => [
            ['f', 'journey.ahead_title', 'Judul penutup', 120, false, true],
            ['f', 'journey.ahead_text', 'Teks penutup', 400, true, true],
        ]],
    ];

    $blank = [
        'text'      => ['id' => '', 'en' => ''],
        'titletext' => ['title' => ['id' => '', 'en' => ''], 'text' => ['id' => '', 'en' => '']],
        'milestone' => ['year' => '', 'tag' => ['id' => '', 'en' => ''], 'title' => ['id' => '', 'en' => ''], 'text' => ['id' => '', 'en' => '']],
    ];
@endphp

@section('content')
    <x-admin.page-header title="Tentang Kami" description="Isi tiga halaman Tentang Kami: Profil Perusahaan (+ Chairman Message), Visi & Misi, dan Perjalanan Kami. Foto belum diatur di sini."
                         :breadcrumbs="[['label' => 'Konten'], ['label' => 'Tentang Kami']]" />

    @unless ($available)
        <p class="mb-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-inset ring-amber-200">
            Tabel penyimpanan belum ada. Jalankan <code>php artisan migrate</code> dulu; sebelum itu perubahan belum bisa disimpan.
        </p>
    @endunless

    @if ($errors->any())
        <div class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-inset ring-red-200" role="alert">
            <p class="font-semibold">Ada isian yang belum benar (cek juga tab bahasa lain):</p>
            <ul class="mt-1.5 list-disc space-y-0.5 pl-5">
                @foreach (array_slice($errors->all(), 0, 8) as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
            @if ($errors->count() > 8)
                <p class="mt-1.5 text-xs">dan {{ $errors->count() - 8 }} pesan lain.</p>
            @endif
        </div>
    @endif

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <form method="POST" novalidate action="{{ route('admin.about.update') }}" class="contents"
              x-data="{
                  lang: @js($startLang),
                  f: @js($state),
                  blank: @js($blank),
                  ref(path) { return path.split('.').reduce((o, k) => o[k], this.f); },
                  add(path, max, kind) { const a = this.ref(path); if (!max || a.length < max) a.push(JSON.parse(JSON.stringify(this.blank[kind]))); },
                  del(path, i) { this.ref(path).splice(i, 1); },
                  move(path, i, d) { const a = this.ref(path), j = i + d; if (j < 0 || j >= a.length) return; [a[i], a[j]] = [a[j], a[i]]; },
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
                        @foreach ($sec['items'] as $item)

                            {{-- Isian tunggal --}}
                            @if ($item[0] === 'f')
                                @php [, $path, $lbl, $maxLen, $area, $isPair] = $item; @endphp
                                <div>
                                    @if ($isPair)
                                        <label class="{{ $label }}"><span>{{ $lbl }}</span> <span x-text="lang === 'id' ? '(ID)' : '(EN)'"></span><span x-show="lang === 'id'" class="text-[#E5352E]"> *</span></label>
                                        @foreach (['id', 'en'] as $l)
                                            <div x-show="lang === '{{ $l }}'" @if ($l === 'en') x-cloak @endif>
                                                @if ($area)
                                                    <textarea rows="3" maxlength="{{ $maxLen }}" name="{{ $nm($path, $l) }}" x-model="f.{{ $path }}.{{ $l }}" class="{{ $field }}"></textarea>
                                                @else
                                                    <input maxlength="{{ $maxLen }}" name="{{ $nm($path, $l) }}" x-model="f.{{ $path }}.{{ $l }}" class="{{ $field }}">
                                                @endif
                                                @error($path.'.'.$l)<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                                            </div>
                                        @endforeach
                                    @else
                                        <label class="{{ $label }}" for="{{ str_replace('.', '_', $path) }}">{{ $lbl }}<span class="text-[#E5352E]"> *</span></label>
                                        <input id="{{ str_replace('.', '_', $path) }}" maxlength="{{ $maxLen }}" name="{{ $nm($path) }}" x-model="f.{{ $path }}" class="{{ $field }}">
                                        <p class="mt-1.5 text-xs text-slate-500">Satu teks untuk ID dan EN.</p>
                                        @error($path)<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                                    @endif
                                </div>

                            {{-- Daftar --}}
                            @else
                                @php [, $path, $noun, $maxKey, $kind, $lbl, $maxLen] = $item; $maxItems = $max[$maxKey]; @endphp
                                <div class="space-y-4">
                                    <template x-for="(it, i) in f.{{ $path }}" :key="i">
                                        <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="text-sm font-semibold text-slate-700" x-text="'{{ ucfirst($noun) }} ' + (i + 1)"></p>
                                                <div class="flex items-center">
                                                    <button type="button" class="{{ $ghost }}" :disabled="i === 0" @click="move('{{ $path }}', i, -1)" aria-label="Naikkan"><x-admin.icon name="arrow-left" class="h-4 w-4 rotate-90" /></button>
                                                    <button type="button" class="{{ $ghost }}" :disabled="i === f.{{ $path }}.length - 1" @click="move('{{ $path }}', i, 1)" aria-label="Turunkan"><x-admin.icon name="arrow-right" class="h-4 w-4 rotate-90" /></button>
                                                    <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-[#E5352E]" @click="del('{{ $path }}', i)" aria-label="Hapus butir"><x-admin.icon name="trash" class="h-4 w-4" /></button>
                                                </div>
                                            </div>

                                            <div class="mt-2 space-y-3">
                                                @if ($kind === 'text')
                                                    <div>
                                                        <label class="{{ $label }}" x-text="lang === 'id' ? '{{ $lbl }} (ID)' : '{{ $lbl }} (EN)'"></label>
                                                        <textarea rows="3" maxlength="{{ $maxLen }}" :name="'{{ $nm($path) }}[' + i + '][' + lang + ']'" x-model="it[lang]" class="{{ $field }}"></textarea>
                                                        <input type="hidden" :name="'{{ $nm($path) }}[' + i + '][' + (lang === 'id' ? 'en' : 'id') + ']'" :value="it[lang === 'id' ? 'en' : 'id']">
                                                    </div>
                                                @elseif ($kind === 'titletext')
                                                    <div>
                                                        <label class="{{ $label }}" x-text="lang === 'id' ? 'Judul (ID)' : 'Title (EN)'"></label>
                                                        <input maxlength="80" :name="'{{ $nm($path) }}[' + i + '][title][' + lang + ']'" x-model="it.title[lang]" class="{{ $field }}">
                                                    </div>
                                                    <div>
                                                        <label class="{{ $label }}" x-text="lang === 'id' ? 'Keterangan (ID)' : 'Text (EN)'"></label>
                                                        <textarea rows="2" maxlength="200" :name="'{{ $nm($path) }}[' + i + '][text][' + lang + ']'" x-model="it.text[lang]" class="{{ $field }}"></textarea>
                                                    </div>
                                                    <input type="hidden" :name="'{{ $nm($path) }}[' + i + '][title][' + (lang === 'id' ? 'en' : 'id') + ']'" :value="it.title[lang === 'id' ? 'en' : 'id']">
                                                    <input type="hidden" :name="'{{ $nm($path) }}[' + i + '][text][' + (lang === 'id' ? 'en' : 'id') + ']'" :value="it.text[lang === 'id' ? 'en' : 'id']">
                                                @else
                                                    <div class="grid gap-3 sm:grid-cols-3">
                                                        <div>
                                                            <label class="{{ $label }}">Tahun<span class="text-[#E5352E]"> *</span></label>
                                                            <input maxlength="20" placeholder="2019" :name="'{{ $nm($path) }}[' + i + '][year]'" x-model="it.year" class="{{ $field }}">
                                                        </div>
                                                        <div class="sm:col-span-2">
                                                            <label class="{{ $label }}" x-text="lang === 'id' ? 'Label pendek (ID)' : 'Short label (EN)'"></label>
                                                            <input maxlength="40" :name="'{{ $nm($path) }}[' + i + '][tag][' + lang + ']'" x-model="it.tag[lang]" class="{{ $field }}">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="{{ $label }}" x-text="lang === 'id' ? 'Judul peristiwa (ID)' : 'Event title (EN)'"></label>
                                                        <input maxlength="100" :name="'{{ $nm($path) }}[' + i + '][title][' + lang + ']'" x-model="it.title[lang]" class="{{ $field }}">
                                                    </div>
                                                    <div>
                                                        <label class="{{ $label }}" x-text="lang === 'id' ? 'Cerita (ID)' : 'Story (EN)'"></label>
                                                        <textarea rows="4" maxlength="700" :name="'{{ $nm($path) }}[' + i + '][text][' + lang + ']'" x-model="it.text[lang]" class="{{ $field }}"></textarea>
                                                    </div>
                                                    <input type="hidden" :name="'{{ $nm($path) }}[' + i + '][tag][' + (lang === 'id' ? 'en' : 'id') + ']'" :value="it.tag[lang === 'id' ? 'en' : 'id']">
                                                    <input type="hidden" :name="'{{ $nm($path) }}[' + i + '][title][' + (lang === 'id' ? 'en' : 'id') + ']'" :value="it.title[lang === 'id' ? 'en' : 'id']">
                                                    <input type="hidden" :name="'{{ $nm($path) }}[' + i + '][text][' + (lang === 'id' ? 'en' : 'id') + ']'" :value="it.text[lang === 'id' ? 'en' : 'id']">
                                                @endif
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <button type="button" x-show="f.{{ $path }}.length < {{ $maxItems }}" @click="add('{{ $path }}', {{ $maxItems }}, '{{ $kind }}')"
                                        class="text-sm font-semibold text-[#1B3590] hover:underline">+ Tambah {{ $noun }}</button>
                                @error($path)<p class="text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                            @endif
                        @endforeach

                        @if ($sec['title'] === 'Chairman Message')
                            <x-admin.media-picker name="profile[chairman][photo]" :value="$state['profile']['chairman']['photo'] ?? ''" label="Foto Chairman"
                                                  hint="Potret vertikal rasio 4:5 paling pas. Kosongkan untuk memakai foto bawaan." ratio="aspect-[4/5]" />
                        @endif
                    </x-admin.form-section>
                @endforeach
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:col-start-3 lg:row-span-3 lg:row-start-1">
                <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                    <div class="px-6 pb-5 pt-6">
                        <x-admin.badge :tone="$row ? 'success' : 'dummy'">{{ $row ? 'Isi dari admin' : 'Teks contoh' }}</x-admin.badge>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Paragraf "Siapa kami"</dt><dd class="font-medium text-slate-700" x-text="f.profile.story.length + ' paragraf'"></dd></div>
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Pesan Chairman</dt><dd class="font-medium text-slate-700" x-text="f.profile.chairman.message.length + ' paragraf'"></dd></div>
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Misi</dt><dd class="font-medium text-slate-700" x-text="f.vision.missions.length + ' butir'"></dd></div>
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Nilai</dt><dd class="font-medium text-slate-700" x-text="f.vision.values.length + ' nilai'"></dd></div>
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Peristiwa</dt><dd class="font-medium text-slate-700" x-text="f.journey.milestones.length + ' peristiwa'"></dd></div>
                            @if ($row)
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah oleh</dt><dd class="text-right font-medium text-slate-700">{{ $row->editor?->name ?? '-' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah</dt><dd class="font-medium text-slate-700">{{ $row->updated_at?->translatedFormat('j M Y, H:i') }}</dd></div>
                            @endif
                        </dl>
                        @unless ($row)
                            <p class="mt-4 text-xs leading-relaxed text-slate-500">Situs sekarang memakai teks contoh (termasuk nama Chairman dan Misi yang masih berupa draf). Setelah disimpan, isi dari formulir ini yang tampil.</p>
                        @endunless
                    </div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <x-admin.button type="submit" class="w-full">Simpan Perubahan</x-admin.button>
                        <x-admin.button variant="secondary" class="w-full" icon="external" target="_blank" rel="noopener" :href="lroute('about.profile', [], 'id')">Lihat Profil Di Situs</x-admin.button>
                        <x-admin.button variant="secondary" class="w-full" icon="external" target="_blank" rel="noopener" :href="lroute('about.vision', [], 'id')">Lihat Visi & Misi</x-admin.button>
                        <x-admin.button variant="secondary" class="w-full" icon="external" target="_blank" rel="noopener" :href="lroute('about.journey', [], 'id')">Lihat Perjalanan Kami</x-admin.button>
                    </div>
                </section>

                @if ($row)
                    <button type="button" @click="$dispatch('open-modal', { name: 'reset', action: '{{ route('admin.about.reset') }}' })"
                            class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-[#E5352E] hover:bg-red-50">
                        <x-admin.icon name="trash" class="h-4 w-4" /> Kembalikan Ke Teks Contoh
                    </button>
                @endif
            </aside>
        </form>
    </div>

    @if ($row)
        <x-admin.confirm name="reset" title="Kembalikan ke Teks Contoh?" method="DELETE" button="Ya, Kembalikan"
                         message="Isi Tentang Kami yang sudah Anda simpan dihapus dan situs memakai teks contoh bawaan lagi. Tindakan ini tidak bisa dibatalkan." />
    @endif
@endsection
