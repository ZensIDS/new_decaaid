@extends('layouts.admin')

@section('title', 'Log Aktivitas')

@section('content')
    <x-admin.page-header title="Log Aktivitas" description="Catatan siapa melakukan apa dan kapan. Hanya Super Admin yang bisa melihat halaman ini."
                         :breadcrumbs="[['label' => 'Sistem'], ['label' => 'Log Aktivitas']]" />

    <form method="GET" action="{{ route('admin.logs.index') }}" role="search"
          class="mb-5 grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-900/5 sm:grid-cols-2 lg:grid-cols-[1fr_14rem_auto] lg:items-end">
        <div>
            <label for="q" class="block text-sm font-medium text-slate-700">Cari</label>
            <input id="q" name="q" type="search" value="{{ $q }}" placeholder="Nama pelaku atau isi aktivitas"
                   class="mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]">
        </div>

        <x-admin.select name="group" label="Jenis" placeholder="Semua jenis" :options="\App\Models\ActivityLog::GROUPS" :value="$group" />

        <div class="flex items-center gap-2">
            <x-admin.button type="submit" icon="search">Terapkan</x-admin.button>
            @if ($filtered)
                <x-admin.button variant="ghost" :href="route('admin.logs.index')">Atur Ulang</x-admin.button>
            @endif
        </div>
    </form>

    @if ($logs->isEmpty())
        <x-admin.empty icon="clock" :title="$filtered ? 'Tidak ada aktivitas yang cocok' : 'Belum ada aktivitas'"
                       :text="$filtered ? 'Coba ubah kata kunci atau atur ulang filter.' : 'Login dan perubahan data pengguna akan tercatat di sini.'" />
    @else
        <x-admin.table>
            <x-slot name="head">
                <tr>
                    <th scope="col" class="px-5 py-3.5">Waktu</th>
                    <th scope="col" class="px-5 py-3.5">Pelaku</th>
                    <th scope="col" class="px-5 py-3.5">Aktivitas</th>
                    <th scope="col" class="px-5 py-3.5">Alamat IP</th>
                </tr>
            </x-slot>

            @foreach ($logs as $log)
                <tr class="hover:bg-slate-50/60">
                    <td class="whitespace-nowrap px-5 py-3.5 text-slate-600">
                        <time datetime="{{ $log->created_at->toIso8601String() }}" title="{{ $log->created_at->diffForHumans() }}">
                            {{ $log->created_at->translatedFormat('j M Y, H:i:s') }}
                        </time>
                    </td>
                    <td class="whitespace-nowrap px-5 py-3.5 font-semibold text-slate-900">{{ $log->user_name ?: 'Sistem' }}</td>
                    <td class="px-5 py-3.5 text-slate-700">{{ $log->description }}</td>
                    <td class="whitespace-nowrap px-5 py-3.5 font-mono text-xs text-slate-500">{{ $log->ip_address ?: '-' }}</td>
                </tr>
            @endforeach
        </x-admin.table>

        <x-admin.pager :paginator="$logs" />
    @endif
@endsection