@extends('layouts.admin')

@php
    $canSort = $q === '' && ! $status && ! $permission;
    $shown = $category ? $categories->where('id', (int) $category) : $categories;
    $categoryOptions = $categories->mapWithKeys(fn ($c) => [$c->id => $c->label('id')])->all();
    $iconBtn = 'rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700';
@endphp

@section('title', 'Klien & Mitra')

@section('content')
    <x-admin.page-header title="Klien & Mitra"
                         description="Logo klien dan mitra di halaman Klien & Mitra serta strip logo di beranda. Nama bersifat opsional."
                         :breadcrumbs="[['label' => 'Konten'], ['label' => 'Klien & Mitra']]">
        <x-slot name="actions">
            <x-admin.button variant="secondary" icon="tag" :href="route('admin.clientcat.index')">Kategori</x-admin.button>
            @if ($categories->isNotEmpty())
                <x-admin.button icon="plus" :href="route('admin.clients.create', $category ? ['kategori' => $category] : [])">Tambah Logo</x-admin.button>
            @endif
        </x-slot>
    </x-admin.page-header>

    @if ($categories->isEmpty())
        <x-admin.empty icon="users" title="Belum ada kategori klien"
                       text="Jalankan php artisan content:import-clients untuk memindahkan 7 kategori dan seluruh logo bawaan, atau buat kategori pertama secara manual.">
            <x-admin.button icon="plus" :href="route('admin.clientcat.create')">Tambah Kategori</x-admin.button>
        </x-admin.empty>
    @else
        <div class="mb-5 flex items-start gap-3 rounded-2xl bg-blue-50/70 px-5 py-4 text-sm text-slate-600 ring-1 ring-blue-100">
            <x-admin.icon name="users" class="mt-0.5 h-5 w-5 shrink-0 text-[#1B3590]" />
            <p>
                <strong class="text-slate-800">{{ $total }} logo, {{ $homeCount }} tampil di beranda.</strong>
                @if ($canSort)
                    Seret logo (atau pakai tombol panah) untuk mengurutkan di dalam kategorinya; tersimpan otomatis.
                @else
                    Urutan hanya bisa diubah saat tidak ada pencarian atau filter status/izin.
                @endif
                Ikon mata menampilkan atau menyembunyikan; ikon rumah menandai logo untuk strip beranda.
            </p>
        </div>

        {{-- Unggah banyak logo --}}
        <x-admin.card class="mb-5" title="Unggah Banyak Logo" description="Pilih kategori lalu beberapa berkas sekaligus. Ruang kosong di sekeliling logo dipangkas otomatis.">
            <form method="POST" action="{{ route('admin.clients.bulk') }}" enctype="multipart/form-data" novalidate
                  x-data="{ count: 0 }" class="grid gap-4 lg:grid-cols-[14rem_1fr_auto] lg:items-end">
                @csrf
                <x-admin.select name="client_category_id" label="Kategori" required placeholder="Pilih kategori"
                                :options="$categoryOptions" :value="$category" />
                <div>
                    <label for="files" class="block text-sm font-medium text-slate-700">Berkas logo</label>
                    <input id="files" name="files[]" type="file" multiple accept="image/jpeg,image/png,image/webp"
                           @change="count = $event.target.files.length"
                           class="mt-1.5 block w-full text-sm text-slate-600 file:mr-4 file:rounded-xl file:border-0 file:bg-blue-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-[#1B3590] hover:file:bg-blue-100">
                    @if ($errors->has('files') || $errors->has('files.*'))
                        <p class="mt-1.5 text-sm text-[#E5352E]">{{ $errors->first('files') ?: $errors->first('files.*') }}</p>
                    @else
                        <p class="mt-1.5 text-xs text-slate-500">JPG, PNG, atau WebP, maksimal 5 MB per berkas dan {{ $bulkMax }} berkas sekali unggah.</p>
                    @endif
                </div>
                <x-admin.button type="submit" icon="plus" x-bind:disabled="count === 0">
                    <span x-text="count ? 'Unggah ' + count + ' Logo' : 'Unggah'">Unggah</span>
                </x-admin.button>
            </form>
        </x-admin.card>

        {{-- Cari dan filter --}}
        <form method="GET" action="{{ route('admin.clients.index') }}" role="search"
              class="mb-6 grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-900/5 sm:grid-cols-2 lg:grid-cols-[1fr_12rem_11rem_12rem_auto] lg:items-end">
            <div>
                <label for="q" class="block text-sm font-medium text-slate-700">Cari nama</label>
                <input id="q" name="q" type="search" value="{{ $q }}" placeholder="Nama klien"
                       class="mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]">
            </div>
            <x-admin.select name="kategori" label="Kategori" placeholder="Semua kategori" :options="$categoryOptions" :value="$category" />
            <x-admin.select name="status" label="Status" placeholder="Semua status"
                            :options="['tampil' => 'Tampil', 'tersembunyi' => 'Tersembunyi', 'beranda' => 'Di beranda']" :value="$status" />
            <x-admin.select name="izin" label="Izin tampil" placeholder="Semua"
                            :options="['belum' => 'Belum dikonfirmasi', 'sudah' => 'Sudah dikonfirmasi']" :value="$permission" />
            <div class="flex items-center gap-2">
                <x-admin.button type="submit" icon="search">Terapkan</x-admin.button>
                @if ($filtered)
                    <x-admin.button variant="ghost" :href="route('admin.clients.index')">Atur Ulang</x-admin.button>
                @endif
            </div>
        </form>

        @php $anyShown = false; @endphp

        <div class="space-y-8">
            @foreach ($shown as $cat)
                @php
                    $items = $groups->get($cat->id, collect());
                    if ($filtered && $items->isEmpty() && ! $category) { continue; }
                    $anyShown = $anyShown || $items->isNotEmpty();
                @endphp

                <section aria-labelledby="cat-{{ $cat->id }}">
                    <div class="mb-3 flex items-center gap-3">
                        <span class="h-6 w-1 rounded-full bg-gradient-to-b from-[#1B3590] to-[#4A6CF0]"></span>
                        <h2 id="cat-{{ $cat->id }}" class="text-base font-bold text-slate-900">{{ $cat->label('id') }}</h2>
                        <x-admin.badge>{{ $items->count() }}{{ $filtered ? ' dari ' . $cat->clients_count : '' }}</x-admin.badge>
                        <span class="h-px flex-1 bg-slate-200"></span>
                        <a href="{{ route('admin.clients.create', ['kategori' => $cat->id]) }}" class="text-xs font-semibold text-[#1B3590] hover:underline">Tambah logo</a>
                    </div>

                    @if ($items->isEmpty())
                        <p class="rounded-2xl border border-dashed border-slate-300 bg-white/70 px-5 py-6 text-center text-sm text-slate-500">
                            {{ $filtered ? 'Tidak ada logo yang cocok di kategori ini.' : 'Belum ada logo di kategori ini.' }}
                        </p>
                    @else
                        <ul x-data="clientSort()" x-ref="list" data-url="{{ route('admin.clients.sort') }}" data-token="{{ csrf_token() }}"
                            @if ($canSort) @dragstart="start($event)" @dragover="over($event)" @dragend="end()" @drop.prevent @endif
                            class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
                            @foreach ($items as $client)
                                @php $logo = $client->logoUrl(); $name = $client->displayName(); @endphp
                                <li data-id="{{ $client->id }}" @if ($canSort) draggable="true" @endif
                                    class="flex flex-col overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-900/5 transition hover:shadow-md {{ $client->is_visible ? '' : 'opacity-60' }} {{ $canSort ? 'cursor-grab active:cursor-grabbing' : '' }}">
                                    <div class="relative flex aspect-[3/2] items-center justify-center p-4">
                                        @if ($logo)
                                            <img src="{{ $logo }}" alt="{{ $client->name ?: 'Logo klien' }}" draggable="false" loading="lazy" class="max-h-full max-w-full object-contain">
                                        @else
                                            <span class="flex flex-col items-center gap-1 text-slate-300" title="Berkas logo tidak ditemukan">
                                                <x-admin.icon name="photo" class="h-7 w-7" />
                                                <span class="text-[11px] font-medium text-amber-600">Berkas hilang</span>
                                            </span>
                                        @endif
                                        <span class="absolute left-2 top-2 flex items-center gap-1">
                                            @unless ($client->permission_ok)
                                                <span class="h-2.5 w-2.5 rounded-full bg-amber-400 ring-2 ring-white" title="Izin tampil belum dikonfirmasi" role="img" aria-label="Izin tampil belum dikonfirmasi"></span>
                                            @endunless
                                        </span>
                                        <span class="absolute right-2 top-2 flex items-center gap-1">
                                            @if ($client->show_on_home)
                                                <span class="rounded-full bg-blue-50 p-1 text-[#1B3590] ring-1 ring-blue-200" title="Tampil di beranda"><x-admin.icon name="home" class="h-3.5 w-3.5" /></span>
                                            @endif
                                            @unless ($client->is_visible)
                                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600 ring-1 ring-slate-200">Tersembunyi</span>
                                            @endunless
                                        </span>
                                    </div>
                                    <p class="truncate border-t border-slate-100 px-3 py-2 text-xs font-semibold {{ $client->name ? 'text-slate-800' : 'text-slate-400' }}" title="{{ $name }}">{{ $name }}</p>

                                    <div class="flex items-center justify-between gap-0.5 border-t border-slate-100 bg-slate-50/60 px-1.5 py-1">
                                        <div class="flex items-center">
                                            @if ($canSort)
                                                <button type="button" @click="move($event, -1)" title="Geser ke depan" aria-label="Geser {{ $name }} ke depan" class="{{ $iconBtn }}"><x-admin.icon name="arrow-left" class="h-4 w-4" /></button>
                                                <button type="button" @click="move($event, 1)" title="Geser ke belakang" aria-label="Geser {{ $name }} ke belakang" class="{{ $iconBtn }}"><x-admin.icon name="arrow-right" class="h-4 w-4" /></button>
                                            @endif
                                        </div>
                                        <div class="flex items-center">
                                            <form method="POST" action="{{ route('admin.clients.home', $client) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" title="{{ $client->show_on_home ? 'Lepas dari beranda' : 'Tampilkan di beranda' }}" aria-label="{{ $client->show_on_home ? 'Lepas dari beranda' : 'Tampilkan di beranda' }}: {{ $name }}"
                                                        class="rounded-lg p-1.5 {{ $client->show_on_home ? 'text-[#1B3590] hover:bg-blue-50' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-700' }}">
                                                    <x-admin.icon name="home" class="h-4 w-4" />
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.clients.toggle', $client) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" title="{{ $client->is_visible ? 'Sembunyikan' : 'Tampilkan' }}" aria-label="{{ $client->is_visible ? 'Sembunyikan' : 'Tampilkan' }} {{ $name }}" class="{{ $iconBtn }}">
                                                    <x-admin.icon :name="$client->is_visible ? 'eye' : 'eye-slash'" class="h-4 w-4" />
                                                </button>
                                            </form>
                                            <a href="{{ route('admin.clients.edit', $client) }}" title="Ubah" aria-label="Ubah {{ $name }}" class="rounded-lg p-1.5 text-slate-400 hover:bg-blue-50 hover:text-[#1B3590]">
                                                <x-admin.icon name="pencil" class="h-4 w-4" />
                                            </a>
                                            <button type="button" title="Hapus" aria-label="Hapus {{ $name }}"
                                                    @click="$dispatch('open-modal', { name: 'hapus', action: '{{ route('admin.clients.destroy', $client) }}' })"
                                                    class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-[#E5352E]">
                                                <x-admin.icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            @endforeach
        </div>

        @if ($filtered && ! $anyShown)
            <x-admin.empty icon="users" title="Tidak ada klien yang cocok" text="Coba ubah kata kunci atau atur ulang filter." />
        @endif
    @endif

    <x-admin.confirm name="hapus" title="Hapus Klien?" method="DELETE" button="Ya, Hapus"
                     message="Logo ini dihapus dari daftar dan dari situs. Bila hanya ingin menyembunyikannya, pakai ikon mata." />

    <script>
        function clientSort() {
            return {
                dragging: null,

                item(el) {
                    const list = this.$refs.list;
                    let li = el ? el.closest('[data-id]') : null;
                    while (li && li.parentElement !== list) {
                        li = li.parentElement ? li.parentElement.closest('[data-id]') : null;
                    }
                    return li;
                },

                start(e) {
                    const li = this.item(e.target);
                    if (!li || e.target !== li) return;
                    this.dragging = li;
                    e.dataTransfer.effectAllowed = 'move';
                    try { e.dataTransfer.setData('text/plain', li.dataset.id); } catch (err) { /* abaikan */ }
                    setTimeout(() => li.classList.add('opacity-40'), 0);
                },

                over(e) {
                    if (!this.dragging) return;
                    e.preventDefault();
                    const li = this.item(e.target);
                    if (!li || li === this.dragging) return;
                    const box = li.getBoundingClientRect();
                    // Kisi: sesudah bila pointer di bawah kartu, atau di setengah kanan pada baris yang sama.
                    const after = e.clientY > box.bottom || (e.clientY >= box.top && e.clientX > box.left + box.width / 2);
                    this.$refs.list.insertBefore(this.dragging, after ? li.nextElementSibling : li);
                },

                end() {
                    if (!this.dragging) return;
                    this.dragging.classList.remove('opacity-40');
                    this.dragging = null;
                    this.save();
                },

                // Tombol panah (untuk HP dan papan ketik).
                move(e, dir) {
                    const li = this.item(e.currentTarget);
                    if (!li) return;
                    const sib = dir < 0 ? li.previousElementSibling : li.nextElementSibling;
                    if (!sib) return;
                    this.$refs.list.insertBefore(li, dir < 0 ? sib : sib.nextElementSibling);
                    this.save();
                },

                async save() {
                    const list = this.$refs.list;
                    const ids = Array.from(list.children).filter((c) => c.dataset.id).map((c) => Number(c.dataset.id));

                    try {
                        const res = await fetch(list.dataset.url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': list.dataset.token,
                            },
                            body: JSON.stringify({ ids: ids }),
                        });
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        this.$dispatch('toast', { type: 'success', text: 'Urutan disimpan.' });
                    } catch (err) {
                        this.$dispatch('toast', { type: 'error', text: 'Urutan gagal disimpan. Muat ulang halaman lalu coba lagi.' });
                    }
                },
            };
        }
    </script>
@endsection
