@extends('layouts.admin')

@section('title', 'Menu')

@php
    $state = collect(old('items', $items))->map(function ($i, $n) use ($items) {
        $d = collect($items)->firstWhere('key', $i['key']) ?? [];

        return [
            'key'        => $i['key'],
            'label_id'   => (string) ($i['label_id'] ?? ''),
            'label_en'   => (string) ($i['label_en'] ?? ''),
            'visible'    => isset($i['visible']) ? (bool) (int) $i['visible'] : true,
            'default_id' => $d['default_id'] ?? '',
            'default_en' => $d['default_en'] ?? '',
            'children'   => $d['children'] ?? 0,
        ];
    })->values()->all();

    $field = 'block w-full rounded-xl border-0 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]';
    $ghost = 'rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800 disabled:opacity-30 disabled:hover:bg-transparent';
@endphp

@section('content')
    <x-admin.page-header title="Menu" description="Urutan, label, dan tampil/sembunyi menu utama di header dan footer. Anak menu (Tentang Kami, Pilar Bisnis, Portofolio) tetap otomatis."
                         :breadcrumbs="[['label' => 'Situs'], ['label' => 'Menu']]" />

    @unless ($available)
        <p class="mb-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-inset ring-amber-200">
            Tabel penyimpanan belum ada. Jalankan <code>php artisan migrate</code> dulu; sebelum itu perubahan belum bisa disimpan.
        </p>
    @endunless

    @if ($errors->any())
        <p class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-inset ring-red-200">
            {{ $errors->first('items') ?: 'Ada isian yang belum benar. Periksa pesan di bawah.' }}
        </p>
    @endif

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <form method="POST" novalidate action="{{ route('admin.menus.update') }}" class="contents"
              x-data="{
                  lang: 'id',
                  f: @js($state),
                  move(i, d) { const a = this.f, j = i + d; if (j < 0 || j >= a.length) return; [a[i], a[j]] = [a[j], a[i]]; },
                  get shown() { return this.f.filter(i => i.visible).length; },
              }">
            @csrf @method('PUT')

            <div class="space-y-6 lg:col-span-2 lg:col-start-1 lg:row-start-1">
                <div class="flex items-center gap-1 rounded-xl bg-white p-1 shadow-sm ring-1 ring-slate-900/5" role="tablist" aria-label="Bahasa label">
                    <button type="button" role="tab" :aria-selected="(lang === 'id').toString()" @click="lang = 'id'"
                            :class="lang === 'id' ? 'bg-blue-50 text-[#1B3590] shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                            class="flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition">Indonesia (ID)</button>
                    <button type="button" role="tab" :aria-selected="(lang === 'en').toString()" @click="lang = 'en'"
                            :class="lang === 'en' ? 'bg-blue-50 text-[#1B3590] shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                            class="flex-1 rounded-lg px-4 py-2 text-sm font-semibold transition">English (EN)</button>
                </div>

                <x-admin.form-section icon="menu" title="Menu Utama" description="Atur urutan dengan panah. Ikon mata menyembunyikan item dari header, footer, dan daftar halaman di pencarian (halamannya tetap bisa dibuka lewat alamat). Label kosong = label bawaan (tampil abu-abu).">
                    <div class="space-y-3">
                        <template x-for="(it, i) in f" :key="it.key">
                            <div class="flex items-center gap-2 rounded-xl p-3 ring-1 ring-inset" :class="it.visible ? 'bg-slate-50 ring-slate-200' : 'bg-slate-100/60 opacity-70 ring-slate-200'">
                                <input type="hidden" :name="'items[' + i + '][key]'" :value="it.key">
                                <input type="hidden" :name="'items[' + i + '][visible]'" :value="it.visible ? 1 : 0">
                                <input type="hidden" :name="'items[' + i + '][label_id]'" :value="it.label_id">
                                <input type="hidden" :name="'items[' + i + '][label_en]'" :value="it.label_en">

                                <span class="w-6 shrink-0 text-center text-sm font-semibold text-slate-400" x-text="i + 1"></span>
                                <div class="min-w-0 flex-1">
                                    <input x-show="lang === 'id'" x-model="it.label_id" maxlength="40" :placeholder="it.default_id" aria-label="Label ID" class="{{ $field }}">
                                    <input x-show="lang === 'en'" x-cloak x-model="it.label_en" maxlength="40" :placeholder="it.default_en" aria-label="Label EN" class="{{ $field }}">
                                    <p class="mt-1 px-1 text-xs text-slate-500" x-show="it.children > 0" x-text="it.children + ' anak menu otomatis'"></p>
                                </div>
                                <button type="button" class="{{ $ghost }}" @click="it.visible = ! it.visible" :title="it.visible ? 'Sembunyikan' : 'Tampilkan'" :aria-label="it.visible ? 'Sembunyikan' : 'Tampilkan'">
                                    <x-admin.icon name="eye" class="h-4 w-4" x-show="it.visible" />
                                    <x-admin.icon name="eye-slash" class="h-4 w-4" x-show="! it.visible" x-cloak />
                                </button>
                                <button type="button" class="{{ $ghost }}" :disabled="i === 0" @click="move(i, -1)" aria-label="Naikkan"><x-admin.icon name="arrow-left" class="h-4 w-4 rotate-90" /></button>
                                <button type="button" class="{{ $ghost }}" :disabled="i === f.length - 1" @click="move(i, 1)" aria-label="Turunkan"><x-admin.icon name="arrow-right" class="h-4 w-4 rotate-90" /></button>
                            </div>
                        </template>
                    </div>
                    @foreach ($errors->get('items.*') as $msgs)<p class="text-sm text-[#E5352E]">{{ $msgs[0] }}</p>@endforeach
                    <p class="text-sm text-[#E5352E]" x-show="shown === 0" x-cloak>Minimal satu item menu harus tampil.</p>
                </x-admin.form-section>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:col-start-3 lg:row-span-3 lg:row-start-1">
                <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                    <div class="px-6 pb-5 pt-6">
                        <x-admin.badge :tone="$row ? 'success' : 'dummy'">{{ $row ? 'Diatur dari admin' : 'Menu bawaan' }}</x-admin.badge>
                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Item tampil</dt><dd class="font-medium text-slate-700" x-text="shown + ' dari ' + f.length"></dd></div>
                            @if ($row)
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah oleh</dt><dd class="text-right font-medium text-slate-700">{{ $row->editor?->name ?? '-' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah</dt><dd class="font-medium text-slate-700">{{ $row->updated_at?->translatedFormat('j M Y, H:i') }}</dd></div>
                            @endif
                        </dl>
                        <p class="mt-4 text-xs leading-relaxed text-slate-500">Tombol kontak di header dan tab di halaman Tentang Kami tidak ikut terpengaruh.</p>
                    </div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <x-admin.button type="submit" class="w-full">Simpan Perubahan</x-admin.button>
                        <x-admin.button variant="secondary" class="w-full" icon="external" target="_blank" rel="noopener" :href="lroute('home', [], 'id')">Lihat Di Situs</x-admin.button>
                    </div>
                </section>

                @if ($row)
                    <button type="button" @click="$dispatch('open-modal', { name: 'reset', action: '{{ route('admin.menus.reset') }}' })"
                            class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-[#E5352E] hover:bg-red-50">
                        <x-admin.icon name="trash" class="h-4 w-4" /> Kembalikan Ke Menu Bawaan
                    </button>
                @endif
            </aside>
        </form>
    </div>

    @if ($row)
        <x-admin.confirm name="reset" title="Kembalikan ke Menu Bawaan?" method="DELETE" button="Ya, Kembalikan"
                         message="Urutan, label, dan item tersembunyi yang sudah disimpan dihapus; menu kembali seperti bawaan. Tindakan ini tidak bisa dibatalkan." />
    @endif
@endsection
