@extends('layouts.admin')

@php
    $editing = (bool) $project;
    $canDelete = auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_ADMIN);

    $val = fn (string $field, string $lang) => $editing ? ($project->{$field}[$lang] ?? '') : '';
    // Lingkup kerja disimpan sebagai daftar; di form ditulis satu butir per baris.
    $scopeVal = fn (string $lang) => $editing ? implode("\n", $project->scope[$lang] ?? []) : '';

    // B5: terjemahan otomatis (butuh penyedia di .env dan migration kolom translated_from).
    $autoAvailable = \App\Support\Translator::enabled();
    $transColumns  = \App\Models\Project::hasTranslatedFrom();

    $currentTrans = old('translation_status', $editing ? $project->translation_status : 'manual');
    $currentPillar = old('pillar_id', $editing ? $project->pillar_id : null);
    $currentBrand = old('brand_id', $editing ? $project->brand_id : null);
    $imageUrl = $editing && $project->image && file_exists(public_path($project->image)) ? asset($project->image) : null;

    // Buka tab EN otomatis bila galat hanya ada di kolom EN.
    $enErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.en'));
    $idErrors = collect($errors->keys())->contains(fn ($k) => str_ends_with($k, '.id'));
    $startLang = $enErrors && ! $idErrors ? 'en' : 'id';

    $slugHint = $editing
        ? 'Mengubah slug mengubah alamat proyek; tautan lama otomatis dialihkan (301) ke alamat baru.'
        : 'Kosongkan agar dibuat otomatis dari judul. Hanya huruf kecil, angka, dan tanda hubung.';

    $brandRows = $brands->map(fn ($b) => ['id' => $b->id, 'pillar_id' => $b->pillar_id, 'name' => $b->name])->values()->all();
    $pillarNames = $pillars->mapWithKeys(fn ($p) => [$p->id => $p->name])->all();
@endphp

@section('title', $editing ? 'Ubah Proyek' : 'Tambah Proyek')

