@extends('layouts.admin')

@section('title', 'Kategori Berita')

@section('content')
    <x-admin.page-header title="Kategori Berita" description="Kelompok berita di halaman Berita. Kategori yang masih dipakai berita tidak bisa dihapus."
                         :breadcrumbs="[['label' => 'Konten'], ['label' => 'Berita', 'url' => route('admin.news.index')], ['label' => 'Kategori']]">
        <x-slot name="actions">
            <x-admin.button icon="plus" :href="route('admin.newscat.create')">Tambah Kategori</x-admin.button>
        </x-slot>
    </x-admin.page-header>

    @if ($categories->isEmpty())
        <x-admin.empty icon="tag" title="Belum ada kategori" text="Berita harus punya kategori. Tambahkan kategori pertama, atau jalankan php artisan content:import-news.">
            <x-admin.button icon="plus" :href="route('admin.newscat.create')">Tambah Kategori</x-admin.button>
        </x-admin.empty>
    @else
        <x-admin.table>
            <x-slot name="head">
                <tr>
                    <th scope="col" class="px-5 py-3.5">Nama</th>
                    <th scope="col" class="px-5 py-3.5">Slug</th>
                    <th scope="col" class="px-5 py-3.5">Berita</th>
                    <th scope="col" class="px-5 py-3.5">Urutan</th>
                    <th scope="col" class="px-5 py-3.5 text-right"><span class="sr-only">Aksi</span></th>
                </tr>
            </x-slot>

            @foreach ($categories as $row)
                @php $name = $row->label('id'); @endphp
                <tr class="hover:bg-slate-50/60">
                    <td class="px-5 py-4">
                        <a href="{{ route('admin.newscat.edit', $row) }}" class="font-semibold text-slate-900 hover:text-[#1B3590]">{{ $name }}</a>
                        <p class="text-xs text-slate-500">EN: {{ ($row->name['en'] ?? '') !== '' ? $row->name['en'] : 'sama dengan ID' }}</p>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $row->slug }}</td>
                    <td class="whitespace-nowrap px-5 py-4">
                        @if ($row->news_count > 0)
                            <a href="{{ route('admin.news.index', ['kategori' => $row->id]) }}" class="font-semibold text-[#1B3590] hover:underline">{{ $row->news_count }} berita</a>
                        @else
                            <span class="text-slate-400">Kosong</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $row->sort_order }}</td>
                    <td class="whitespace-nowrap px-5 py-4">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('admin.newscat.edit', $row) }}" title="Ubah {{ $name }}" aria-label="Ubah {{ $name }}"
                               class="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-[#1B3590]">
                                <x-admin.icon name="pencil" class="h-5 w-5" />
                            </a>
                            <button type="button" title="Hapus {{ $name }}" aria-label="Hapus {{ $name }}"
                                    @click="$dispatch('open-modal', { name: 'hapus', action: '{{ route('admin.newscat.destroy', $row) }}' })"
                                    class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-[#E5352E]">
                                <x-admin.icon name="trash" class="h-5 w-5" />
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-admin.table>
    @endif

    <x-admin.confirm name="hapus" title="Hapus Kategori?" method="DELETE" button="Ya, Hapus"
                     message="Kategori dihapus permanen. Kategori yang masih dipakai berita akan ditolak; pindahkan beritanya dulu." />
@endsection
