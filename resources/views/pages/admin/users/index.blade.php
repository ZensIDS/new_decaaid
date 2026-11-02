@extends('layouts.admin')

@section('title', 'Pengguna')

@section('content')
    <x-admin.page-header title="Pengguna" description="Kelola siapa yang bisa masuk ke panel admin, peran, dan statusnya."
                         :breadcrumbs="[['label' => 'Sistem'], ['label' => 'Pengguna']]">
        <x-slot name="actions">
            <x-admin.button icon="plus" :href="route('admin.users.create')">Tambah Pengguna</x-admin.button>
        </x-slot>
    </x-admin.page-header>

    {{-- Cari dan filter --}}
    <form method="GET" action="{{ route('admin.users.index') }}" role="search"
          class="mb-5 grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-900/5 sm:grid-cols-2 lg:grid-cols-[1fr_12rem_12rem_auto] lg:items-end">
        <div>
            <label for="q" class="block text-sm font-medium text-slate-700">Cari</label>
            <input id="q" name="q" type="search" value="{{ $q }}" placeholder="Nama atau email"
                   class="mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]">
        </div>

        <x-admin.select name="role" label="Peran" placeholder="Semua peran" :options="\App\Models\User::ROLES" :value="$role" />
        <x-admin.select name="status" label="Status" placeholder="Semua status" :options="['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']" :value="$status" />

        <div class="flex items-center gap-2">
            <x-admin.button type="submit" icon="search">Terapkan</x-admin.button>
            @if ($filtered)
                <x-admin.button variant="ghost" :href="route('admin.users.index')">Atur Ulang</x-admin.button>
            @endif
        </div>
    </form>

    @if ($users->isEmpty())
        <x-admin.empty icon="users" :title="$filtered ? 'Tidak ada pengguna yang cocok' : 'Belum ada pengguna'"
                       :text="$filtered ? 'Coba ubah kata kunci atau atur ulang filter.' : null" />
    @else
        <x-admin.table>
            <x-slot name="head">
                <tr>
                    <th scope="col" class="px-5 py-3.5">Pengguna</th>
                    <th scope="col" class="px-5 py-3.5">Peran</th>
                    <th scope="col" class="px-5 py-3.5">Status</th>
                    <th scope="col" class="px-5 py-3.5">Login Terakhir</th>
                    <th scope="col" class="px-5 py-3.5 text-right"><span class="sr-only">Aksi</span></th>
                </tr>
            </x-slot>

            @foreach ($users as $row)
                @php $me = $row->is(auth()->user()); @endphp
                <tr class="hover:bg-slate-50/60">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#1B3590] to-[#4A6CF0] text-sm font-bold text-white">
                                {{ mb_strtoupper(mb_substr($row->name, 0, 1)) }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-900">
                                    {{ $row->name }}
                                    @if ($me)<span class="ml-1 text-xs font-medium text-slate-400">(Anda)</span>@endif
                                </p>
                                <p class="truncate text-xs text-slate-500">{{ $row->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <x-admin.badge :tone="$row->isSuperAdmin() ? 'brand' : 'neutral'">{{ $row->roleLabel() }}</x-admin.badge>
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <x-admin.badge :tone="$row->is_active ? 'success' : 'danger'">{{ $row->is_active ? 'Aktif' : 'Nonaktif' }}</x-admin.badge>
                            @if ($row->must_change_password)
                                <x-admin.badge tone="warning">Password Sementara</x-admin.badge>
                            @endif
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                        {{ $row->last_login_at ? $row->last_login_at->translatedFormat('j M Y, H:i') : 'Belum pernah' }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-4">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('admin.users.edit', $row) }}" title="Ubah {{ $row->name }}" aria-label="Ubah {{ $row->name }}"
                               class="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-[#1B3590]">
                                <x-admin.icon name="pencil" class="h-5 w-5" />
                            </a>

                            @unless ($me)
                                @if ($row->is_active)
                                    <button type="button" title="Nonaktifkan {{ $row->name }}" aria-label="Nonaktifkan {{ $row->name }}"
                                            @click="$dispatch('open-modal', { name: 'nonaktifkan', action: '{{ route('admin.users.toggle', $row) }}' })"
                                            class="rounded-lg p-2 text-slate-500 hover:bg-amber-50 hover:text-amber-700">
                                        <x-admin.icon name="power" class="h-5 w-5" />
                                    </button>
                                @else
                                    <button type="button" title="Aktifkan {{ $row->name }}" aria-label="Aktifkan {{ $row->name }}"
                                            @click="$dispatch('open-modal', { name: 'aktifkan', action: '{{ route('admin.users.toggle', $row) }}' })"
                                            class="rounded-lg p-2 text-slate-500 hover:bg-emerald-50 hover:text-emerald-700">
                                        <x-admin.icon name="power" class="h-5 w-5" />
                                    </button>
                                @endif

                                <button type="button" title="Hapus {{ $row->name }}" aria-label="Hapus {{ $row->name }}"
                                        @click="$dispatch('open-modal', { name: 'hapus', action: '{{ route('admin.users.destroy', $row) }}' })"
                                        class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-[#E5352E]">
                                    <x-admin.icon name="trash" class="h-5 w-5" />
                                </button>
                            @endunless
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-admin.table>

        <x-admin.pager :paginator="$users" />
    @endif

    <x-admin.confirm name="nonaktifkan" title="Nonaktifkan Akun?" method="PATCH" button="Ya, Nonaktifkan"
                     message="Pengguna ini langsung dikeluarkan dan tidak bisa masuk sampai diaktifkan lagi. Datanya tetap tersimpan." />
    <x-admin.confirm name="aktifkan" title="Aktifkan Akun?" method="PATCH" button="Ya, Aktifkan" tone="primary"
                     message="Pengguna ini bisa masuk kembali ke panel admin." />
    <x-admin.confirm name="hapus" title="Hapus Pengguna?" method="DELETE" button="Ya, Hapus"
                     message="Akun dihapus permanen dan tidak bisa dikembalikan. Riwayat aktivitasnya tetap tercatat di log. Bila hanya ingin menghentikan akses, pilih Nonaktifkan." />
@endsection