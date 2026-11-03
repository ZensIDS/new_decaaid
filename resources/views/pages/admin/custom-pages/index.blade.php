@extends('layouts.admin')

@section('title', 'Halaman Kustom')

@section('content')
    <x-admin.page-header title="Halaman Kustom" description="Halaman sederhana yang kamu tambah sendiri, mis. Syarat & Ketentuan atau Kebijakan Cookie."
                         :breadcrumbs="[['label' => 'Konten'], ['label' => 'Halaman Kustom']]">
        <x-slot name="actions">
            @if ($available)
                <x-admin.button icon="plus" :href="route('admin.custompages.create')">Tambah Halaman</x-admin.button>
            @endif
        </x-slot>
    </x-admin.page-header>

    @unless ($available)
        <p class="mb-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-inset ring-amber-200">
            Tabel halaman kustom belum ada. Jalankan dulu <code>php artisan migrate</code>, lalu muat ulang halaman ini.
        </p>
    @else
        {{-- Cari dan filter --}}
        <form method="GET" action="{{ route('admin.custompages.index') }}" role="search"
              class="mb-5 grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-900/5 sm:grid-cols-2 lg:grid-cols-[1fr_12rem_auto] lg:items-end">
            <div>
                <label for="q" class="block text-sm font-medium text-slate-700">Cari</label>
                <input id="q" name="q" type="search" value="{{ $q }}" placeholder="Judul atau slug"
                       class="mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]">
            </div>

            <x-admin.select name="status" label="Status" placeholder="Semua status" :options="['published' => 'Terbit', 'draft' => 'Draf']" :value="$status" />

            <div class="flex items-center gap-2">
                <x-admin.button type="submit" icon="search">Terapkan</x-admin.button>
                @if ($filtered)
                    <x-admin.button variant="ghost" :href="route('admin.custompages.index')">Atur Ulang</x-admin.button>
                @endif
            </div>
        </form>

        @if ($items->isEmpty())
            <x-admin.empty icon="document" :title="$filtered ? 'Tidak ada halaman yang cocok' : 'Belum ada halaman kustom'"
                           :text="$filtered ? 'Coba ubah kata kunci atau atur ulang filter.' : 'Tambah halaman pertama, misalnya Syarat & Ketentuan. Kebijakan Privasi punya menu sendiri di Halaman Legal.'">
                @unless ($filtered)
                    <x-admin.button icon="plus" :href="route('admin.custompages.create')">Tambah Halaman</x-admin.button>
                @endunless
            </x-admin.empty>
        @else
            <x-admin.table>
                <x-slot name="head">
                    <tr>
                        <th scope="col" class="px-5 py-3.5">Halaman</th>
                        <th scope="col" class="px-5 py-3.5">Status</th>
                        <th scope="col" class="px-5 py-3.5">Footer</th>
                        <th scope="col" class="px-5 py-3.5">Diubah</th>
                        <th scope="col" class="px-5 py-3.5 text-right"><span class="sr-only">Aksi</span></th>
                    </tr>
                </x-slot>

                @foreach ($items as $row)
                    @php $title = $row->t('title', 'id'); @endphp
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-5 py-4">
                            <a href="{{ route('admin.custompages.edit', $row) }}" class="line-clamp-2 font-semibold text-slate-900 hover:text-[#1B3590]">{{ $title }}</a>
                            <p class="mt-0.5 truncate text-xs text-slate-500">/id/{{ $row->slug }} &middot; /en/{{ $row->slug }}</p>
                            <div class="mt-1.5">
                                <x-admin.badge :tone="$row->hasEnglish() ? 'brand' : 'neutral'">{{ $row->hasEnglish() ? 'ID + EN' : 'ID saja' }}</x-admin.badge>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <x-admin.badge :tone="$row->is_published ? 'success' : 'neutral'">{{ $row->is_published ? 'Terbit' : 'Draf' }}</x-admin.badge>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                            {{ $row->show_in_footer ? 'Tampil' : '-' }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $row->updated_at?->translatedFormat('j M Y, H:i') ?? '-' }}</td>
                        <td class="whitespace-nowrap px-5 py-4">
                            <div class="flex items-center justify-end gap-1">
                                @if ($row->is_published)
                                    <a href="{{ lroute('custom', ['slug' => $row->slug], 'id') }}" target="_blank" rel="noopener" title="Lihat di situs" aria-label="Lihat {{ $title }} di situs"
                                       class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800">
                                        <x-admin.icon name="external" class="h-5 w-5" />
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('admin.custompages.toggle', $row) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" title="{{ $row->is_published ? 'Kembalikan ke Draf' : 'Terbitkan' }}" aria-label="{{ $row->is_published ? 'Kembalikan ke Draf' : 'Terbitkan' }} {{ $title }}"
                                            class="rounded-lg p-2 {{ $row->is_published ? 'text-emerald-600 hover:bg-emerald-50' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-700' }}">
                                        <x-admin.icon name="power" class="h-5 w-5" />
                                    </button>
                                </form>
                                <a href="{{ route('admin.custompages.edit', $row) }}" title="Ubah {{ $title }}" aria-label="Ubah {{ $title }}"
                                   class="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-[#1B3590]">
                                    <x-admin.icon name="pencil" class="h-5 w-5" />
                                </a>
                                <button type="button" title="Hapus {{ $title }}" aria-label="Hapus {{ $title }}"
                                        @click="$dispatch('open-modal', { name: 'hapus', action: '{{ route('admin.custompages.destroy', $row) }}' })"
                                        class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-[#E5352E]">
                                    <x-admin.icon name="trash" class="h-5 w-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-admin.table>

            <x-admin.pager :paginator="$items" />
        @endif

        <x-admin.confirm name="hapus" title="Hapus Halaman?" method="DELETE" button="Ya, Hapus"
                         message="Halaman dihapus permanen dan alamatnya tidak bisa dibuka lagi. Bila hanya ingin menyembunyikan dari situs, kembalikan ke Draf." />
    @endunless
@endsection
