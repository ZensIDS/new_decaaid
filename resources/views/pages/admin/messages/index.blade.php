@extends('layouts.admin')

@section('title', 'Pesan Masuk')

@section('content')
    <x-admin.page-header title="Pesan Masuk" description="Pesan dari formulir Kontak di situs. Disimpan 12 bulan, lalu dihapus otomatis."
                         :breadcrumbs="[['label' => 'Interaksi'], ['label' => 'Pesan Masuk']]">
        <x-slot name="actions">
            @if ($available && $items && $items->total() > 0)
                <x-admin.button variant="secondary" icon="arrow-right" :href="route('admin.messages.export', request()->query())">Ekspor CSV</x-admin.button>
            @endif
        </x-slot>
    </x-admin.page-header>

    @unless ($available)
        <p class="mb-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-inset ring-amber-200">
            Tabel pesan belum ada. Jalankan dulu <code>php artisan migrate</code>, lalu muat ulang halaman ini.
        </p>
    @else
        <form method="GET" action="{{ route('admin.messages.index') }}" role="search"
              class="mb-5 grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-900/5 sm:grid-cols-2 lg:grid-cols-[1fr_11rem_14rem_auto] lg:items-end">
            <div>
                <label for="q" class="block text-sm font-medium text-slate-700">Cari</label>
                <input id="q" name="q" type="search" value="{{ $q }}" placeholder="Nama, email, atau isi pesan"
                       class="mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]">
            </div>
            <x-admin.select name="status" label="Status" placeholder="Semua status" :options="['new' => 'Baru', 'read' => 'Dibaca']" :value="$status" />
            <x-admin.select name="subject" label="Subjek" placeholder="Semua subjek" :options="$subjects" :value="$subject" />
            <div class="flex items-center gap-2">
                <x-admin.button type="submit" icon="search">Terapkan</x-admin.button>
                @if ($filtered)
                    <x-admin.button variant="ghost" :href="route('admin.messages.index')">Atur Ulang</x-admin.button>
                @endif
            </div>
        </form>

        @if ($unread > 0)
            <p class="mb-3 text-sm font-semibold text-[#1B3590]">{{ $unread }} pesan belum dibaca</p>
        @endif

        @if ($items->isEmpty())
            <x-admin.empty icon="inbox" :title="$filtered ? 'Tidak ada pesan yang cocok' : 'Belum ada pesan masuk'"
                           :text="$filtered ? 'Coba ubah kata kunci atau atur ulang filter.' : 'Pesan dari formulir di halaman Kontak akan muncul di sini.'" />
        @else
            <x-admin.table>
                <x-slot name="head">
                    <tr>
                        <th scope="col" class="px-5 py-3.5">Pengirim</th>
                        <th scope="col" class="px-5 py-3.5">Subjek</th>
                        <th scope="col" class="px-5 py-3.5">Pesan</th>
                        <th scope="col" class="px-5 py-3.5">Diterima</th>
                        <th scope="col" class="px-5 py-3.5 text-right"><span class="sr-only">Aksi</span></th>
                    </tr>
                </x-slot>

                @foreach ($items as $row)
                    <tr class="hover:bg-slate-50/60 {{ $row->is_read ? '' : 'bg-blue-50/40' }}">
                        <td class="px-5 py-4">
                            <div class="flex items-start gap-2.5">
                                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $row->is_read ? 'bg-transparent' : 'bg-[#1B3590]' }}" @unless ($row->is_read) title="Belum dibaca" @endunless></span>
                                <div class="min-w-0">
                                    <a href="{{ route('admin.messages.show', $row) }}" class="block truncate font-semibold text-slate-900 hover:text-[#1B3590]">{{ $row->name }}</a>
                                    <p class="truncate text-xs text-slate-500">{{ $row->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4"><x-admin.badge tone="brand">{{ $subjects[$row->subject] ?? $row->subject }}</x-admin.badge></td>
                        <td class="max-w-xs px-5 py-4 text-slate-600"><p class="line-clamp-2">{{ $row->message }}</p></td>
                        <td class="whitespace-nowrap px-5 py-4 text-slate-600">{{ $row->created_at->translatedFormat('j M Y, H:i') }}</td>
                        <td class="whitespace-nowrap px-5 py-4">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.messages.show', $row) }}" title="Buka pesan" aria-label="Buka pesan dari {{ $row->name }}"
                                   class="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-[#1B3590]"><x-admin.icon name="eye" class="h-5 w-5" /></a>
                                <button type="button" title="Hapus pesan" aria-label="Hapus pesan dari {{ $row->name }}"
                                        @click="$dispatch('open-modal', { name: 'hapus', action: '{{ route('admin.messages.destroy', $row) }}' })"
                                        class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-[#E5352E]"><x-admin.icon name="trash" class="h-5 w-5" /></button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-admin.table>

            <x-admin.pager :paginator="$items" />
        @endif

        <x-admin.confirm name="hapus" title="Hapus Pesan?" method="DELETE" button="Ya, Hapus"
                         message="Pesan dihapus permanen dari kotak masuk. Salinannya tetap ada di email info@decaa.id bila notifikasi terkirim." />
    @endunless
@endsection
