@extends('layouts.admin')

@php
    $editing = (bool) $brand;
    $val = fn (string $field) => $editing ? ($brand->{$field} ?? '') : '';
    $descId = $editing ? ($brand->description['id'] ?? '') : '';
    $descEn = $editing ? ($brand->description['en'] ?? '') : '';
    $visible = $editing ? $brand->is_visible : true;
    $currentTrans = old('translation_status', $editing ? $brand->translation_status : 'manual');

    // Logo saat ini (logo bawaan di img/logo dipangkas otomatis seperti di situs).
    $logoOk = $editing && $brand->logo && file_exists(public_path($brand->logo));
    $logoUrl = $logoOk ? (str_starts_with($brand->logo, 'img/logo/') ? logo_url($brand->logo) : asset($brand->logo)) : null;

    // Buka tab EN otomatis bila galat hanya ada di kolom EN.
    $enErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.en'));
    $idErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.id'));
    $startLang = $enErrors && ! $idErrors ? 'en' : 'id';

    // Layanan / fokus (opsional). old() dipakai bila validasi gagal.
    $svc = old('services', $editing ? ($brand->services ?? []) : []);
    $svcItems = collect(data_get($svc, 'items', []))->map(fn ($i) => [
        'title' => ['id' => (string) data_get($i, 'title.id', ''), 'en' => (string) data_get($i, 'title.en', '')],
        'text'  => ['id' => (string) data_get($i, 'text.id', ''),  'en' => (string) data_get($i, 'text.en', '')],
    ])->values()->all();
    $svcErrors = collect($errors->keys())->filter(fn ($k) => str_starts_with($k, 'services'))
        ->flatMap(fn ($k) => $errors->get($k))->unique()->values();

    $cfg = [
        'lang'     => $startLang,
        'name'     => old('name', $val('name')),
        'pillarId' => (string) old('pillar_id', $editing ? $brand->pillar_id : $defaultPillarId),
        'pillars'  => $pillars->mapWithKeys(fn ($p) => [(string) $p->id => $p->name])->all(),
        'descEn'   => old('description.en', $descEn),
        'logoUrl'  => $logoUrl,
        'svcTitle' => ['id' => (string) data_get($svc, 'title.id', ''), 'en' => (string) data_get($svc, 'title.en', '')],
        'svcItems' => $svcItems,
    ];

    $slugHint = $editing
        ? 'Mengubah slug mengubah alamat halaman brand; tautan lama otomatis dialihkan (301) ke alamat baru.'
        : 'Kosongkan agar dibuat otomatis dari nama. Hanya huruf kecil, angka, dan tanda hubung.';
    $fieldClass = 'mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]';
@endphp

@section('title', $editing ? 'Ubah Brand' : 'Tambah Brand')

