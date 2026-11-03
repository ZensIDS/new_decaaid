@extends('layouts.admin')

@php
    $canDelete = ! $media->isBundled() && count($usages) === 0;
    $missing = ! $media->exists();
@endphp

@section('title', $media->name)

@section('content')
    <x-admin.page-header :title="$media->name" description="Detail gambar: ubah nama dan alt teks, salin alamat, dan lihat di mana gambar ini dipakai."
                         :breadcrumbs="[['label' => 'Media Library', 'url' => route('admin.media.index')], ['label' => 'Detail']]" />

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Pratinjau --}}
            <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5">
                <div class="flex min-h-[16rem] items-center justify-center bg-[linear-gradient(45deg,#f1f5f9_25%,transparent_25%,transparent_75%,#f1f5f9_75%),linear-gradient(45deg,#f1f5f9_25%,transparent_25%,transparent_75%,#f1f5f9_75%)] p-6 [background-position:0_0,10px_10px] [background-size:20px_20px]">
                    @if ($missing)
                        <p class="text-sm font-medium text-amber-700">Berkas fisik tidak ditemukan di server.</p>
                    @else
                        <img src="{{ $media->url() }}" alt="{{ $media->altText('id') ?: $media->name }}" class="max-h-[28rem] max-w-full object-contain">
                    @endif
                </div>
            </section>

            {{-- Nama dan alt --}}
            <form method="POST" action="{{ route('admin.media.update', $media) }}" novalidate>
                @csrf @method('PUT')
                <x-admin.form-section icon="pencil" title="Nama dan Alt Teks" description="Nama hanya untuk Media Library (nama berkas tidak berubah). Alt teks membantu pembaca layar dan SEO.">
                    <x-admin.input name="name" label="Nama" required :value="$media->name" />
                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-admin.input name="alt_id" label="Alt teks (ID)" :value="$media->alt['id'] ?? ''" hint="Deskripsi singkat isi gambar." />
                        <x-admin.input name="alt_en" label="Alt text (EN)" :value="$media->alt['en'] ?? ''" hint="Kosong = memakai teks Indonesia." />
                    </div>
                    <div><x-admin.button type="submit">Simpan</x-admin.button></div>
                </x-admin.form-section>
            </form>

            {{-- Pemakaian --}}
            <x-admin.card title="Dipakai Di" :description="count($usages) ? 'Gambar ini tidak bisa dihapus selama masih dipakai.' : 'Belum dipakai di mana pun.'">
                @if (count($usages))
                    <ul class="divide-y divide-slate-100 rounded-xl ring-1 ring-slate-200">
                        @foreach ($usages as $use)
                            <li class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                                <span class="min-w-0 truncate font-medium text-slate-800">{{ $use['label'] }}</span>
                                @if ($use['url'])
                                    <a href="{{ $use['url'] }}" class="shrink-0 text-xs font-semibold text-[#1B3590] hover:underline">Buka</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-slate-500">Pemindaian mencakup Berita, Proyek, Penghargaan, Klien, Brand, Halaman Kustom, dan isi halaman (Beranda, Tentang Kami, dst.).</p>
                @endif
            </x-admin.card>
        </div>

        <aside class="space-y-6 lg:sticky lg:top-24">
            <section class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5">
                <dl class="space-y-3 px-6 py-5 text-sm">
                    <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Sumber</dt>
                        <dd><x-admin.badge :tone="$media->isBundled() ? 'neutral' : 'brand'">{{ $media->isBundled() ? 'Bawaan situs' : 'Unggahan' }}</x-admin.badge></dd></div>
                    <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Folder</dt><dd class="font-medium text-slate-700">{{ $media->folder ?: '-' }}</dd></div>
                    <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Ukuran</dt>
                        <dd class="font-medium text-slate-700">{{ $media->width && $media->height ? $media->width . ' x ' . $media->height . ' px, ' : '' }}{{ $media->sizeLabel() }}</dd></div>
                    <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Jenis</dt><dd class="font-medium text-slate-700">{{ $media->mime ?: '-' }}</dd></div>
                    <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Ditambahkan</dt>
                        <dd class="text-right font-medium text-slate-700">{{ $media->created_at?->translatedFormat('j M Y') }}@if ($media->uploader)<br><span class="text-xs font-normal text-slate-500">oleh {{ $media->uploader->name }}</span>@endif</dd></div>
                </dl>
                <div class="border-t border-slate-100 px-6 py-4" x-data="{ copied: false }">
                    <p class="text-xs font-medium text-slate-500">Alamat gambar</p>
                    <p class="mt-1 break-all rounded-xl bg-slate-50 px-3 py-2 text-xs text-slate-700 ring-1 ring-inset ring-slate-200">{{ $media->url() }}</p>
                    <button type="button" class="mt-2 text-xs font-semibold text-[#1B3590] hover:underline"
                            @click="
                                const t = @js($media->url());
                                const ok = () => { $dispatch('toast', { type: 'success', text: 'Alamat gambar disalin.' }) };
                                const fail = () => { $dispatch('toast', { type: 'error', text: 'Gagal menyalin. Salin manual dari kotak di atas.' }) };
                                if (navigator.clipboard && window.isSecureContext) { navigator.clipboard.writeText(t).then(ok, fail) }
                                else { const a = document.createElement('textarea'); a.value = t; document.body.appendChild(a); a.select(); try { document.execCommand('copy') ? ok() : fail() } catch (e) { fail() } a.remove() }
                            ">Salin alamat</button>
                </div>
            </section>

            @if ($media->isBundled())
                <p class="rounded-2xl bg-slate-50 px-5 py-4 text-xs leading-relaxed text-slate-500 ring-1 ring-slate-200">Berkas ini bagian dari situs (<code>{{ $media->path }}</code>) dan tidak bisa dihapus dari Media Library.</p>
            @else
                <button type="button" @if (! $canDelete) disabled @endif
                        @click="$dispatch('open-modal', { name: 'hapus', action: '{{ route('admin.media.destroy', $media) }}' })"
                        class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-[#E5352E] hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent">
                    <x-admin.icon name="trash" class="h-4 w-4" /> Hapus Gambar
                </button>
                @unless ($canDelete)
                    <p class="text-center text-xs text-slate-500">Dipakai di {{ count($usages) }} tempat; lepas dulu agar bisa dihapus.</p>
                @endunless
            @endif
        </aside>
    </div>

    @if (! $media->isBundled())
        <x-admin.confirm name="hapus" title="Hapus Gambar?" method="DELETE" button="Ya, Hapus"
                         message="Berkas gambar dan thumbnail-nya dihapus permanen dari server. Gambar ini tidak sedang dipakai di mana pun." />
    @endif
@endsection
