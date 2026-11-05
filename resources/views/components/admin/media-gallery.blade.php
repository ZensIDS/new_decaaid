{{--
    Galeri foto dari Media Library (B3g, v79): banyak foto, bisa diurutkan dan dihapus; menyimpan array PATH sebagai input tersembunyi gallery[0], gallery[1], ...
    <x-admin.media-gallery name="gallery" :value="$paths" :max="12" label="Foto galeri" hint="..." />
    Di jendela pilih: cari, centang beberapa foto lalu Tambahkan, atau unggah foto baru (langsung masuk Media Library dan terpilih).
    Daftar kosong berarti galeri dikosongkan saat disimpan.
--}}
@props(['name' => 'gallery', 'value' => [], 'max' => 12, 'label' => 'Galeri foto', 'hint' => null])

@php
    $paths = collect((array) old($name, $value))->filter(fn ($p) => is_string($p) && $p !== '')->unique()->take($max)->values();
    $items = $paths->map(fn ($p) => ['path' => $p, 'url' => asset($p), 'thumb' => asset($p)])->all();
    $uid   = 'mg-' . substr(md5($name), 0, 8);
@endphp

<div x-data="mediaGallery({ items: @js($items), max: {{ (int) $max }}, listUrl: @js(route('admin.media.picker')), uploadUrl: @js(route('admin.media.picker.upload')), token: @js(csrf_token()) })"
     @keydown.escape.window="open = false" class="space-y-3">
    <div class="flex items-end justify-between gap-3">
        <span class="block text-sm font-medium text-slate-700">{{ $label }}</span>
        <span class="text-xs tabular-nums text-slate-500" x-text="items.length + ' / ' + max + ' foto'"></span>
    </div>

    <template x-for="(it, i) in items" :key="it.path">
        <input type="hidden" :name="'{{ $name }}[' + i + ']'" :value="it.path">
    </template>

    <p x-show="items.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-white/70 px-5 py-6 text-center text-sm text-slate-500">
        Belum ada foto galeri. Halaman proyek tampil tanpa galeri.
    </p>

    <ul x-show="items.length > 0" class="grid grid-cols-2 gap-3 sm:grid-cols-3">
        <template x-for="(it, i) in items" :key="it.path">
            <li class="overflow-hidden rounded-xl bg-white ring-1 ring-slate-200">
                <div class="relative aspect-[4/3] bg-slate-100">
                    <img :src="it.thumb || it.url" alt="" loading="lazy" class="h-full w-full object-cover">
                    <span class="absolute left-1.5 top-1.5 rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-semibold text-slate-600 ring-1 ring-slate-200" x-text="i + 1"></span>
                </div>
                <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50/60 px-1 py-1">
                    <div class="flex items-center">
                        <button type="button" @click="move(i, -1)" :disabled="i === 0" title="Geser ke depan" aria-label="Geser foto ke depan"
                                class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 disabled:opacity-30">
                            <x-admin.icon name="arrow-left" class="h-4 w-4" />
                        </button>
                        <button type="button" @click="move(i, 1)" :disabled="i === items.length - 1" title="Geser ke belakang" aria-label="Geser foto ke belakang"
                                class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 disabled:opacity-30">
                            <x-admin.icon name="arrow-right" class="h-4 w-4" />
                        </button>
                    </div>
                    <button type="button" @click="remove(i)" title="Lepas foto dari galeri" aria-label="Lepas foto dari galeri"
                            class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-[#E5352E]">
                        <x-admin.icon name="trash" class="h-4 w-4" />
                    </button>
                </div>
            </li>
        </template>
    </ul>

    <div class="flex flex-wrap items-center gap-3">
        <button type="button" @click="openPicker()" :disabled="items.length >= max"
                class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50">
            <x-admin.icon name="plus" class="h-4 w-4" /> Tambah foto dari Media Library
        </button>
        @if ($hint)<p class="text-xs text-slate-500">{{ $hint }}</p>@endif
    </div>

    {{-- Jendela pilih (banyak foto) --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="{{ $uid }}-title">
        <div x-show="open" x-transition.opacity @click="open = false" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
        <div x-show="open" x-transition x-focustrap class="relative flex max-h-[85vh] w-full max-w-3xl flex-col rounded-3xl bg-white shadow-2xl ring-1 ring-slate-900/5">
            <div class="flex items-start justify-between gap-4 px-6 pt-6">
                <div>
                    <h2 id="{{ $uid }}-title" class="text-lg font-bold text-slate-900">Pilih Foto Galeri</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Centang beberapa foto, lalu klik Tambahkan. Sisa tempat: <span x-text="Math.max(0, max - items.length - picked.length)"></span>.</p>
                </div>
                <button type="button" @click="open = false" aria-label="Tutup" class="-mr-2 -mt-1 rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    <x-admin.icon name="close" class="h-5 w-5" />
                </button>
            </div>

            <div class="grid gap-3 px-6 pt-4 sm:grid-cols-2">
                <input type="search" x-model="q" @input.debounce.350ms="load(true)" @keydown.enter.prevent placeholder="Cari nama gambar" aria-label="Cari gambar"
                       class="block w-full rounded-xl border-0 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]">
                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-blue-50 px-4 py-2.5 text-sm font-semibold text-[#1B3590] hover:bg-blue-100">
                    <x-admin.icon name="plus" class="h-4 w-4" />
                    <span x-text="uploading ? 'Mengunggah...' : 'Unggah foto baru'"></span>
                    <input type="file" multiple accept="image/jpeg,image/png,image/webp" class="sr-only" :disabled="uploading" @change="upload($event)">
                </label>
            </div>
            <p x-show="error" x-text="error" class="px-6 pt-2 text-sm text-[#E5352E]"></p>

            <div class="min-h-0 flex-1 overflow-y-auto px-6 py-4">
                <p x-show="!loading && list.length === 0" class="rounded-2xl border border-dashed border-slate-300 px-5 py-8 text-center text-sm text-slate-500">
                    Belum ada gambar yang cocok. Unggah foto baru, atau minta Admin menjalankan <code>php artisan media:sync</code>.
                </p>
                <ul class="grid grid-cols-3 gap-3 sm:grid-cols-4">
                    <template x-for="m in list" :key="m.path">
                        <li>
                            <button type="button" @click="toggle(m)" :disabled="has(m.path)" :aria-pressed="isPicked(m).toString()" :aria-label="'Pilih ' + m.name"
                                    class="group relative block w-full overflow-hidden rounded-xl bg-white ring-1 ring-slate-200 transition hover:ring-2 hover:ring-[#1B3590] disabled:cursor-not-allowed disabled:opacity-40"
                                    :class="isPicked(m) ? 'ring-2 ring-[#1B3590]' : ''">
                                <span class="flex aspect-square items-center justify-center bg-slate-50">
                                    <img :src="m.thumb" :alt="m.alt || m.name" loading="lazy" class="max-h-full max-w-full object-contain">
                                </span>
                                <span class="block truncate border-t border-slate-100 px-2 py-1.5 text-left text-[11px] font-medium text-slate-700" x-text="has(m.path) ? 'Sudah di galeri' : m.name"></span>
                                <span x-show="isPicked(m)" class="absolute right-1.5 top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-[#1B3590] text-white" aria-hidden="true">
                                    <x-admin.icon name="check" class="h-3 w-3" />
                                </span>
                            </button>
                        </li>
                    </template>
                </ul>
                <div class="mt-4 text-center" x-show="more">
                    <button type="button" @click="load(false)" :disabled="loading" class="rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-50 disabled:opacity-50">Muat lebih banyak</button>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-100 px-6 py-4">
                <button type="button" @click="open = false" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="button" @click="addPicked()" :disabled="picked.length === 0"
                        class="rounded-xl bg-[#1B3590] px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-[#162b75] disabled:cursor-not-allowed disabled:opacity-50"
                        x-text="picked.length ? 'Tambahkan ' + picked.length + ' foto' : 'Tambahkan'"></button>
            </div>
        </div>
    </div>
</div>

@once
    <script>
        function mediaGallery(cfg) {
            return {
                items: cfg.items || [],
                max: cfg.max || 12,
                open: false,
                q: '',
                list: [],
                more: false,
                page: 1,
                loading: false,
                uploading: false,
                error: '',
                picked: [],

                openPicker() {
                    this.picked = [];
                    this.error = '';
                    this.open = true;
                    if (this.list.length === 0) this.load(true);
                },

                has(path) { return this.items.some((i) => i.path === path); },
                isPicked(m) { return this.picked.some((p) => p.path === m.path); },
                free() { return this.max - this.items.length - this.picked.length; },

                toggle(m) {
                    if (this.has(m.path)) return;
                    const k = this.picked.findIndex((p) => p.path === m.path);
                    if (k >= 0) { this.picked.splice(k, 1); return; }
                    if (this.free() <= 0) { this.error = 'Galeri penuh (maksimal ' + this.max + ' foto).'; return; }
                    this.error = '';
                    this.picked.push(m);
                },

                addPicked() {
                    this.picked.forEach((m) => this.items.push({ path: m.path, url: m.url, thumb: m.thumb }));
                    this.picked = [];
                    this.open = false;
                },

                remove(i) { this.items.splice(i, 1); },

                move(i, dir) {
                    const j = i + dir;
                    if (j < 0 || j >= this.items.length) return;
                    const moved = this.items.splice(i, 1)[0];
                    this.items.splice(j, 0, moved);
                },

                async load(reset) {
                    if (reset) { this.page = 1; this.list = []; }
                    this.loading = true;
                    try {
                        const res = await fetch(cfg.listUrl + '?q=' + encodeURIComponent(this.q) + '&page=' + this.page, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        const json = await res.json();
                        this.list = this.list.concat(json.data || []);
                        this.more = !!json.more;
                        this.page += 1;
                        if (json.notReady) this.error = 'Media Library belum siap: jalankan php artisan migrate.';
                    } catch (e) {
                        this.error = 'Gagal memuat gambar. Muat ulang halaman lalu coba lagi.';
                    } finally {
                        this.loading = false;
                    }
                },

                async upload(e) {
                    const files = Array.from(e.target.files || []);
                    if (files.length === 0) return;
                    this.uploading = true;
                    this.error = '';
                    let failed = 0;

                    for (const file of files) {
                        if (file.size > 5 * 1024 * 1024) { failed++; this.error = 'Ada foto lebih dari 5 MB yang dilewati.'; continue; }
                        const body = new FormData();
                        body.append('file', file);
                        try {
                            const res = await fetch(cfg.uploadUrl, {
                                method: 'POST',
                                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.token, 'X-Requested-With': 'XMLHttpRequest' },
                                body: body,
                            });
                            const json = await res.json();
                            if (!res.ok) throw new Error((json.errors && Object.values(json.errors)[0][0]) || json.message || 'Gagal mengunggah.');
                            this.list.unshift(json);
                            if (this.free() > 0) this.picked.push(json);
                        } catch (err) {
                            failed++;
                            this.error = err.message || 'Gagal mengunggah foto.';
                        }
                    }

                    this.uploading = false;
                    e.target.value = '';
                    if (failed === 0) this.$dispatch('toast', { type: 'success', text: 'Foto diunggah dan dipilih. Klik Tambahkan untuk memasukkannya.' });
                },
            };
        }
    </script>
@endonce
