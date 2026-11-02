@extends('layouts.admin')

@section('title', 'Berita')

@php
    $canDelete = auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_ADMIN);
    $statusTone = ['draft' => 'neutral', 'published' => 'success', 'scheduled' => 'warning'];
    $categoryOptions = $categories->mapWithKeys(fn ($c) => [$c->id => $c->label('id')])->all();
@endphp

@section('content')
    <x-admin.page-header title="Berita" description="Tulis, jadwalkan, dan kelola berita yang tampil di situs."
                         :breadcrumbs="[['label' => 'Konten'], ['label' => 'Berita']]">
        <x-slot name="actions">
            <x-admin.button icon="plus" :href="route('admin.news.create')">Tambah Berita</x-admin.button>
        </x-slot>
    </x-admin.page-header>

    {{-- Cari dan filter --}}
    <form method="GET" action="{{ route('admin.news.index') }}" role="search"
          class="mb-5 grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-900/5 sm:grid-cols-2 lg:grid-cols-[1fr_12rem_12rem_auto] lg:items-end">
        <div>
            <label for="q" class="block text-sm font-medium text-slate-700">Cari</label>
            <input id="q" name="q" type="search" value="{{ $q }}" placeholder="Judul atau slug"
                   class="mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]">
        </div>

        <x-admin.select name="status" label="Status" placeholder="Semua status" :options="\App\Models\News::STATUSES" :value="$status" />
        <x-admin.select name="kategori" label="Kategori" placeholder="Semua kategori" :options="$categoryOptions" :value="$category" />

        <div class="flex items-center gap-2">
            <x-admin.button type="submit" icon="search">Terapkan</x-admin.button>
            @if ($filtered)
                <x-admin.button variant="ghost" :href="route('admin.news.index')">Atur Ulang</x-admin.button>
            @endif
        </div>
    </form>

    @if ($items->isEmpty())
        <x-admin.empty icon="newspaper" :title="$filtered ? 'Tidak ada berita yang cocok' : 'Belum ada berita'"
                       :text="$filtered ? 'Coba ubah kata kunci atau atur ulang filter.' : 'Mulai dengan menulis berita pertama, atau jalankan php artisan content:import-news untuk memuat berita contoh.'">
            @unless ($filtered)
                <x-admin.button icon="plus" :href="route('admin.news.create')">Tambah Berita</x-admin.button>
            @endunless
        </x-admin.empty>
    @else
        <x-admin.table>
            <x-slot name="head">
                <tr>
                    <th scope="col" class="px-5 py-3.5">Berita</th>
                    <th scope="col" class="px-5 py-3.5">Kategori</th>
                    <th scope="col" class="px-5 py-3.5">Status</th>
                    <th scope="col" class="px-5 py-3.5">Tayang</th>
                    <th scope="col" class="px-5 py-3.5 text-right"><span class="sr-only">Aksi</span></th>
                </tr>
            </x-slot>

            @foreach ($items as $row)
                @php
                    $thumb = $row->image && file_exists(public_path($row->image)) ? asset($row->image) : null;
                    $title = $row->t('title', 'id');
                @endphp
                <tr class="hover:bg-slate-50/60">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-4">
                            @if ($thumb)
                                <img src="{{ $thumb }}" alt="" loading="lazy" class="h-14 w-20 shrink-0 rounded-lg object-cover ring-1 ring-slate-200">
                            @else
                                <span class="flex h-14 w-20 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-400 ring-1 ring-slate-200">
                                    <x-admin.icon name="photo" class="h-5 w-5" />
                                </span>
                            @endif
                            <div class="min-w-0">
                                <a href="{{ route('admin.news.edit', $row) }}" class="line-clamp-2 font-semibold text-slate-900 hover:text-[#1B3590]">{{ $title }}</a>
                                <p class="mt-0.5 truncate text-xs text-slate-500">/{{ $row->slug }}</p>
                                <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                    @if ($row->is_dummy)<x-admin.badge tone="dummy">Dummy</x-admin.badge>@endif
                                    <x-admin.badge :tone="$row->hasEnglish() ? 'brand' : 'neutral'">{{ $row->hasEnglish() ? 'ID + EN' : 'ID saja' }}</x-admin.badge>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $row->category?->label('id') ?? '-' }}</td>
                    <td class="px-5 py-4">
                        <x-admin.badge :tone="$statusTone[$row->status] ?? 'neutral'">{{ $row->statusLabel() }}</x-admin.badge>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                        {{ $row->published_at ? $row->published_at->translatedFormat('j M Y, H:i') : '-' }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-4">
                        <div class="flex items-center justify-end gap-1">
                            @if ($row->isVisible())
                                <a href="{{ lroute('news.show', ['slug' => $row->slug], 'id') }}" target="_blank" rel="noopener" title="Lihat di situs" aria-label="Lihat {{ $title }} di situs"
                                   class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">
                                    <x-admin.icon name="external" class="h-5 w-5" />
                                </a>
                            @endif
                            <a href="{{ route('admin.news.edit', $row) }}" title="Ubah {{ $title }}" aria-label="Ubah {{ $title }}"
                               class="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-[#1B3590]">
                                <x-admin.icon name="pencil" class="h-5 w-5" />
                            </a>
                            @if ($canDelete)
                                <button type="button" title="Hapus {{ $title }}" aria-label="Hapus {{ $title }}"
                                        @click="$dispatch('open-modal', { name: 'hapus', action: '{{ route('admin.news.destroy', $row) }}' })"
                                        class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-[#E5352E]">
                                    <x-admin.icon name="trash" class="h-5 w-5" />
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-admin.table>

        <x-admin.pager :paginator="$items" />
    @endif

    @if ($canDelete)
        <x-admin.confirm name="hapus" title="Hapus Berita?" method="DELETE" button="Ya, Hapus"
                         message="Berita dan seluruh riwayat revisinya dihapus permanen. Bila hanya ingin menyembunyikan dari situs, ubah statusnya menjadi Draf." />
    @endif
@endsection