@section('content')
    <x-admin.page-header :title="$editing ? 'Ubah Proyek' : 'Tambah Proyek'"
                         :description="$editing ? 'Perbarui isi, pilar, atau gambar proyek ini.' : 'Isi judul, klien, tahun, dan ceritakan singkat pekerjaannya. Bahasa Inggris boleh dikosongkan; pengunjung EN akan melihat teks Indonesia.'"
                         :breadcrumbs="[['label' => 'Proyek', 'url' => route('admin.projects.index')], ['label' => $editing ? 'Ubah' : 'Tambah']]" />

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <form method="POST" novalidate enctype="multipart/form-data" class="contents"
              action="{{ $editing ? route('admin.projects.update', $project) : route('admin.projects.store') }}"
              @media-chosen.window="chosen($event.detail)"
              x-data="{
                  lang: @js($startLang),
                  title: @js(old('title.id', $val('title', 'id'))),
                  client: @js(old('client', $editing ? $project->client : '')),
                  year: @js((string) old('year', $editing ? $project->year : now()->year)),
                  pillar: @js((string) $currentPillar),
                  brand: @js((string) $currentBrand),
                  brands: @js($brandRows),
                  pillarNames: @js($pillarNames),
                  visible: @js((bool) old('is_visible', $editing ? $project->is_visible : true)),
                  imageUrl: @js($imageUrl),
                  removeImage: false,
                  get brandOptions() { return this.brands.filter(b => String(b.pillar_id) === this.pillar); },
                  pickPillar() { if (!this.brandOptions.some(b => String(b.id) === this.brand)) this.brand = ''; },
                  pick(e) {
                      const f = e.target.files[0];
                      if (!f) return;
                      if (f.size > 5 * 1024 * 1024) { this.$dispatch('toast', { type: 'error', text: 'Gambar terlalu besar (maksimal 5 MB).' }); e.target.value = ''; return; }
                      this.imageUrl = URL.createObjectURL(f);
                      this.removeImage = false;
                  },
                  clearImage() { this.$refs.file.value = ''; this.imageUrl = null; this.removeImage = true; },
                  chosen(d) { if (d.name !== 'image_path') return; this.imageUrl = d.url; this.removeImage = false; if (this.$refs.file) this.$refs.file.value = ''; },
              }">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="space-y-6 lg:col-span-2 lg:col-start-1 lg:row-start-1">

                {{-- Isi proyek (dwibahasa) --}}
                <x-admin.form-section icon="briefcase" title="Isi Proyek" description="Teks yang tampil di kartu dan halaman detail proyek.">
                    <div class="flex items-center gap-1 rounded-xl bg-slate-100 p-1" role="tablist" aria-label="Bahasa isi">
                        <button type="button" role="tab" :aria-selected="(lang === 'id').toString()" @click="lang = 'id'"
                                :class="lang === 'id' ? 'bg-white text-[#1B3590] shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                                class="flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition">Indonesia (ID)</button>
                        <button type="button" role="tab" :aria-selected="(lang === 'en').toString()" @click="lang = 'en'"
                                :class="lang === 'en' ? 'bg-white text-[#1B3590] shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                                class="flex flex-1 items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition">
                            English (EN)
                        </button>
                    </div>

                    @if ($transColumns)
                        @if ($autoAvailable)
                            <x-admin.toggle name="auto_translate" label="Terjemahkan otomatis bahasa yang kosong"
                                            description="Saat disimpan, kolom ID atau EN yang kosong diisi hasil terjemahan mesin dan ditandai Otomatis (belum ditinjau). Isian yang sudah kamu tulis tidak ditimpa."
                                            :checked="true" />
                        @else
                            <p class="rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-inset ring-amber-200">
                                Terjemahan otomatis belum aktif (penyedia belum diatur di <code>.env</code>). Tab EN yang kosong akan tampil sebagai teks Indonesia.
                            </p>
                        @endif
                    @endif

                    <div x-show="lang === 'id'" class="space-y-5" role="tabpanel">
                        <x-admin.input name="title[id]" label="Judul proyek" required x-model="title" :value="$val('title', 'id')" />
                        <x-admin.textarea name="summary[id]" label="Ringkasan" required rows="2" :value="$val('summary', 'id')"
                                          hint="Satu atau dua kalimat untuk kartu proyek. Maksimal 300 karakter." />
                        <x-admin.textarea name="description[id]" label="Deskripsi" required rows="6" :value="$val('description', 'id')"
                                          hint="Cerita singkat proyek di halaman detail. Teks biasa, pisahkan paragraf dengan baris kosong tidak diperlukan." />
                        <x-admin.textarea name="scope[id]" label="Lingkup pekerjaan" rows="5" :value="$scopeVal('id')"
                                          hint="Satu butir per baris (maksimal 12). Tampil sebagai daftar centang di halaman detail." />
                    </div>

                    <div x-show="lang === 'en'" x-cloak class="space-y-5" role="tabpanel">
                        <x-admin.input name="title[en]" label="Title" :value="$val('title', 'en')" />
                        <x-admin.textarea name="summary[en]" label="Summary" rows="2" :value="$val('summary', 'en')" />
                        <x-admin.textarea name="description[en]" label="Description" rows="6" :value="$val('description', 'en')"
                                          hint="Boleh dikosongkan. Bila kosong, pengunjung EN melihat teks Indonesia." />
                        <x-admin.textarea name="scope[en]" label="Scope of work" rows="5" :value="$scopeVal('en')"
                                          hint="One item per line. Kosong = memakai daftar Indonesia." />
                    </div>
                </x-admin.form-section>

                {{-- Pilar, klien, tahun --}}
                <x-admin.form-section icon="cube" title="Pilar, Klien, dan Tahun" description="Satu proyek terhubung ke satu pilar bisnis; brand bersifat opsional.">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.select name="pillar_id" label="Pilar bisnis" required placeholder="Pilih pilar"
                                        :options="$pillarNames" :value="$currentPillar" x-model="pillar" @change="pickPillar()" />

                        <div>
                            <label for="brand_id" class="block text-sm font-medium text-slate-700">Brand (opsional)</label>
                            <select id="brand_id" name="brand_id" x-model="brand" :disabled="!pillar"
                                    class="mt-1.5 block w-full rounded-xl border-0 bg-slate-50 py-3 pl-4 pr-9 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590] disabled:opacity-60 @error('brand_id') ring-[#E5352E] @enderror">
                                <option value="">Tanpa brand</option>
                                <template x-for="b in brandOptions" :key="b.id">
                                    <option :value="String(b.id)" x-text="b.name" :selected="String(b.id) === brand"></option>
                                </template>
                            </select>
                            @error('brand_id')<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-[1fr_9rem]">
                        <x-admin.input name="client" label="Klien" required x-model="client" :value="$editing ? $project->client : null"
                                       hint="Nama instansi atau perusahaan. Tampil di kartu. Pastikan klien mengizinkan namanya dipajang." />
                        <x-admin.input name="year" label="Tahun" type="number" required min="1990" max="{{ now()->year + 1 }}" x-model="year" :value="$editing ? $project->year : now()->year" />
                    </div>
                </x-admin.form-section>

                {{-- Gambar utama --}}
                <x-admin.form-section icon="photo" title="Gambar Utama" description="Tampil di kartu dan bagian atas halaman detail. Rasio 16:10 paling pas. JPG, PNG, atau WebP, maksimal 5 MB; otomatis dikecilkan dan diubah ke WebP.">
                    <input type="hidden" name="remove_image" :value="removeImage ? 1 : 0">

                    <div class="grid gap-5 sm:grid-cols-[14rem_1fr]">
                        <div>
                            <div class="relative aspect-[16/10] overflow-hidden rounded-xl bg-slate-100 ring-1 ring-slate-200">
                                <template x-if="imageUrl"><img :src="imageUrl" alt="Pratinjau gambar utama" class="h-full w-full object-cover"></template>
                                <div x-show="!imageUrl" class="flex h-full flex-col items-center justify-center gap-1 text-slate-400">
                                    <x-admin.icon name="photo" class="h-7 w-7" />
                                    <span class="text-xs">Belum ada gambar</span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label for="image" class="block text-sm font-medium text-slate-700">Pilih gambar</label>
                                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" x-ref="file" @change="pick($event)"
                                       class="mt-1.5 block w-full text-sm text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-blue-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-[#1B3590] hover:file:bg-blue-100">
                                @error('image')<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                            </div>
                            <button type="button" x-show="imageUrl" @click="clearImage()" class="text-sm font-semibold text-[#E5352E] hover:underline">Hapus gambar</button>
                            <x-admin.media-picker name="image_path" label="" :compact="true" />
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.input name="image_alt[id]" label="Teks alternatif (ID)" :value="$val('image_alt', 'id')" hint="Deskripsi singkat gambar untuk pembaca layar dan SEO. Kosong = memakai judul." />
                        <x-admin.input name="image_alt[en]" label="Alt text (EN)" :value="$val('image_alt', 'en')" />
                    </div>
                </x-admin.form-section>

                {{-- Publikasi --}}
                <x-admin.form-section icon="eye" title="Tampilan di Situs" description="Atur apakah proyek tampil, dan apakah dipajang di beranda.">
                    <div class="space-y-4 rounded-xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                        <x-admin.toggle name="is_visible" label="Tampilkan di situs"
                                        description="Bila dimatikan, proyek disimpan tetapi tidak tampil di Portofolio maupun halaman pilar."
                                        :checked="$editing ? $project->is_visible : true" />
                        <x-admin.toggle name="is_featured" label="Proyek unggulan (beranda)"
                                        description="Beranda menampilkan 3 proyek unggulan terbaru; bila kurang dari 3, dilengkapi proyek terbaru."
                                        :checked="$editing ? $project->is_featured : false" />
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.select name="translation_status" label="Status terjemahan" required
                                        :options="\App\Models\Project::TRANSLATION" :value="$currentTrans" />
                        <x-admin.input name="slug" label="Slug (alamat proyek)" :value="$editing ? $project->slug : null" autocomplete="off" :hint="$slugHint" />
                    </div>
                </x-admin.form-section>

                {{-- SEO --}}
                <x-admin.form-section icon="search" title="SEO" description="Opsional. Bila kosong, situs memakai judul dan ringkasan proyek.">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="space-y-5">
                            <x-admin.input name="seo_title[id]" label="Judul SEO (ID)" :value="$val('seo_title', 'id')" hint="Maksimal 120 karakter." />
                            <x-admin.textarea name="seo_description[id]" label="Deskripsi SEO (ID)" rows="3" :value="$val('seo_description', 'id')" hint="Maksimal 300 karakter." />
                        </div>
                        <div class="space-y-5">
                            <x-admin.input name="seo_title[en]" label="SEO title (EN)" :value="$val('seo_title', 'en')" />
                            <x-admin.textarea name="seo_description[en]" label="SEO description (EN)" rows="3" :value="$val('seo_description', 'en')" />
                        </div>
                    </div>
                </x-admin.form-section>
            </div>

            {{-- Panel samping: pratinjau + aksi --}}
            <aside class="space-y-6 lg:sticky lg:top-24 lg:col-start-3 lg:row-span-3 lg:row-start-1">
                <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                    <div class="relative aspect-[16/10] bg-gradient-to-br from-[#1B3590] via-[#2644B0] to-[#4A6CF0]">
                        <template x-if="imageUrl"><img :src="imageUrl" alt="" class="h-full w-full object-cover"></template>
                        <div x-show="!imageUrl" class="pointer-events-none absolute inset-0 flex items-center justify-center text-white/50" aria-hidden="true">
                            <x-admin.icon name="briefcase" class="h-10 w-10" />
                        </div>
                    </div>
                    <div class="px-6 pb-6 pt-5">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                  :class="visible ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-700 ring-slate-200'"
                                  x-text="visible ? 'Tampil' : 'Disembunyikan'"></span>
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-[#1B3590] ring-1 ring-inset ring-blue-200"
                                  x-show="pillar" x-text="pillarNames[pillar]"></span>
                            @if ($editing && $project->is_dummy)<x-admin.badge tone="dummy">Dummy</x-admin.badge>@endif
                        </div>
                        <p class="mt-3 line-clamp-3 text-base font-bold leading-snug text-slate-900" x-text="title.trim() || 'Judul proyek'"></p>
                        <p class="mt-1 text-xs font-semibold text-slate-500" x-text="(client.trim() || 'Klien') + ' · ' + (year || '')"></p>

                        @if ($editing)
                            <dl class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-sm">
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Dibuat oleh</dt>
                                    <dd class="text-right font-medium text-slate-700">{{ $project->author?->name ?? '-' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Dibuat</dt>
                                    <dd class="font-medium text-slate-700">{{ $project->created_at?->translatedFormat('j M Y') ?? '-' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah</dt>
                                    <dd class="font-medium text-slate-700">{{ $project->updated_at?->translatedFormat('j M Y, H:i') ?? '-' }}</dd></div>
                            </dl>
                        @endif
                    </div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <x-admin.button type="submit" class="w-full">{{ $editing ? 'Simpan Perubahan' : 'Simpan Proyek' }}</x-admin.button>
                        @if ($editing && $project->is_visible)
                            <x-admin.button variant="secondary" class="w-full" icon="external" target="_blank" rel="noopener"
                                            :href="lroute('portfolio.show', ['slug' => $project->slug], 'id')">Lihat Di Situs</x-admin.button>
                        @endif
                        <x-admin.button variant="ghost" class="w-full" :href="route('admin.projects.index')">Batal</x-admin.button>
                    </div>
                </section>

                @if ($editing && $canDelete)
                    <button type="button" @click="$dispatch('open-modal', { name: 'hapus', action: '{{ route('admin.projects.destroy', $project) }}' })"
                            class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-[#E5352E] hover:bg-red-50">
                        <x-admin.icon name="trash" class="h-4 w-4" /> Hapus Proyek Ini
                    </button>
                @endif
            </aside>
        </form>
    </div>

    @if ($editing && $canDelete)
        <x-admin.confirm name="hapus" title="Hapus Proyek?" method="DELETE" button="Ya, Hapus"
                         message="Proyek dihapus permanen dari situs dan admin. Bila hanya ingin menyembunyikan, matikan saklar Tampilkan di situs." />
    @endif
@endsection