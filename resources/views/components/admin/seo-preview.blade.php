@props([
    'titleField' => 'title',      // nama kolom judul biasa (dipakai bila Judul SEO kosong)
    'descField'  => null,         // nama kolom ringkasan (dipakai bila Deskripsi SEO kosong); null = tidak ada
    'image'      => null,         // path gambar tersimpan (relatif ke public/); kosong = gambar bawaan situs
    'path'       => null,         // contoh alamat tampil di hasil pencarian, mis. "/id/berita/slug"
])

@php
    $site = (string) config('site.name');
    $host = parse_url(config('app.url'), PHP_URL_HOST) ?: $site;
    $img  = $image ?: \App\Support\SeoMeta::defaultImage();
    $src  = $img && file_exists(public_path($img)) ? asset($img) : null;
@endphp

{{-- Pratinjau langsung (B3n): membaca isian form di sekitarnya, jadi cukup diletakkan di dalam <form>.
     Aturan situs: Judul/Deskripsi SEO > judul/ringkasan biasa; EN kosong memakai ID. Akhiran judul bisa sedikit berbeda per halaman. --}}
<div x-data="{
        l: 'id', v: { id: {}, en: {} },
        read() {
            const f = this.$el.closest('form');
            const g = n => (f && f.querySelector('[name=\'' + n + '\']') ? f.querySelector('[name=\'' + n + '\']').value : '').trim();
            for (const k of ['id', 'en']) {
                this.v[k] = { st: g('seo_title[' + k + ']'), sd: g('seo_description[' + k + ']'), t: g(@js($titleField) + '[' + k + ']'), d: @js($descField) ? g(@js($descField) + '[' + k + ']') : '' };
            }
        },
        title() { const a = this.v[this.l], b = this.v.id; return a.st || a.t || b.st || b.t || ''; },
        desc()  { const a = this.v[this.l], b = this.v.id; return a.sd || a.d || b.sd || b.d || ''; },
        full()  { const t = this.title(); return t ? t + ' | ' + @js($site) : 'Judul bawaan halaman'; },
     }"
     x-init="read()" @input.window="read()" @change.window="read()"
     class="space-y-3 rounded-xl bg-slate-50 p-4 ring-1 ring-inset ring-slate-200" aria-label="Pratinjau tampilan di pencarian dan saat dibagikan">
    <div class="flex items-center justify-between gap-3">
        <p class="text-sm font-semibold text-slate-700">Pratinjau</p>
        <div class="flex items-center gap-1 rounded-lg bg-white p-0.5 ring-1 ring-slate-200" role="group" aria-label="Bahasa pratinjau">
            <button type="button" @click="l = 'id'" :class="l === 'id' ? 'bg-blue-50 text-[#1B3590]' : 'text-slate-500'" :aria-pressed="(l === 'id').toString()" class="rounded-md px-3 py-1 text-xs font-semibold">ID</button>
            <button type="button" @click="l = 'en'" :class="l === 'en' ? 'bg-blue-50 text-[#1B3590]' : 'text-slate-500'" :aria-pressed="(l === 'en').toString()" class="rounded-md px-3 py-1 text-xs font-semibold">EN</button>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Hasil pencarian</p>
            <div class="mt-2 rounded-lg bg-white p-3 ring-1 ring-slate-200">
                <p class="truncate text-xs text-slate-600">{{ $host }}{{ $path ? ' › ' . ltrim($path, '/') : '' }}</p>
                <p class="mt-0.5 line-clamp-2 text-[17px] leading-snug text-[#1a0dab]"
                   x-text="full().length > 60 ? full().slice(0, 59) + '…' : full()"></p>
                <p class="mt-1 line-clamp-2 text-[13px] leading-snug text-slate-600"
                   x-text="desc() ? (desc().length > 160 ? desc().slice(0, 157) + '…' : desc()) : 'Deskripsi bawaan halaman'"></p>
            </div>
        </div>
        <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Kartu share</p>
            <div class="mt-2 overflow-hidden rounded-lg bg-white ring-1 ring-slate-200">
                @if ($src)
                    <img src="{{ $src }}" alt="" loading="lazy" class="aspect-[1200/630] w-full object-cover">
                @else
                    <div class="flex aspect-[1200/630] w-full items-center justify-center bg-slate-100 text-xs text-slate-400">Belum ada gambar share</div>
                @endif
                <div class="space-y-0.5 border-t border-slate-200 px-3 py-2">
                    <p class="truncate text-[11px] uppercase text-slate-500">{{ $host }}</p>
                    <p class="line-clamp-2 text-sm font-semibold leading-snug text-slate-900" x-text="full()"></p>
                    <p class="line-clamp-2 text-xs leading-snug text-slate-500" x-text="desc() || 'Deskripsi bawaan halaman'"></p>
                </div>
            </div>
        </div>
    </div>
    <p class="text-[11px] text-slate-400">Pendekatan saja: mesin pencari dan aplikasi bebas memotong sendiri. Gambar sesuai yang sudah tersimpan (gambar utama, atau gambar bawaan situs).</p>
</div>
