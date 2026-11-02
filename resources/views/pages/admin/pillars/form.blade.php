@extends('layouts.admin')

@php
    $editing = (bool) $pillar;
    $nameVal = old('name', $editing ? $pillar->name : '');
    $slugVal = old('slug', $editing ? $pillar->slug : '');
    $iconVal = old('icon', $editing ? $pillar->icon : 'briefcase');
    $visible = $editing ? $pillar->is_visible : true;
@endphp

@section('title', $editing ? 'Ubah Pilar' : 'Tambah Pilar')

@section('content')
    <x-admin.page-header :title="$editing ? 'Ubah Pilar' : 'Tambah Pilar'"
                         description="Pilar adalah jenis bisnis. Nama dan ikon tampil di menu, footer, dan halaman Pilar Bisnis; brand dikelola di dalam pilar."
                         :breadcrumbs="[['label' => 'Konten'], ['label' => 'Pilar & Brand', 'url' => route('admin.pillars.index')], ['label' => $editing ? 'Ubah' : 'Tambah']]" />

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <form method="POST" novalidate class="contents"
              action="{{ $editing ? route('admin.pillars.update', $pillar) : route('admin.pillars.store') }}"
              x-data="{ name: @js($nameVal), slug: @js($slugVal), icon: @js($iconVal) }">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="space-y-6 lg:col-span-2 lg:col-start-1 lg:row-start-1">
                <x-admin.form-section icon="cube" title="Informasi Pilar" description="Nama jenis bisnis dan alamat halamannya.">
                    <x-admin.input name="name" label="Nama pilar" required x-model="name" :value="$nameVal"
                                   hint="Contoh: Hardware & Peripheral. Nama pilar sama untuk Indonesia dan Inggris." />
                    <x-admin.input name="slug" label="Slug (alamat halaman)" x-model="slug" :value="$slugVal" autocomplete="off"
                                   :hint="$editing
                                       ? 'Mengubah slug mengubah alamat halaman pilar dan semua brand di dalamnya; tautan lama berhenti bekerja (redirect otomatis menyusul di B3n).'
                                       : 'Kosongkan agar dibuat otomatis dari nama. Hanya huruf kecil, angka, dan tanda hubung.'" />
                </x-admin.form-section>

                <x-admin.form-section icon="sparkles" title="Ikon" description="Ikon tampil di kartu pilar. Pilih salah satu dari daftar.">
                    <fieldset>
                        <legend class="sr-only">Ikon pilar</legend>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach (\App\Models\Pillar::ICONS as $value => $label)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="icon" value="{{ $value }}" class="peer sr-only" x-model="icon" @checked($iconVal === $value)>
                                    <span class="flex flex-col items-center gap-2 rounded-2xl bg-slate-50 px-3 py-4 text-center ring-1 ring-inset ring-slate-200 transition hover:bg-white hover:shadow-md peer-checked:bg-blue-50 peer-checked:text-[#1B3590] peer-checked:ring-2 peer-checked:ring-[#1B3590] peer-focus-visible:ring-2 peer-focus-visible:ring-[#1B3590]">
                                        <x-ui.icon :name="$value" class="h-6 w-6" />
                                        <span class="text-xs font-semibold text-slate-700">{{ $label }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('icon')<p class="mt-2 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                    </fieldset>
                </x-admin.form-section>

                <x-admin.form-section icon="eye" title="Tampilan di Situs" description="Pilar yang disembunyikan hilang dari menu, footer, pencarian, dan halaman Pilar Bisnis.">
                    <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
                        <x-admin.toggle name="is_visible" label="Tampilkan di situs" description="Matikan untuk menyembunyikan tanpa menghapus." :checked="$visible" />
                    </div>
                </x-admin.form-section>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:col-start-3 lg:row-start-1">
                <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                    <div class="relative h-24 bg-gradient-to-br from-[#1B3590] via-[#2644B0] to-[#4A6CF0]">
                        <div class="pointer-events-none absolute inset-0 opacity-[0.12]" aria-hidden="true"
                             style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 20px 20px;"></div>
                    </div>
                    <div class="px-6 pb-6 text-center">
                        <span class="relative -mt-8 mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-[#1B3590] shadow-xl ring-4 ring-white">
                            @foreach (array_keys(\App\Models\Pillar::ICONS) as $iconName)
                                <span x-show="icon === '{{ $iconName }}'" @if ($iconVal !== $iconName) x-cloak @endif>
                                    <x-ui.icon :name="$iconName" class="h-7 w-7" />
                                </span>
                            @endforeach
                        </span>
                        <p class="mt-3 truncate text-base font-bold text-slate-900" x-text="name.trim() || 'Nama pilar'"></p>
                        <p class="mt-1 truncate text-xs text-slate-400" x-text="'/pilar-bisnis/' + (slug.trim() || '...')"></p>
                        @if ($editing)
                            <p class="mt-4 border-t border-slate-100 pt-4 text-sm text-slate-600">Memuat <strong>{{ $pillar->brands_count }}</strong> brand</p>
                        @endif
                    </div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <x-admin.button type="submit" class="w-full">{{ $editing ? 'Simpan Perubahan' : 'Buat Pilar' }}</x-admin.button>
                        <x-admin.button variant="ghost" class="w-full" :href="route('admin.pillars.index')">Batal</x-admin.button>
                    </div>
                </section>
            </aside>
        </form>
    </div>
@endsection