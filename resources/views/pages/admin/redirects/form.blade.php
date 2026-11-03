@extends('layouts.admin')

@php $editing = (bool) $rule; @endphp

@section('title', $editing ? 'Ubah Redirect' : 'Tambah Redirect')

@section('content')
    <x-admin.page-header :title="$editing ? 'Ubah Redirect' : 'Tambah Redirect'" description="Alamat lama yang tidak ditemukan akan dialihkan ke alamat tujuan."
                         :breadcrumbs="[['label' => 'Situs'], ['label' => 'SEO & Redirect', 'url' => route('admin.redirects.index')], ['label' => $editing ? 'Ubah' : 'Tambah']]" />

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <form method="POST" novalidate class="contents" action="{{ $editing ? route('admin.redirects.update', $rule) : route('admin.redirects.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="space-y-6 lg:col-span-2 lg:col-start-1 lg:row-start-1">
                <x-admin.form-section icon="link" title="Alamat" description="Boleh path (/2024/05/judul-lama) atau URL lengkap; bagian domain, query, dan garis miring akhir diabaikan, huruf besar-kecil tidak dibedakan.">
                    <x-admin.input name="from_path" label="Alamat lama" required :value="$rule?->from_path" placeholder="/2024/05/judul-lama" autocomplete="off"
                                   hint="Hanya dipakai bila alamat ini tidak ditemukan di situs (404). Halaman yang masih ada tidak pernah dialihkan." />
                    <x-admin.input name="to_path" label="Alamat tujuan" required :value="$rule?->to_path" placeholder="/id/berita/judul-baru" autocomplete="off"
                                   hint="Halaman di situs ini (diawali /) atau situs lain (https://...)." />
                </x-admin.form-section>

                <x-admin.form-section icon="document" title="Pengaturan" description="Gunakan 301 (permanen) untuk URL lama yang sudah pindah selamanya; 302 hanya untuk pengalihan sementara.">
                    <x-admin.select name="status_code" label="Jenis redirect" :options="\App\Models\UrlRedirect::CODES" :value="$rule?->status_code ?? 301" />
                    <x-admin.input name="note" label="Catatan (opsional)" :value="$rule?->note" maxlength="200" hint="Hanya untuk kamu, tidak tampil di situs." />
                    <x-admin.toggle name="is_active" label="Aktif" description="Redirect nonaktif tidak dijalankan." :checked="$rule ? $rule->is_active : true" />
                </x-admin.form-section>
            </div>

            <aside class="space-y-6 lg:sticky lg:top-24 lg:col-start-3 lg:row-start-1">
                <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                    @if ($editing)
                        <div class="px-6 pb-5 pt-6">
                            <dl class="space-y-3 text-sm">
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Dipakai</dt><dd class="font-medium text-slate-700">{{ number_format($rule->hits, 0, ',', '.') }}&times;</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Terakhir dipakai</dt><dd class="font-medium text-slate-700">{{ $rule->last_hit_at?->translatedFormat('j M Y, H:i') ?? '-' }}</dd></div>
                                <div class="flex items-center justify-between gap-3"><dt class="text-slate-500">Diubah oleh</dt><dd class="text-right font-medium text-slate-700">{{ $rule->editor?->name ?? '-' }}</dd></div>
                            </dl>
                        </div>
                    @else
                        <p class="px-6 pb-2 pt-6 text-sm leading-relaxed text-slate-600">Setelah disimpan, cek dengan membuka alamat lama di jendela baru: browser harus langsung pindah ke alamat tujuan.</p>
                    @endif
                    <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <x-admin.button type="submit" class="w-full">Simpan</x-admin.button>
                        <x-admin.button variant="ghost" class="w-full" :href="route('admin.redirects.index')">Batal</x-admin.button>
                    </div>
                </section>
            </aside>
        </form>
    </div>
@endsection
