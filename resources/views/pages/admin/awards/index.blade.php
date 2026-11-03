@extends('layouts.admin')

@section('title', 'Penghargaan')

@php
    $canDelete = auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_ADMIN);
    $yearOptions = $years->mapWithKeys(fn ($y) => [$y => $y])->all();
    $stateOptions = ['tampil' => 'Tampil', 'sembunyi' => 'Disembunyikan', 'unggulan' => 'Terbaru (sorotan)'];
@endphp

@section('content')
    <x-admin.page-header title="Penghargaan" description="Kelola penghargaan dan sertifikat yang tampil di halaman Penghargaan dan carousel beranda."
                         :breadcrumbs="[['label' => 'Konten'], ['label' => 'Penghargaan']]">
        <x-slot name="actions">
            <x-admin.button icon="plus" :href="route('admin.awards.create')">Tambah Penghargaan</x-admin.button>
        </x-slot>
    </x-admin.page-header>

    {{-- Cari dan filter --}}
    <form method="GET" action="{{ route('admin.awards.index') }}" role="search"
          class="mb-5 grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-900/5 sm:grid-cols-2 lg:grid-cols-[1fr_9rem_12rem_auto] lg:items-end">
        <div>
            <label for="q" class="block text-sm font-medium text-slate-700">Cari</label>
            <input id="q" name="q" type="search" value="{{ $q }}" placeholder="Judul atau deskripsi"
                   class="mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]">
        </div>

        <x-admin.select name="tahun" label="Tahun" placeholder="Semua tahun" :options="$yearOptions" :value="$year" />
        <x-admin.select name="status" label="Status" placeholder="Semua status" :options="$stateOptions" :value="$state" />

        <div class="flex items-center gap-2">
            <x-admin.button type="submit" icon="search">Terapkan</x-admin.button>
            @if ($filtered)
                <x-admin.button variant="ghost" :href="route('admin.awards.index')">Atur Ulang</x-admin.button>
            @endif
        </div>
    </form>

    @if ($items->isEmpty())
        <x-admin.empty icon="trophy" :title="$filtered ? 'Tidak ada penghargaan yang cocok' : 'Belum ada penghargaan'"
                       :text="$filtered ? 'Coba ubah kata kunci atau atur ulang filter.' : 'Mulai dengan menambah penghargaan pertama, atau jalankan php artisan content:import-awards untuk memuat penghargaan contoh.'">
            @unless ($filtered)
                <x-admin.button icon="plus" :href="route('admin.awards.create')">Tambah Penghargaan</x-admin.button>
            @endunless
        </x-admin.empty>
    @else
        <x-admin.table>
            <x-slot name="head">
                <tr>
                    <th scope="col" class="px-5 py-3.5">Penghargaan</th>
                    <th scope="col" class="px-5 py-3.5">Tahun</th>
                    <th scope="col" class="px-5 py-3.5">Status</th>
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
                                <a href="{{ route('admin.awards.edit', $row) }}" class="line-clamp-2 font-semibold text-slate-900 hover:text-[#1B3590]">{{ $title }}</a>
                                @if ($row->t('description', 'id') !== '')
                                    <p class="mt-0.5 line-clamp-1 text-xs text-slate-500">{{ $row->t('description', 'id') }}</p>
                                @endif
                                <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                    @if ($row->is_dummy)<x-admin.badge tone="dummy">Dummy</x-admin.badge>@endif
                                    @if ($row->is_featured)<x-admin.badge tone="brand">Terbaru</x-admin.badge>@endif
                                    <x-admin.badge :tone="$row->hasEnglish() ? 'brand' : 'neutral'">{{ $row->hasEnglish() ? 'ID + EN' : 'ID saja' }}</x-admin.badge>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $row->year }}</td>
                    <td class="px-5 py-4">
                        <x-admin.badge :tone="$row->is_visible ? 'success' : 'neutral'">{{ $row->is_visible ? 'Tampil' : 'Disembunyikan' }}</x-admin.badge>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4">
                        <div class="flex items-center justify-end gap-1">
                            {{-- Terbaru (sorotan besar) --}}
                            <form method="POST" action="{{ route('admin.awards.feature', $row) }}">
                                @csrf @method('PATCH')
                                <button type="submit" title="{{ $row->is_featured ? 'Lepas dari sorotan' : 'Jadikan penghargaan terbaru (sorotan)' }}" aria-label="{{ $row->is_featured ? 'Lepas dari sorotan' : 'Jadikan sorotan' }} {{ $title }}"
                                        class="rounded-lg p-2 {{ $row->is_featured ? 'text-amber-500 hover:bg-amber-50' : 'text-slate-400 hover:bg-slate-100 hover:text-amber-500' }}">
                                    <x-admin.icon name="trophy" class="h-5 w-5" />
                                </button>
                            </form>
                            {{-- Tampil / sembunyi --}}
                            <form method="POST" action="{{ route('admin.awards.toggle', $row) }}">
                                @csrf @method('PATCH')
                                <button type="submit" title="{{ $row->is_visible ? 'Sembunyikan dari situs' : 'Tampilkan di situs' }}" aria-label="{{ $row->is_visible ? 'Sembunyikan' : 'Tampilkan' }} {{ $title }}"
                                        class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">
                                    <x-admin.icon name="power" class="h-5 w-5" />
                                </button>
                            </form>
                            @if ($row->is_visible)
                                <a href="{{ lroute('awards', [], 'id') }}" target="_blank" rel="noopener" title="Lihat di situs" aria-label="Lihat halaman Penghargaan di situs"
                                   class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">
                                    <x-admin.icon name="external" class="h-5 w-5" />
                                </a>
                            @endif
                            <a href="{{ route('admin.awards.edit', $row) }}" title="Ubah {{ $title }}" aria-label="Ubah {{ $title }}"
                               class="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-[#1B3590]">
                                <x-admin.icon name="pencil" class="h-5 w-5" />
                            </a>
                            @if ($canDelete)
                                <button type="button" title="Hapus {{ $title }}" aria-label="Hapus {{ $title }}"
                                        @click="$dispatch('open-modal', { name: 'hapus', action: '{{ route('admin.awards.destroy', $row) }}' })"
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
        <x-admin.confirm name="hapus" title="Hapus Penghargaan?" method="DELETE" button="Ya, Hapus"
                         message="Penghargaan dihapus permanen dari situs dan admin. Bila hanya ingin menyembunyikan, pakai tombol tampil/sembunyi." />
    @endif
@endsection
