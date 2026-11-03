@extends('layouts.admin')

@section('title', 'SEO & Redirect')

@section('content')
    <x-admin.page-header title="SEO & Redirect" description="Alihkan alamat lama (mis. URL WordPress) ke alamat baru, dan lihat sitemap serta robots.txt situs."
                         :breadcrumbs="[['label' => 'Situs'], ['label' => 'SEO & Redirect']]">
        <x-slot name="actions">
            @if ($available)
                <x-admin.button variant="secondary" :href="route('admin.redirects.import')">Impor Banyak</x-admin.button>
                <x-admin.button icon="plus" :href="route('admin.redirects.create')">Tambah Redirect</x-admin.button>
            @endif
        </x-slot>
    </x-admin.page-header>

    {{-- Sitemap dan robots.txt --}}
    <section class="mb-5 grid gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-900/5 sm:grid-cols-2" aria-label="Sitemap dan robots.txt">
        <div>
            <p class="text-sm font-semibold text-slate-800">Sitemap XML</p>
            <p class="mt-1 text-xs leading-relaxed text-slate-500">Dibuat otomatis dari halaman bawaan, pilar dan brand, proyek, berita, dan halaman kustom yang terbit (ID + EN, lengkap dengan hreflang). Tidak perlu diperbarui manual.</p>
            <a href="{{ url('/sitemap.xml') }}" target="_blank" rel="noopener" class="mt-2 inline-flex items-center gap-1.5 text-sm font-semibold text-[#1B3590] hover:underline">
                {{ url('/sitemap.xml') }} <x-admin.icon name="external" class="h-4 w-4" />
            </a>
        </div>
        <div>
            <p class="text-sm font-semibold text-slate-800">robots.txt</p>
            <p class="mt-1 text-xs leading-relaxed text-slate-500">
                @if ($noindex)
                    Mode staging aktif (<code>SITE_NOINDEX=true</code>): semua halaman <strong>noindex</strong>, robots.txt melarang semua mesin pencari, dan sitemap dikosongkan.
                @else
                    Mode produksi: mesin pencari boleh mengindeks situs (kecuali /admin dan pencarian). Alamat sitemap dicantumkan otomatis.
                @endif
            </p>
            <a href="{{ url('/robots.txt') }}" target="_blank" rel="noopener" class="mt-2 inline-flex items-center gap-1.5 text-sm font-semibold text-[#1B3590] hover:underline">
                {{ url('/robots.txt') }} <x-admin.icon name="external" class="h-4 w-4" />
            </a>
        </div>
    </section>

    @if (session('import_skipped'))
        <div class="mb-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-inset ring-amber-200">
            <p class="font-semibold">Baris yang dilewati:</p>
            <ul class="mt-1.5 list-disc space-y-0.5 pl-5">
                @foreach (session('import_skipped') as $note)
                    <li>{{ $note }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @unless ($available)
        <p class="mb-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-inset ring-amber-200">
            Tabel redirect belum ada. Jalankan dulu <code>php artisan migrate</code>, lalu muat ulang halaman ini.
        </p>
    @else
        <form method="GET" action="{{ route('admin.redirects.index') }}" role="search"
              class="mb-5 grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-900/5 sm:grid-cols-2 lg:grid-cols-[1fr_12rem_auto] lg:items-end">
            <div>
                <label for="q" class="block text-sm font-medium text-slate-700">Cari</label>
                <input id="q" name="q" type="search" value="{{ $q }}" placeholder="Alamat lama, tujuan, atau catatan"
                       class="mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]">
            </div>

            <x-admin.select name="status" label="Status" placeholder="Semua status" :options="['active' => 'Aktif', 'off' => 'Nonaktif']" :value="$status" />

            <div class="flex items-center gap-2">
                <x-admin.button type="submit" icon="search">Terapkan</x-admin.button>
                @if ($filtered)
                    <x-admin.button variant="ghost" :href="route('admin.redirects.index')">Atur Ulang</x-admin.button>
                @endif
            </div>
        </form>

        @if ($items->isEmpty())
            <x-admin.empty icon="link" :title="$filtered ? 'Tidak ada redirect yang cocok' : 'Belum ada redirect'"
                           :text="$filtered ? 'Coba ubah kata kunci atau atur ulang filter.' : 'Tambah satu per satu, tempel banyak sekaligus, atau buat dari berita WordPress dengan php artisan redirects:import-wp.'">
                @unless ($filtered)
                    <x-admin.button icon="plus" :href="route('admin.redirects.create')">Tambah Redirect</x-admin.button>
                @endunless
            </x-admin.empty>
        @else
            <x-admin.table>
                <x-slot name="head">
                    <tr>
                        <th scope="col" class="px-5 py-3.5">Alamat Lama</th>
                        <th scope="col" class="px-5 py-3.5">Tujuan</th>
                        <th scope="col" class="px-5 py-3.5">Status</th>
                        <th scope="col" class="px-5 py-3.5">Dipakai</th>
                        <th scope="col" class="px-5 py-3.5 text-right"><span class="sr-only">Aksi</span></th>
                    </tr>
                </x-slot>

                @foreach ($items as $row)
                    <tr class="hover:bg-slate-50/60">
                        <td class="max-w-xs px-5 py-4">
                            <a href="{{ route('admin.redirects.edit', $row) }}" class="block truncate font-semibold text-slate-900 hover:text-[#1B3590]" title="{{ $row->from_path }}">{{ $row->from_path }}</a>
                            @if ($row->note)<p class="mt-0.5 truncate text-xs text-slate-500">{{ $row->note }}</p>@endif
                        </td>
                        <td class="max-w-xs px-5 py-4"><span class="block truncate text-slate-600" title="{{ $row->to_path }}">{{ $row->to_path }}</span></td>
                        <td class="whitespace-nowrap px-5 py-4">
                            <x-admin.badge :tone="$row->is_active ? 'success' : 'neutral'">{{ $row->is_active ? 'Aktif' : 'Nonaktif' }}</x-admin.badge>
                            <span class="ml-1 text-xs text-slate-500">{{ $row->status_code }}</span>
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                            {{ number_format($row->hits, 0, ',', '.') }}&times;
                            @if ($row->last_hit_at)<span class="block text-xs text-slate-400">{{ $row->last_hit_at->translatedFormat('j M Y') }}</span>@endif
                        </td>
                        <td class="whitespace-nowrap px-5 py-4">
                            <div class="flex items-center justify-end gap-1">
                                <form method="POST" action="{{ route('admin.redirects.toggle', $row) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" title="{{ $row->is_active ? 'Nonaktifkan' : 'Aktifkan' }}" aria-label="{{ $row->is_active ? 'Nonaktifkan' : 'Aktifkan' }} redirect {{ $row->from_path }}"
                                            class="rounded-lg p-2 {{ $row->is_active ? 'text-emerald-600 hover:bg-emerald-50' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-700' }}">
                                        <x-admin.icon name="power" class="h-5 w-5" />
                                    </button>
                                </form>
                                <a href="{{ route('admin.redirects.edit', $row) }}" title="Ubah" aria-label="Ubah redirect {{ $row->from_path }}"
                                   class="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-[#1B3590]"><x-admin.icon name="pencil" class="h-5 w-5" /></a>
                                <button type="button" title="Hapus" aria-label="Hapus redirect {{ $row->from_path }}"
                                        @click="$dispatch('open-modal', { name: 'hapus', action: '{{ route('admin.redirects.destroy', $row) }}' })"
                                        class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-[#E5352E]"><x-admin.icon name="trash" class="h-5 w-5" /></button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-admin.table>

            <x-admin.pager :paginator="$items" />
        @endif

        <x-admin.confirm name="hapus" title="Hapus Redirect?" method="DELETE" button="Ya, Hapus"
                         message="Aturan dihapus permanen. Alamat lama itu akan memberi halaman 404 lagi. Bila hanya ingin menghentikan sementara, nonaktifkan saja." />
    @endunless
@endsection
