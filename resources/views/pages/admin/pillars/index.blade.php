@extends('layouts.admin')

@php
    $isSuper = auth()->user()->hasRole(\App\Models\User::ROLE_SUPER_ADMIN);
    $totalBrands = $pillars->sum(fn ($p) => $p->brands->count());
    $grip = '<svg viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5" aria-hidden="true"><circle cx="7" cy="5" r="1.5"/><circle cx="13" cy="5" r="1.5"/><circle cx="7" cy="10" r="1.5"/><circle cx="13" cy="10" r="1.5"/><circle cx="7" cy="15" r="1.5"/><circle cx="13" cy="15" r="1.5"/></svg>';
@endphp

@section('title', 'Pilar & Brand')

@section('content')
    <x-admin.page-header title="Pilar & Brand"
                         description="Jenis bisnis DECAA.ID beserta brand di dalamnya. Perubahan langsung tampil di halaman Pilar Bisnis, menu, dan footer."
                         :breadcrumbs="[['label' => 'Konten'], ['label' => 'Pilar & Brand']]">
        <x-slot name="actions">
            <x-admin.button variant="secondary" icon="plus" :href="route('admin.pillars.create')">Tambah Pilar</x-admin.button>
            @if ($pillars->isNotEmpty())
                <x-admin.button icon="plus" :href="route('admin.pillars.brands.create')">Tambah Brand</x-admin.button>
            @endif
        </x-slot>
    </x-admin.page-header>

    @if ($pillars->isEmpty())
        <x-admin.empty icon="cube" title="Belum ada pilar bisnis"
                       text="Jalankan php artisan content:import-pillars untuk memindahkan 8 pilar dan seluruh brand dari kode, atau tambah pilar pertama secara manual.">
            <x-admin.button icon="plus" :href="route('admin.pillars.create')">Tambah Pilar</x-admin.button>
        </x-admin.empty>
    @else
        <div class="mb-5 flex items-start gap-3 rounded-2xl bg-blue-50/70 px-5 py-4 text-sm text-slate-600 ring-1 ring-blue-100">
            <x-admin.icon name="cube" class="mt-0.5 h-5 w-5 shrink-0 text-[#1B3590]" />
            <p>
                <strong class="text-slate-800">{{ $pillars->count() }} pilar, {{ $totalBrands }} brand.</strong>
                Seret kartu (atau pakai tombol panah) untuk mengubah urutan; tersimpan otomatis. Ikon mata menampilkan atau menyembunyikan di situs.
                Untuk memindahkan brand ke pilar lain, buka Ubah brand.
            </p>
        </div>

        <ul x-data="sortList()" x-ref="list" data-url="{{ route('admin.pillars.sort') }}" data-token="{{ csrf_token() }}"
            @dragstart="start($event)" @dragover="over($event)" @dragend="end()" @drop.prevent
            class="space-y-6">
            @foreach ($pillars as $pillar)
                <li data-id="{{ $pillar->id }}" draggable="true"
                    class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5 transition {{ $pillar->is_visible ? '' : 'opacity-70' }}">

                    {{-- Kepala pilar --}}
                    <div class="flex flex-wrap items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-4 py-3.5 sm:px-6">
                        <span class="cursor-grab text-slate-400 active:cursor-grabbing" title="Seret untuk mengurutkan" aria-hidden="true">{!! $grip !!}</span>
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#1B3590] to-[#4A6CF0] text-white shadow-md shadow-blue-900/20">
                            <x-ui.icon :name="$pillar->icon" class="h-5 w-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <h2 class="truncate text-base font-bold text-slate-900">
                                <a href="{{ route('admin.pillars.edit', $pillar) }}" class="hover:text-[#1B3590]">{{ $pillar->name }}</a>
                            </h2>
                            <p class="truncate text-xs text-slate-500">/pilar-bisnis/{{ $pillar->slug }} &middot; {{ $pillar->brands->count() }} brand</p>
                        </div>
                        @unless ($pillar->is_visible)<x-admin.badge>Tersembunyi</x-admin.badge>@endunless

                        <div class="flex items-center gap-0.5">
                            <button type="button" @click="move($event, -1)" title="Naikkan {{ $pillar->name }}" aria-label="Naikkan {{ $pillar->name }}"
                                    class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                                <x-admin.icon name="chevron-down" class="h-5 w-5 rotate-180" />
                            </button>
                            <button type="button" @click="move($event, 1)" title="Turunkan {{ $pillar->name }}" aria-label="Turunkan {{ $pillar->name }}"
                                    class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                                <x-admin.icon name="chevron-down" class="h-5 w-5" />
                            </button>
                            <form method="POST" action="{{ route('admin.pillars.toggle', $pillar) }}">
                                @csrf @method('PATCH')
                                <button type="submit" title="{{ $pillar->is_visible ? 'Sembunyikan' : 'Tampilkan' }} {{ $pillar->name }}"
                                        aria-label="{{ $pillar->is_visible ? 'Sembunyikan' : 'Tampilkan' }} {{ $pillar->name }}"
                                        class="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-[#1B3590]">
                                    <x-admin.icon :name="$pillar->is_visible ? 'eye' : 'eye-slash'" class="h-5 w-5" />
                                </button>
                            </form>
                            <a href="{{ route('admin.pillars.brands.create', ['pillar' => $pillar->id]) }}" title="Tambah brand di {{ $pillar->name }}" aria-label="Tambah brand di {{ $pillar->name }}"
                               class="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-[#1B3590]">
                                <x-admin.icon name="plus" class="h-5 w-5" />
                            </a>
                            <a href="{{ route('admin.pillars.edit', $pillar) }}" title="Ubah {{ $pillar->name }}" aria-label="Ubah {{ $pillar->name }}"
                               class="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-[#1B3590]">
                                <x-admin.icon name="pencil" class="h-5 w-5" />
                            </a>
                            @if ($isSuper)
                                <button type="button" title="Hapus {{ $pillar->name }}" aria-label="Hapus {{ $pillar->name }}"
                                        @click="$dispatch('open-modal', { name: 'hapus-pilar', action: '{{ route('admin.pillars.destroy', $pillar) }}' })"
                                        class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-[#E5352E]">
                                    <x-admin.icon name="trash" class="h-5 w-5" />
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Brand di dalam pilar --}}
                    @if ($pillar->brands->isEmpty())
                        <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-5 text-sm text-slate-500">
                            <span>Belum ada brand di pilar ini.</span>
                            <a href="{{ route('admin.pillars.brands.create', ['pillar' => $pillar->id]) }}" class="font-semibold text-[#1B3590] hover:underline">Tambah Brand</a>
                        </div>
                    @else
                        <ul x-data="sortList()" x-ref="list" data-url="{{ route('admin.pillars.brands.sort') }}" data-pillar="{{ $pillar->id }}" data-token="{{ csrf_token() }}"
                            @dragstart="start($event)" @dragover="over($event)" @dragend="end()" @drop.prevent
                            class="divide-y divide-slate-100">
                            @foreach ($pillar->brands as $brand)
                                @php
                                    $logoOk = $brand->logo && file_exists(public_path($brand->logo));
                                    $logoSrc = $logoOk ? (str_starts_with($brand->logo, 'img/logo/') ? logo_url($brand->logo) : asset($brand->logo)) : null;
                                @endphp
                                <li data-id="{{ $brand->id }}" draggable="true"
                                    class="flex flex-wrap items-center gap-3 px-4 py-3 transition hover:bg-slate-50/60 sm:px-6 {{ $brand->is_visible ? '' : 'opacity-60' }}">
                                    <span class="cursor-grab text-slate-300 active:cursor-grabbing" title="Seret untuk mengurutkan" aria-hidden="true">{!! $grip !!}</span>

                                    <span class="flex h-12 w-16 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-white p-1.5 ring-1 ring-slate-200">
                                        @if ($logoSrc)
                                            <img src="{{ $logoSrc }}" alt="" draggable="false" loading="lazy" class="max-h-full max-w-full object-contain">
                                        @else
                                            <span class="text-sm font-bold text-slate-300" aria-hidden="true">{{ mb_strtoupper(mb_substr($brand->name, 0, 1)) }}</span>
                                        @endif
                                    </span>

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-slate-900">
                                            <a href="{{ route('admin.pillars.brands.edit', $brand) }}" class="hover:text-[#1B3590]">{{ $brand->name }}</a>
                                        </p>
                                        <p class="truncate text-xs text-slate-500">{{ $brand->slug }}</p>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @if ($brand->is_dummy)<x-admin.badge tone="dummy">Dummy</x-admin.badge>@endif
                                        @unless ($brand->is_visible)<x-admin.badge>Tersembunyi</x-admin.badge>@endunless
                                        @unless ($brand->hasDescription())<x-admin.badge tone="warning">Tanpa deskripsi</x-admin.badge>@endunless
                                        @unless ($logoOk)<x-admin.badge tone="warning">Tanpa logo</x-admin.badge>@endunless
                                    </div>

                                    <div class="flex items-center gap-0.5">
                                        <button type="button" @click="move($event, -1)" title="Naikkan {{ $brand->name }}" aria-label="Naikkan {{ $brand->name }}"
                                                class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                                            <x-admin.icon name="chevron-down" class="h-4 w-4 rotate-180" />
                                        </button>
                                        <button type="button" @click="move($event, 1)" title="Turunkan {{ $brand->name }}" aria-label="Turunkan {{ $brand->name }}"
                                                class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                                            <x-admin.icon name="chevron-down" class="h-4 w-4" />
                                        </button>
                                        <form method="POST" action="{{ route('admin.pillars.brands.toggle', $brand) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" title="{{ $brand->is_visible ? 'Sembunyikan' : 'Tampilkan' }} {{ $brand->name }}"
                                                    aria-label="{{ $brand->is_visible ? 'Sembunyikan' : 'Tampilkan' }} {{ $brand->name }}"
                                                    class="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-[#1B3590]">
                                                <x-admin.icon :name="$brand->is_visible ? 'eye' : 'eye-slash'" class="h-5 w-5" />
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.pillars.brands.edit', $brand) }}" title="Ubah {{ $brand->name }}" aria-label="Ubah {{ $brand->name }}"
                                           class="rounded-lg p-2 text-slate-500 hover:bg-blue-50 hover:text-[#1B3590]">
                                            <x-admin.icon name="pencil" class="h-5 w-5" />
                                        </a>
                                        @if ($isSuper)
                                            <button type="button" title="Hapus {{ $brand->name }}" aria-label="Hapus {{ $brand->name }}"
                                                    @click="$dispatch('open-modal', { name: 'hapus-brand', action: '{{ route('admin.pillars.brands.destroy', $brand) }}' })"
                                                    class="rounded-lg p-2 text-slate-500 hover:bg-red-50 hover:text-[#E5352E]">
                                                <x-admin.icon name="trash" class="h-5 w-5" />
                                            </button>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    @if ($isSuper)
        <x-admin.confirm name="hapus-pilar" title="Hapus Pilar?" method="DELETE" button="Ya, Hapus"
                         message="Pilar dihapus permanen. Pilar yang masih memuat brand atau pilar terakhir akan ditolak. Untuk sekadar menyembunyikan dari situs, pakai ikon mata." />
        <x-admin.confirm name="hapus-brand" title="Hapus Brand?" method="DELETE" button="Ya, Hapus"
                         message="Brand dan logo unggahannya dihapus permanen, dan halaman profil brand ini tidak bisa dibuka lagi. Untuk sekadar menyembunyikan dari situs, pakai ikon mata." />
    @endif

    <script>
        /* Seret-dan-lepas urutan (tanpa pustaka). Dipakai oleh daftar pilar dan tiap daftar brand. */
        function sortList() {
            return {
                dragging: null,

                // Anak langsung dari daftar ini yang memuat elemen target.
                item(target) {
                    const list = this.$refs.list;
                    const el = target && target.nodeType === 1 ? target : (target ? target.parentElement : null);
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
                    const after = e.clientY > box.top + box.height / 2;
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
                    const body = { ids: ids };
                    if (list.dataset.pillar) body.pillar_id = Number(list.dataset.pillar);

                    try {
                        const res = await fetch(list.dataset.url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': list.dataset.token,
                            },
                            body: JSON.stringify(body),
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