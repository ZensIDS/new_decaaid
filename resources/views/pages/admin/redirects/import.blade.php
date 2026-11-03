@extends('layouts.admin')

@section('title', 'Impor Redirect')

@section('content')
    <x-admin.page-header title="Impor Redirect" description="Tempel banyak aturan sekaligus, satu baris satu aturan."
                         :breadcrumbs="[['label' => 'Situs'], ['label' => 'SEO & Redirect', 'url' => route('admin.redirects.index')], ['label' => 'Impor']]" />

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <form method="POST" novalidate class="contents" action="{{ route('admin.redirects.import.store') }}">
            @csrf

            <div class="space-y-6 lg:col-span-2 lg:col-start-1 lg:row-start-1">
                <x-admin.form-section icon="link" title="Daftar Redirect" description="Format: alamat lama, lalu alamat baru, dipisah spasi, tab, koma, atau tanda ->. Baris berawalan # dianggap catatan.">
                    <div>
                        <label for="lines" class="block text-sm font-medium text-slate-700">Daftar<span class="text-[#E5352E]" aria-hidden="true"> *</span></label>
                        <textarea id="lines" name="lines" rows="14" spellcheck="false" placeholder="/2024/05/judul-lama -> /id/berita/judul-baru&#10;https://decaa.id/produk-lama/ /id/pilar-bisnis"
                                  class="mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 font-mono text-xs leading-relaxed text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]">{{ old('lines') }}</textarea>
                        @error('lines')<p class="mt-1.5 text-sm text-[#E5352E]">{{ $message }}</p>@enderror
                        <p class="mt-1.5 text-xs text-slate-500">Maksimal {{ $max }} baris per impor. Alamat lama yang sudah punya aturan akan diperbarui.</p>
                    </div>
                    <x-admin.select name="status_code" label="Jenis redirect" :options="\App\Models\UrlRedirect::CODES" :value="301" />
                </x-admin.form-section>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:col-start-3 lg:row-start-1">
                <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                    <div class="px-6 pb-5 pt-6 text-sm leading-relaxed text-slate-600">
                        <p class="font-semibold text-slate-800">Tips</p>
                        <p class="mt-2">Untuk 171 berita WordPress, tidak perlu mengetik: jalankan <code>php artisan redirects:import-wp</code>.</p>
                        <p class="mt-2">Baris yang tidak sah dilewati dan dilaporkan setelah impor selesai.</p>
                    </div>
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <x-admin.button type="submit" class="w-full">Impor</x-admin.button>
                        <x-admin.button variant="ghost" class="w-full" :href="route('admin.redirects.index')">Batal</x-admin.button>
                    </div>
                </section>
            </aside>
        </form>
    </div>
@endsection
