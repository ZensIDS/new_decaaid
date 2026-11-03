@extends('layouts.admin')

@section('title', 'Kategori Klien')

@section('content')
    <x-admin.page-header title="Kategori Klien" description="Kelompok logo di halaman Klien & Mitra. Kategori yang masih berisi klien tidak bisa dihapus."
                         :breadcrumbs="[['label' => 'Konten'], ['label' => 'Klien & Mitra', 'url' => route('admin.clients.index')], ['label' => 'Kategori']]">
        <x-slot name="actions">
            <x-admin.button icon="plus" :href="route('admin.clientcat.create')">Tambah Kategori</x-admin.button>
        </x-slot>
    </x-admin.page-header>

    @if ($categories->isEmpty())
        <x-admin.empty icon="tag" title="Belum ada kategori" text="Tambahkan kategori pertama, atau jalankan php artisan content:import-clients.">
            <x-admin.button icon="plus" :href="route('admin.clientcat.create')">Tambah Kategori</x-admin.button>
        </x-admin.empty>
    @else
        <x-admin.table>
            <x-slot name="head">
                <tr>
                    <th scope="col" class="px-5 py-3.5">Nama</th>
                    <th scope="col" class="px-5 py-3.5">Slug</th>
                    <th scope="col" class="px-5 py-3.5">Klien</th>
                    <th scope="col" class="px-5 py-3.5">Urutan</th>
                    <th scope="col" class="px-5 py-3.5 text-right"><span class="sr-only">Aksi</span></th>
                </tr>
            </x-slot>

            @foreach ($categories as $row)
                @php $name = $row->label('id'); @endphp
                <tr class="hover:bg-slate-50/60">
                    <td class="px-5 py-4">
                        <a href="{{ route('admin.clientcat.edit', $row) }}" class="font-semibold text-slate-900 hover:text-[#1B3590]">{{ $name }}</a>
                        <p class="text-xs text-slate-500">EN: {{ ($row->name['en'] ?? '') !== '' ? $row->name['en'] : 'sama dengan ID' }}</p>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $row->slug }}</td>
                    <td class="whitespace-nowrap px-5 py-4">
                        @if ($row->clients_count > 0)
                            <a href="{{ route('admin.clients.index', ['kategori' => $row->id]) }}" class="font-semibold text-[#1B3590] hover:underline">{{ $row->clients_count }} logo</a>
                        @else
                            <span class="text-slate-400">Kosong</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $row->sort_order }}</td>
                    <td class="whitespace-nowrap px-5 py-4">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('admin.clientcat.edit', $row) }}" title="Ubah {{ $name }}" aria-label="Ubah {{ $name }}"
                               class="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-[#1B3590]">
                                <x-admin.icon name="pencil" class="h-5 w-5" />
                            </a>
                            <button type="button" title="Hapus {{ $name }}" aria-label="Hapus {{ $name }}"
                                    @click="$dispatch('open-modal', { name: 'hapus', action: '{{ route('admin.clientcat.destroy', $row) }}' })"
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
                     message="Kategori dihapus permanen. Kategori yang masih berisi klien akan ditolak; pindahkan atau hapus klien-kliennya dulu." />
@endsection
