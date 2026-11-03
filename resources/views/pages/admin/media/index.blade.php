@extends('layouts.admin')

@section('title', 'Media Library')

@section('content')
    <x-admin.page-header title="Media Library"
                         description="Semua gambar situs di satu tempat: unggah, beri alt teks, dan lihat di mana sebuah gambar dipakai."
                         :breadcrumbs="[['label' => 'Situs'], ['label' => 'Media Library']]" />

    @if ($notReady)
        <x-admin.empty icon="photo" title="Media Library belum siap"
                       text="Jalankan php artisan migrate untuk membuat tabelnya, lalu php artisan media:sync untuk mendaftarkan gambar yang sudah ada." />
    @else
        <div class="mb-5 flex items-start gap-3 rounded-2xl bg-blue-50/70 px-5 py-4 text-sm text-slate-600 ring-1 ring-blue-100">
            <x-admin.icon name="photo" class="mt-0.5 h-5 w-5 shrink-0 text-[#1B3590]" />
            <p>
                <strong class="text-slate-800">{{ $total }} gambar</strong>@if ($noAlt > 0), <a href="{{ route('admin.media.index', ['alt' => 'kosong']) }}" class="font-semibold text-amber-700 hover:underline">{{ $noAlt }} belum punya alt teks</a>@endif.
                Gambar yang diunggah di modul lain (Berita, Proyek, dst.) otomatis masuk ke sini. Berkas bawaan situs (<code class="rounded bg-white px-1 py-0.5 text-xs">public/img</code>) tidak bisa dihapus dari sini.
            </p>
        </div>

        {{-- Unggah --}}
        <x-admin.card class="mb-5" title="Unggah Gambar" description="JPG, PNG, atau WebP, maksimal 5 MB per berkas. Otomatis dikecilkan (lebar maks. 1920 px) dan diubah ke WebP.">
            <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" novalidate x-data="{ count: 0 }" class="space-y-4">
                @csrf
                <div>
                    <label for="files" class="block text-sm font-medium text-slate-700">Berkas gambar</label>
                    <input id="files" name="files[]" type="file" multiple accept="image/jpeg,image/png,image/webp"
                           @change="count = $event.target.files.length"
                           class="mt-1.5 block w-full text-sm text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-blue-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-[#1B3590] hover:file:bg-blue-100">
                    @if ($errors->has('files') || $errors->has('files.*'))
                        <p class="mt-1.5 text-sm text-[#E5352E]">{{ $errors->first('files') ?: $errors->first('files.*') }}</p>
                    @else
                        <p class="mt-1.5 text-xs text-slate-500">Maksimal {{ $bulkMax }} berkas sekali unggah (batas PHP).</p>
                    @endif
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.input name="alt_id" label="Alt teks (ID), opsional" hint="Deskripsi singkat gambar; dipakai untuk semua berkas yang diunggah sekaligus." />
                    <x-admin.input name="alt_en" label="Alt text (EN), optional" />
                </div>
                <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200">
                    <x-admin.toggle name="is_logo" label="Ini logo" description="Memangkas ruang kosong di sekeliling logo dan menjaga latar transparan." :checked="false" />
                </div>
                <x-admin.button type="submit" icon="plus" x-bind:disabled="count === 0">
                    <span x-text="count ? 'Unggah ' + count + ' Gambar' : 'Unggah'">Unggah</span>
                </x-admin.button>
            </form>
        </x-admin.card>

        {{-- Cari dan filter --}}
        <form method="GET" action="{{ route('admin.media.index') }}" role="search"
              class="mb-6 grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-900/5 sm:grid-cols-2 lg:grid-cols-[1fr_11rem_11rem_11rem_auto] lg:items-end">
            <div>
                <label for="q" class="block text-sm font-medium text-slate-700">Cari</label>
                <input id="q" name="q" type="search" value="{{ $q }}" placeholder="Nama atau path berkas"
                       class="mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]">
            </div>
            <x-admin.select name="sumber" label="Sumber" placeholder="Semua sumber" :options="['upload' => 'Unggahan', 'bundled' => 'Bawaan situs']" :value="$source" />
            <x-admin.select name="folder" label="Folder" placeholder="Semua folder" :options="$folders->mapWithKeys(fn ($f) => [$f => $f])->all()" :value="$folder" />
            <x-admin.select name="alt" label="Alt teks" placeholder="Semua" :options="['kosong' => 'Belum diisi']" :value="$alt" />
            <div class="flex items-center gap-2">
                <x-admin.button type="submit" icon="search">Terapkan</x-admin.button>
                @if ($filtered)
                    <x-admin.button variant="ghost" :href="route('admin.media.index')">Atur Ulang</x-admin.button>
                @endif
            </div>
        </form>

        @if ($items->isEmpty())
            <x-admin.empty icon="photo" :title="$filtered ? 'Tidak ada gambar yang cocok' : 'Media Library masih kosong'"
                           :text="$filtered ? 'Coba ubah kata kunci atau atur ulang filter.' : 'Unggah gambar di atas, atau jalankan php artisan media:sync untuk mendaftarkan gambar yang sudah ada di situs.'" />
        @else
            <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6">
                @foreach ($items as $m)
                    <li>
                        <a href="{{ route('admin.media.show', $m) }}"
                           class="group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5 transition hover:-translate-y-0.5 hover:shadow-md hover:ring-[#1B3590]/30">
                            <span class="relative flex aspect-square items-center justify-center bg-[linear-gradient(45deg,#f1f5f9_25%,transparent_25%,transparent_75%,#f1f5f9_75%),linear-gradient(45deg,#f1f5f9_25%,transparent_25%,transparent_75%,#f1f5f9_75%)] [background-position:0_0,8px_8px] [background-size:16px_16px]">
                                <img src="{{ $m->thumbUrl() }}" alt="{{ $m->altText('id') ?: $m->name }}" loading="lazy" class="max-h-full max-w-full object-contain">
                                @unless ($m->hasAlt())
                                    <span class="absolute left-2 top-2 h-2.5 w-2.5 rounded-full bg-amber-400 ring-2 ring-white" title="Alt teks belum diisi" role="img" aria-label="Alt teks belum diisi"></span>
                                @endunless
                                @if ($m->isBundled())
                                    <span class="absolute right-2 top-2 rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-semibold text-slate-600 ring-1 ring-slate-200">Bawaan</span>
                                @endif
                            </span>
                            <span class="min-w-0 border-t border-slate-100 px-3 py-2">
                                <span class="block truncate text-xs font-semibold text-slate-800 group-hover:text-[#1B3590]" title="{{ $m->name }}">{{ $m->name }}</span>
                                <span class="block truncate text-[11px] text-slate-500">{{ $m->folder }} &middot; {{ $m->sizeLabel() }}</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <x-admin.pager :paginator="$items" />
        @endif
    @endif
@endsection
