{{--
    Pemilih gambar dari Media Library (B3b). Menyimpan PATH gambar (mis. storage/media/2026/11/abc.webp) ke input tersembunyi.
    <x-admin.media-picker name="profile[chairman][photo]" :value="$path" label="Foto Chairman" hint="..." ratio="aspect-[4/5]" />
    Mode ringkas (:compact="true"): hanya tombol; pratinjau dikelola formulir induk lewat event `media-chosen` ({ name, path, url }).
    Di dalam jendela pilih: cari gambar, atau unggah gambar baru (langsung masuk Media Library dan terpilih).
--}}
@props(['name', 'value' => null, 'label' => 'Gambar', 'hint' => null, 'ratio' => 'aspect-[4/3]', 'logo' => false, 'compact' => false])

@php
    $oldKey  = rtrim(str_replace(['[', ']'], ['.', ''], $name), '.');
    $initial = (string) old($oldKey, $value);
    $uid     = 'mp-' . substr(md5($name), 0, 8);
@endphp

<div x-data="mediaPicker({ path: @js($initial), url: @js($initial !== '' ? asset($initial) : ''), listUrl: @js(route('admin.media.picker')), uploadUrl: @js(route('admin.media.picker.upload')), token: @js(csrf_token()), logo: @js((bool) $logo), name: @js($name) })"
     @keydown.escape.window="open = false" class="space-y-2">
    @if ($label !== '')<span class="block text-sm font-medium text-slate-700">{{ $label }}</span>@endif
    <input type="hidden" name="{{ $name }}" :value="path">

    <div class="flex flex-wrap items-start gap-4">
        @unless ($compact)
        <div class="{{ $ratio }} w-40 shrink-0 overflow-hidden rounded-2xl bg-slate-100 ring-1 ring-slate-200">
            <template x-if="url"><img :src="url" alt="" class="h-full w-full object-cover" x-on:error="url = ''"></template>
            <div x-show="!url" class="flex h-full flex-col items-center justify-center gap-1 px-2 text-center text-slate-400">
                <x-admin.icon name="photo" class="h-7 w-7" />
                <span class="text-[11px] leading-tight">Belum dipilih</span>
            </div>
        </div>
        @endunless
        <div class="flex flex-col items-start gap-2">
            <button type="button" @click="openPicker()" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 hover:bg-slate-50">
                <x-admin.icon name="photo" class="h-4 w-4" /> Pilih dari Media Library
            </button>
            @unless ($compact)
                <button type="button" x-show="path" @click="clear()" class="text-sm font-semibold text-[#E5352E] hover:underline">Lepas gambar</button>
            @endunless
            @if ($hint)<p class="max-w-sm text-xs text-slate-500">{{ $hint }}</p>@endif
        </div>
    </div>

    {{-- Jendela pilih --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="{{ $uid }}-title">
        <div x-show="open" x-transition.opacity @click="open = false" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>
        <div x-show="open" x-transition x-focustrap class="relative flex max-h-[85vh] w-full max-w-3xl flex-col rounded-3xl bg-white shadow-2xl ring-1 ring-slate-900/5">
            <div class="flex items-start justify-between gap-4 px-6 pt-6">
                <h2 id="{{ $uid }}-title" class="text-lg font-bold text-slate-900">Pilih Gambar</h2>
                <button type="button" @click="open = false" aria-label="Tutup" class="-mr-2 -mt-1 rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    <x-admin.icon name="close" class="h-5 w-5" />
                </button>
            </div>

            <div class="grid gap-3 px-6 pt-4 sm:grid-cols-2">
                <input type="search" x-model="q" @input.debounce.350ms="load(true)" @keydown.enter.prevent placeholder="Cari nama gambar" aria-label="Cari gambar"
                       class="block w-full rounded-xl border-0 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-inset focus:ring-[#1B3590]">
                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-blue-50 px-4 py-2.5 text-sm font-semibold text-[#1B3590] hover:bg-blue-100">
                    <x-admin.icon name="plus" class="h-4 w-4" />
                    <span x-text="uploading ? 'Mengunggah...' : 'Unggah gambar baru'"></span>
                    <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" :disabled="uploading" @change="upload($event)">
                </label>
            </div>
            <p x-show="error" x-text="error" class="px-6 pt-2 text-sm text-[#E5352E]"></p>

            <div class="min-h-0 flex-1 overflow-y-auto px-6 py-4">
                <p x-show="!loading && items.length === 0" class="rounded-2xl border border-dashed border-slate-300 px-5 py-8 text-center text-sm text-slate-500">
                    Belum ada gambar yang cocok. Unggah gambar baru, atau minta Admin menjalankan <code>php artisan media:sync</code>.
                </p>
                <ul class="grid grid-cols-3 gap-3 sm:grid-cols-4">
                    <template x-for="m in items" :key="m.path">
                        <li>
                            <button type="button" @click="choose(m)" :aria-label="'Pilih ' + m.name"
                                    class="group block w-full overflow-hidden rounded-xl bg-white ring-1 ring-slate-200 transition hover:ring-2 hover:ring-[#1B3590]"
                                    :class="m.path === path ? 'ring-2 ring-[#1B3590]' : ''">
                                <span class="flex aspect-square items-center justify-center bg-slate-50">
                                    <img :src="m.thumb" :alt="m.alt || m.name" loading="lazy" class="max-h-full max-w-full object-contain">
                                </span>
                                <span class="block truncate border-t border-slate-100 px-2 py-1.5 text-left text-[11px] font-medium text-slate-700" x-text="m.name"></span>
                            </button>
                        </li>
                    </template>
                </ul>
                <div class="mt-4 text-center" x-show="more">
                    <button type="button" @click="load(false)" :disabled="loading" class="rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-50 disabled:opacity-50">Muat lebih banyak</button>
                </div>
            </div>
        </div>
    </div>
</div>

@once
    <script>
        function mediaPicker(cfg) {
            return {
                path: cfg.path || '',
                url: cfg.url || '',
                open: false,
                q: '',
                items: [],
                more: false,
                page: 1,
                loading: false,
                uploading: false,
                error: '',

                openPicker() {
                    this.open = true;
                    if (this.items.length === 0) this.load(true);
                },

                async load(reset) {
                    if (reset) { this.page = 1; this.items = []; }
                    this.loading = true;
                    this.error = '';
                    try {
                        const res = await fetch(cfg.listUrl + '?q=' + encodeURIComponent(this.q) + '&page=' + this.page, {
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        const json = await res.json();
                        this.items = this.items.concat(json.data || []);
                        this.more = !!json.more;
                        this.page += 1;
                        if (json.notReady) this.error = 'Media Library belum siap: jalankan php artisan migrate.';
                    } catch (e) {
                        this.error = 'Gagal memuat gambar. Muat ulang halaman lalu coba lagi.';
                    } finally {
                        this.loading = false;
                    }
                },

                choose(m) {
                    this.path = m.path;
                    this.url = m.url;
                    this.open = false;
                    this.$dispatch('media-chosen', { name: cfg.name, path: m.path, url: m.url });
                },

                clear() {
                    this.path = '';
                    this.url = '';
                },

                async upload(e) {
                    const file = e.target.files[0];
                    if (!file) return;
                    if (file.size > 5 * 1024 * 1024) { this.error = 'Gambar terlalu besar (maksimal 5 MB).'; e.target.value = ''; return; }
                    this.uploading = true;
                    this.error = '';
                    const body = new FormData();
                    body.append('file', file);
                    if (cfg.logo) body.append('is_logo', '1');
                    try {
                        const res = await fetch(cfg.uploadUrl, {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': cfg.token, 'X-Requested-With': 'XMLHttpRequest' },
                            body: body,
                        });
                        const json = await res.json();
                        if (!res.ok) throw new Error((json.errors && Object.values(json.errors)[0][0]) || json.message || 'Gagal mengunggah.');
                        this.items.unshift(json);
                        this.choose(json);
                        this.$dispatch('toast', { type: 'success', text: 'Gambar diunggah dan dipilih.' });
                    } catch (err) {
                        this.error = err.message || 'Gagal mengunggah gambar.';
                    } finally {
                        this.uploading = false;
                        e.target.value = '';
                    }
                },
            };
        }
    </script>
@endonce
