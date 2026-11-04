@extends('layouts.admin')

@php
    $editing = (bool) $client;

    $currentCategory = old('client_category_id', $editing ? $client->client_category_id : $preset);
    $nameVal = old('name', $editing ? $client->name : '');
    $logoUrl = $editing ? $client->logoUrl() : null;

    $permission = (bool) old('permission_ok', $editing ? $client->permission_ok : false);
    $categoryLabels = $categories->mapWithKeys(fn ($c) => [$c->id => $c->label('id')])->all();
@endphp

@section('title', $editing ? 'Ubah Klien' : 'Tambah Logo Klien')

@section('content')
    <x-admin.page-header :title="$editing ? 'Ubah Klien' : 'Tambah Logo Klien'"
                         description="Yang wajib hanya logo dan kategori. Nama boleh dikosongkan; di situs logo ditampilkan tanpa teks."
                         :breadcrumbs="[['label' => 'Klien & Mitra', 'url' => route('admin.clients.index')], ['label' => $editing ? 'Ubah' : 'Tambah']]" />

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <form method="POST" novalidate enctype="multipart/form-data" class="contents"
              action="{{ $editing ? route('admin.clients.update', $client) : route('admin.clients.store') }}"
              @media-chosen.window="chosen($event.detail)"
              x-data="{
                  name: @js((string) $nameVal),
                  category: @js((string) $currentCategory),
                  labels: @js($categoryLabels),
                  logoUrl: @js($logoUrl),
                  pick(e) {
                      const f = e.target.files[0];
                      if (!f) return;
                      if (f.size > 5 * 1024 * 1024) { this.$dispatch('toast', { type: 'error', text: 'Logo terlalu besar (maksimal 5 MB).' }); e.target.value = ''; return; }
                      this.logoUrl = URL.createObjectURL(f);
                  },
                  chosen(d) { if (d.name !== 'logo_path') return; this.logoUrl = d.url; if (this.$refs.file) this.$refs.file.value = ''; },
              }">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="space-y-6 lg:col-span-2 lg:col-start-1 lg:row-start-1">

                <x-admin.form-section icon="photo" title="Logo" description="JPG, PNG, atau WebP, maksimal 5 MB. Ruang kosong di sekeliling logo dipangkas otomatis; latar transparan dipertahankan.">
                    <div class="grid gap-5 sm:grid-cols-[14rem_1fr] sm:items-center">
                        <div class="flex aspect-[3/2] items-center justify-center rounded-2xl bg-white p-5 ring-1 ring-slate-200">
                            <template x-if="logoUrl"><img :src="logoUrl" alt="Pratinjau logo" class="max-h-full max-w-full object-contain"></template>
                            <div x-show="!logoUrl" class="flex flex-col items-center gap-1 text-slate-300">
                                <x-admin.icon name="photo" class="h-8 w-8" />
                                <span class="text-xs">Belum ada logo</span>
                            </div>
                        </div>
                        <div>
                            <label for="logo" class="block text-sm font-medium text-slate-700">
                                {{ $editing ? 'Ganti logo' : 'Pilih logo' }}@unless ($editing)<span class="text-[#E5352E]" aria-hidden="true"> *</span>@endunless
                            </label>
                            <input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp" x-ref="file" @change="pick($event)"
                                   class="mt-1.5 block w-full text-sm text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-blue-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-[#1B3590] hover:file:bg-blue-100">
                            @error('logo')<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                            <div class="mt-3">
                                <x-admin.media-picker name="logo_path" label="" :compact="true" :logo="true" />
                            </div>
                            @if ($editing && ! $logoUrl)
                                <p class="mt-1.5 text-sm text-amber-700">Berkas logo lama tidak ditemukan di server. Unggah ulang logonya.</p>
                            @endif
                        </div>
                    </div>
                </x-admin.form-section>

                <x-admin.form-section icon="users" title="Informasi" description="Kategori menentukan kelompok di halaman Klien & Mitra.">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.select name="client_category_id" label="Kategori" required placeholder="Pilih kategori"
                                        x-model="category" :options="$categoryLabels" :value="$currentCategory" />
                        <x-admin.input name="name" label="Nama (opsional)" x-model="name" :value="$nameVal" autocomplete="off"
                                       hint="Dipakai sebagai teks alternatif logo. Satu nama untuk ID dan EN." />
                    </div>
                </x-admin.form-section>

                <x-admin.form-section icon="eye" title="Tampilan di Situs" description="Atur di mana logo ini muncul.">
                    <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                        <x-admin.toggle name="is_visible" label="Tampil di situs" description="Dimatikan = disembunyikan dari halaman Klien & Mitra dan beranda."
                                        :checked="$editing ? $client->is_visible : true" />
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                        <x-admin.toggle name="show_on_home" label="Tampil di beranda" description="Masuk ke strip logo di beranda. Bila tidak ada yang dicentang, beranda memakai logo terdepan."
                                        :checked="$editing ? $client->show_on_home : false" />
                    </div>
                </x-admin.form-section>

                <x-admin.form-section icon="shield" title="Izin Tampil (Internal)" description="Catatan untuk tim; tidak tampil di situs dan tidak menyembunyikan logo.">
                    <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                        <x-admin.toggle name="permission_ok" label="Izin penayangan logo sudah dikonfirmasi"
                                        description="Situs menyatakan logo tampil atas izin masing-masing pihak. Centang setelah izin dipastikan."
                                        :checked="$permission" />
                    </div>
                </x-admin.form-section>
            </div>

            {{-- Panel samping: pratinjau + aksi --}}
            <aside class="space-y-6 lg:sticky lg:top-24 lg:col-start-3 lg:row-span-2 lg:row-start-1">
                <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                    <div class="relative h-24 bg-gradient-to-br from-[#1B3590] via-[#2644B0] to-[#4A6CF0]">
                        <div class="pointer-events-none absolute inset-0 opacity-[0.12]" aria-hidden="true"
                             style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 20px 20px;"></div>
                    </div>
                    <div class="px-6 pb-6 text-center">
                        <span class="relative -mt-10 mx-auto flex h-20 w-28 items-center justify-center rounded-2xl bg-white p-3 shadow-xl ring-4 ring-white">
                            <template x-if="logoUrl"><img :src="logoUrl" alt="" class="max-h-full max-w-full object-contain"></template>
                            <x-admin.icon name="photo" class="h-7 w-7 text-slate-300" x-show="!logoUrl" />
                        </span>
                        <p class="mt-3 truncate text-base font-bold text-slate-900" x-text="name.trim() || 'Tanpa nama'"></p>
                        <p class="truncate text-sm text-slate-500" x-text="labels[category] || 'Pilih kategori'"></p>
                        @if ($editing)
                            <dl class="mt-5 space-y-3 border-t border-slate-100 pt-4 text-left text-sm">
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Status</dt>
                                    <dd><x-admin.badge :tone="$client->is_visible ? 'success' : 'neutral'">{{ $client->is_visible ? 'Tampil' : 'Tersembunyi' }}</x-admin.badge></dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Di beranda</dt>
                                    <dd class="font-medium text-slate-700">{{ $client->show_on_home ? 'Ya' : 'Tidak' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Dibuat</dt>
                                    <dd class="font-medium text-slate-700">{{ $client->created_at?->translatedFormat('j M Y') ?? '-' }}</dd></div>
                            </dl>
                        @endif
                    </div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <x-admin.button type="submit" class="w-full">{{ $editing ? 'Simpan Perubahan' : 'Simpan Logo' }}</x-admin.button>
                        <x-admin.button variant="ghost" class="w-full" :href="route('admin.clients.index', $editing ? ['kategori' => $client->client_category_id] : [])">Batal</x-admin.button>
                    </div>
                </section>

                @if ($editing)
                    <button type="button" @click="$dispatch('open-modal', { name: 'hapus', action: '{{ route('admin.clients.destroy', $client) }}' })"
                            class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-[#E5352E] hover:bg-red-50">
                        <x-admin.icon name="trash" class="h-4 w-4" /> Hapus Klien Ini
                    </button>
                @endif
            </aside>
        </form>
    </div>

    @if ($editing)
        <x-admin.confirm name="hapus" title="Hapus Klien?" method="DELETE" button="Ya, Hapus"
                         message="Logo ini dihapus dari daftar dan dari situs. Bila hanya ingin menyembunyikannya, matikan Tampil di situs." />
    @endif
@endsection