@section('content')
    <x-admin.page-header :title="$editing ? 'Ubah Brand' : 'Tambah Brand'"
                         :description="$editing ? 'Perbarui profil brand ini. Kolom yang dikosongkan tidak ditampilkan di situs.' : 'Hanya nama dan pilar yang wajib. Deskripsi, logo, dan tautan boleh menyusul; yang kosong tidak ditampilkan.'"
                         :breadcrumbs="[['label' => 'Konten'], ['label' => 'Pilar & Brand', 'url' => route('admin.pillars.index')], ['label' => $editing ? 'Ubah Brand' : 'Tambah Brand']]" />

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <form method="POST" novalidate enctype="multipart/form-data" class="contents" @media-chosen.window="chosen($event.detail)"
              action="{{ $editing ? route('admin.pillars.brands.update', $brand) : route('admin.pillars.brands.store') }}"
              x-data="brandForm(@js($cfg))">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="space-y-6 lg:col-span-2 lg:col-start-1 lg:row-start-1">

                {{-- Informasi dasar --}}
                <x-admin.form-section icon="cube" title="Informasi Brand" description="Nama, pilar induk, dan alamat halaman brand.">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.input name="name" label="Nama brand" required x-model="name" :value="$val('name')" />
                        <x-admin.select name="pillar_id" label="Pilar" required x-model="pillarId"
                                        :options="$pillars->mapWithKeys(fn ($p) => [$p->id => $p->name])->all()"
                                        :value="$editing ? $brand->pillar_id : $defaultPillarId"
                                        hint="Brand bisa dipindah ke pilar lain kapan saja." />
                    </div>
                    <x-admin.input name="slug" label="Slug (alamat halaman)" :value="$val('slug')" autocomplete="off" :hint="$slugHint" />
                </x-admin.form-section>

                {{-- Logo --}}
                <x-admin.form-section icon="photo" title="Logo" description="JPG, PNG, atau WebP, maksimal 5 MB. Ruang kosong di sekeliling logo dipangkas otomatis, lalu diubah ke WebP.">
                    <input type="hidden" name="remove_logo" :value="removeLogo ? 1 : 0">

                    <div class="grid gap-5 sm:grid-cols-[14rem_1fr]">
                        <div class="flex aspect-[16/10] items-center justify-center overflow-hidden rounded-xl bg-white p-4 ring-1 ring-slate-200">
                            <template x-if="logoUrl"><img :src="logoUrl" alt="Pratinjau logo" class="max-h-full max-w-full object-contain"></template>
                            <div x-show="!logoUrl" class="flex flex-col items-center gap-1 text-slate-400">
                                <x-admin.icon name="photo" class="h-7 w-7" />
                                <span class="text-xs">Belum ada logo</span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label for="logo" class="block text-sm font-medium text-slate-700">Pilih logo</label>
                                <input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp" x-ref="file" @change="pick($event)"
                                       class="mt-1.5 block w-full text-sm text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-blue-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-[#1B3590] hover:file:bg-blue-100">
                                @error('logo')<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                            </div>
                            <x-admin.media-picker name="logo_path" label="" :compact="true" :logo="true" />
                            <button type="button" x-show="logoUrl" @click="clearLogo()" class="text-sm font-semibold text-[#E5352E] hover:underline">Hapus logo</button>
                            <p class="text-xs text-slate-500">Pratinjau di sini belum dipangkas; hasil akhir tampil rapi di situs.</p>
                        </div>
                    </div>
                </x-admin.form-section>

                {{-- Deskripsi (dwibahasa) --}}
                <x-admin.form-section icon="book" title="Deskripsi" description="Tampil di halaman profil brand dan kartu brand. Tab EN boleh dikosongkan; pengunjung EN akan melihat teks Indonesia.">
                    <div class="flex items-center gap-1 rounded-xl bg-slate-100 p-1" role="tablist" aria-label="Bahasa deskripsi">
                        <button type="button" role="tab" :aria-selected="(lang === 'id').toString()" @click="lang = 'id'"
                                :class="lang === 'id' ? 'bg-white text-[#1B3590] shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                                class="flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition">Indonesia (ID)</button>
                        <button type="button" role="tab" :aria-selected="(lang === 'en').toString()" @click="lang = 'en'"
                                :class="lang === 'en' ? 'bg-white text-[#1B3590] shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                                class="flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition">
                            English (EN)
                            <span class="h-2 w-2 rounded-full" :class="descEn.trim() ? 'bg-emerald-500' : 'bg-slate-300'" aria-hidden="true"></span>
                        </button>
                    </div>

                    <div x-show="lang === 'id'" class="space-y-5" role="tabpanel">
                        <x-admin.textarea name="description[id]" label="Deskripsi" rows="7" :value="$descId" hint="Maksimal 4.000 karakter. Kosongkan bila belum ada bahannya; jangan diisi karangan." />
                    </div>
                    <div x-show="lang === 'en'" x-cloak class="space-y-5" role="tabpanel">
                        <x-admin.textarea name="description[en]" label="Description" rows="7" x-model="descEn" :value="$descEn" hint="Kosongkan bila belum diterjemahkan." />
                    </div>

                    <x-admin.select name="translation_status" label="Status terjemahan" required :options="\App\Models\Brand::TRANSLATION" :value="$currentTrans"
                                    hint="Tandai Ditinjau setelah teks Inggris diperiksa orang." />
                </x-admin.form-section>

                {{-- Kanal dan tautan --}}
                <x-admin.form-section icon="link" title="Kanal & Tautan" description="Semua boleh kosong. Kanal yang kosong tidak ditampilkan.">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.input name="instagram" label="Instagram" :value="$val('instagram')" placeholder="decaa.id" autocomplete="off"
                                       hint="Nama akun atau alamat lengkap (https://...)." />
                        <x-admin.input name="tiktok" label="TikTok" :value="$val('tiktok')" placeholder="decaa.id" autocomplete="off"
                                       hint="Nama akun atau alamat lengkap (https://...)." />
                    </div>
                    <x-admin.input name="url" label="Tautan situs brand (tombol Selengkapnya)" :value="$val('url')" placeholder="https://www.contoh.decaa.id" autocomplete="off"
                                   hint="Alamat yang diawali http:// otomatis diubah jadi https://." />
                </x-admin.form-section>

                {{-- Layanan / fokus utama (opsional) --}}
                <x-admin.form-section icon="document" title="Fokus & Layanan Utama" description="Opsional. Daftar butir yang tampil di profil brand, bila ada. Kosongkan semua butir untuk menyembunyikan bagian ini.">
                    @if ($svcErrors->isNotEmpty())
                        <div class="rounded-xl bg-red-50 px-4 py-3 text-sm text-[#c92a24] ring-1 ring-red-200" role="alert">
                            @foreach ($svcErrors as $message)<p>{{ $message }}</p>@endforeach
                        </div>
                    @endif

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="svc-title-id" class="block text-sm font-medium text-slate-700">Judul bagian (ID)</label>
                            <input id="svc-title-id" type="text" name="services[title][id]" x-model="svcTitle.id" maxlength="120" placeholder="Fokus dan Layanan Utama" class="{{ $fieldClass }}">
                        </div>
                        <div>
                            <label for="svc-title-en" class="block text-sm font-medium text-slate-700">Section title (EN)</label>
                            <input id="svc-title-en" type="text" name="services[title][en]" x-model="svcTitle.en" maxlength="120" placeholder="Main Focus and Services" class="{{ $fieldClass }}">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(it, i) in items" :key="it.key">
                            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-bold text-slate-800" x-text="'Butir ' + (i + 1)"></p>
                                    <div class="flex items-center gap-0.5">
                                        <button type="button" @click="moveItem(i, -1)" :disabled="i === 0" :aria-label="'Naikkan butir ' + (i + 1)"
                                                class="rounded-lg p-2 text-slate-400 hover:bg-white hover:text-slate-700 disabled:opacity-30">
                                            <x-admin.icon name="chevron-down" class="h-4 w-4 rotate-180" />
                                        </button>
                                        <button type="button" @click="moveItem(i, 1)" :disabled="i === items.length - 1" :aria-label="'Turunkan butir ' + (i + 1)"
                                                class="rounded-lg p-2 text-slate-400 hover:bg-white hover:text-slate-700 disabled:opacity-30">
                                            <x-admin.icon name="chevron-down" class="h-4 w-4" />
                                        </button>
                                        <button type="button" @click="removeItem(i)" :aria-label="'Hapus butir ' + (i + 1)"
                                                class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-[#E5352E]">
                                            <x-admin.icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600">Judul (ID) *</label>
                                        <input type="text" :name="'services[items][' + i + '][title][id]'" x-model="it.title.id" maxlength="150" class="{{ $fieldClass }}">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600">Title (EN)</label>
                                        <input type="text" :name="'services[items][' + i + '][title][en]'" x-model="it.title.en" maxlength="150" class="{{ $fieldClass }}">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600">Uraian (ID)</label>
                                        <textarea rows="4" :name="'services[items][' + i + '][text][id]'" x-model="it.text.id" maxlength="1500" class="{{ $fieldClass }}"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600">Description (EN)</label>
                                        <textarea rows="4" :name="'services[items][' + i + '][text][en]'" x-model="it.text.en" maxlength="1500" class="{{ $fieldClass }}"></textarea>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <p x-show="items.length === 0" class="rounded-xl border border-dashed border-slate-300 px-4 py-5 text-center text-sm text-slate-500">
                            Belum ada butir. Bagian ini tidak tampil di situs.
                        </p>

                        <button type="button" x-show="items.length < 12" @click="addItem()"
                                class="inline-flex items-center gap-2 rounded-xl bg-blue-50 px-4 py-2.5 text-sm font-semibold capitalize text-[#1B3590] hover:bg-blue-100">
                            <x-admin.icon name="plus" class="h-4 w-4" /> Tambah Butir
                        </button>
                    </div>
                </x-admin.form-section>

                {{-- SEO (B3n 2c) --}}
                <x-admin.form-section icon="search" title="SEO" description="Opsional. Bila kosong, situs memakai judul dan deskripsi bawaan halaman. Akhiran | {{ config('site.name') }} ditambahkan otomatis pada judul.">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="space-y-5">
                            <x-admin.input name="seo_title[id]" label="Judul SEO (ID)" :value="$brand ? ($brand->seo_title['id'] ?? '') : ''" hint="Ideal sampai 60 karakter." />
                            <x-admin.textarea name="seo_description[id]" label="Deskripsi SEO (ID)" rows="3" :value="$brand ? ($brand->seo_description['id'] ?? '') : ''" hint="Ideal sampai 160 karakter." />
                        </div>
                        <div class="space-y-5">
                            <x-admin.input name="seo_title[en]" label="SEO title (EN)" :value="$brand ? ($brand->seo_title['en'] ?? '') : ''" hint="Kosong = memakai teks Indonesia." />
                            <x-admin.textarea name="seo_description[en]" label="SEO description (EN)" rows="3" :value="$brand ? ($brand->seo_description['en'] ?? '') : ''" />
                        </div>
                    </div>
                </x-admin.form-section>

                {{-- Tampilan --}}
                <x-admin.form-section icon="eye" title="Tampilan di Situs" description="Brand yang disembunyikan hilang dari menu, pencarian, dan halaman pilar.">
                    <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
                        <x-admin.toggle name="is_visible" label="Tampilkan di situs" description="Matikan untuk menyembunyikan tanpa menghapus." :checked="$visible" />
                    </div>
                </x-admin.form-section>
            </div>

            {{-- Panel samping: pratinjau + aksi --}}
            <aside class="space-y-6 lg:sticky lg:top-24 lg:col-start-3 lg:row-start-1">
                <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                    <div class="relative h-24 bg-gradient-to-br from-[#1B3590] via-[#2644B0] to-[#4A6CF0]">
                        <div class="pointer-events-none absolute inset-0 opacity-[0.12]" aria-hidden="true"
                             style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 20px 20px;"></div>
                    </div>
                    <div class="px-6 pb-6 text-center">
                        <span class="relative -mt-10 mx-auto flex h-20 w-28 items-center justify-center overflow-hidden rounded-2xl bg-white p-3 shadow-xl ring-4 ring-white">
                            <template x-if="logoUrl"><img :src="logoUrl" alt="" class="max-h-full max-w-full object-contain"></template>
                            <span x-show="!logoUrl" class="text-2xl font-bold text-slate-300" aria-hidden="true" x-text="(name.trim().charAt(0) || '?').toUpperCase()"></span>
                        </span>
                        <p class="mt-3 truncate text-base font-bold text-slate-900" x-text="name.trim() || 'Nama brand'"></p>
                        <p class="truncate text-sm text-slate-500" x-text="pillars[pillarId] || 'Pilih pilar'"></p>
                        @if ($editing && $brand->is_dummy)
                            <div class="mt-3 flex justify-center"><x-admin.badge tone="dummy">Dummy</x-admin.badge></div>
                        @endif

                        @if ($editing)
                            <dl class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-left text-sm">
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Dibuat oleh</dt>
                                    <dd class="text-right font-medium text-slate-700">{{ $brand->author?->name ?? '-' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Dibuat</dt>
                                    <dd class="font-medium text-slate-700">{{ $brand->created_at?->translatedFormat('j M Y') ?? '-' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah</dt>
                                    <dd class="font-medium text-slate-700">{{ $brand->updated_at?->translatedFormat('j M Y, H:i') ?? '-' }}</dd></div>
                            </dl>
                            @if ($brand->is_dummy)
                                <p class="mt-4 rounded-xl bg-fuchsia-50 px-3 py-2 text-left text-xs text-fuchsia-800 ring-1 ring-fuchsia-200">
                                    Isi brand ini masih contoh. Menyimpan perubahan menghapus penanda Dummy.
                                </p>
                            @endif
                        @endif
                    </div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <x-admin.button type="submit" class="w-full">{{ $editing ? 'Simpan Perubahan' : 'Buat Brand' }}</x-admin.button>
                        <x-admin.button variant="ghost" class="w-full" :href="route('admin.pillars.index')">Batal</x-admin.button>
                    </div>
                </section>
            </aside>
        </form>
    </div>

    <script>
        function brandForm(cfg) {
            let seq = 0;
            const withKey = (it) => ({ key: ++seq, title: { id: it.title.id, en: it.title.en }, text: { id: it.text.id, en: it.text.en } });

            return {
                lang: cfg.lang,
                name: cfg.name,
                pillarId: cfg.pillarId,
                pillars: cfg.pillars,
                descEn: cfg.descEn,
                logoUrl: cfg.logoUrl,
                removeLogo: false,
                svcTitle: cfg.svcTitle,
                items: cfg.svcItems.map(withKey),

                pick(e) {
                    const f = e.target.files[0];
                    if (!f) return;
                    if (f.size > 5 * 1024 * 1024) {
                        this.$dispatch('toast', { type: 'error', text: 'Logo terlalu besar (maksimal 5 MB).' });
                        e.target.value = '';
                        return;
                    }
                    this.logoUrl = URL.createObjectURL(f);
                    this.removeLogo = false;
                },
                clearLogo() {
                    this.$refs.file.value = '';
                    this.logoUrl = null;
                    this.removeLogo = true;
                },
                // Dipilih dari Media Library (pemilih mode ringkas): ganti pratinjau, kosongkan berkas unggahan.
                chosen(d) {
                    if (d.name !== 'logo_path') return;
                    this.logoUrl = d.url;
                    this.removeLogo = false;
                    if (this.$refs.file) this.$refs.file.value = '';
                },

                addItem() {
                    this.items.push(withKey({ title: { id: '', en: '' }, text: { id: '', en: '' } }));
                },
                removeItem(i) {
                    this.items.splice(i, 1);
                },
                moveItem(i, dir) {
                    const j = i + dir;
                    if (j < 0 || j >= this.items.length) return;
                    const item = this.items.splice(i, 1)[0];
                    this.items.splice(j, 0, item);
                },
            };
        }
    </script>
@endsection