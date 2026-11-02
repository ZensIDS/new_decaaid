{{--
    Editor teks kaya ringan (tanpa pustaka tambahan). Menghasilkan HTML yang disaring ulang di server
    (App\Support\Html::clean), jadi aman walau isi ditempel dari sumber lain.
    Fitur (QA12): paragraf, subjudul H2/H3, tebal, miring, daftar, kutipan, tautan, gambar. Tanpa video dan tabel.

    <x-admin.editor name="body[id]" label="Isi berita" :value="$html" :upload="route('admin.news.upload')" />

    Memakai document.execCommand (usang tetapi didukung semua peramban). Tempel selalu sebagai teks polos
    supaya format dari Word/web tidak ikut masuk.
--}}
@props([
    'name',
    'label' => null,
    'value' => null,
    'upload' => null,
    'hint' => null,
])

@php
    $key = str_replace(['[', ']'], ['.', ''], rtrim($name, '[]'));
    $id = 'editor-'.str_replace('.', '-', $key);
    $current = old($key, $value);
    $hasError = $errors->has($key);
    $btn = 'flex h-9 min-w-[2.25rem] items-center justify-center rounded-lg px-2 text-sm font-semibold text-slate-600 transition hover:bg-white hover:text-[#1B3590] hover:shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1B3590]';
@endphp

<div x-data="{
        html: @js((string) $current),
        busy: false,
        init() {
            document.execCommand('defaultParagraphSeparator', false, 'p');
        },
        sync() { this.html = this.$refs.area.innerHTML; },
        cmd(name, value = null) {
            this.$refs.area.focus();
            document.execCommand(name, false, value);
            this.sync();
        },
        block(tag) { this.cmd('formatBlock', '<' + tag + '>'); },
        link() {
            const url = window.prompt('Alamat tautan (diawali https://)');
            if (url) this.cmd('createLink', url.trim());
        },
        paste(e) {
            const text = (e.clipboardData || window.clipboardData).getData('text/plain');
            const esc = (s) => s.replace(/\u0026/g, '\u0026amp;').replace(/</g, '\u0026lt;').replace(/>/g, '\u0026gt;');
            const lines = text.split(/\r?\n+/).map((l) => l.trim()).filter(Boolean);
            document.execCommand('insertHTML', false, lines.length > 1 ? lines.map((l) => '<p>' + esc(l) + '</p>').join('') : esc(lines[0] || ''));
            this.sync();
        },
        async image(e) {
            const file = e.target.files[0];
            e.target.value = '';
            if (!file) return;
            if (file.size > 5 * 1024 * 1024) { this.$dispatch('toast', { type: 'error', text: 'Gambar terlalu besar (maksimal 5 MB).' }); return; }
            this.busy = true;
            try {
                const body = new FormData();
                body.append('image', file);
                body.append('_token', @js(csrf_token()));
                const res = await fetch(@js($upload), { method: 'POST', body, headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal mengunggah gambar.');
                this.cmd('insertImage', '/' + data.path);
            } catch (err) {
                this.$dispatch('toast', { type: 'error', text: err.message });
            } finally { this.busy = false; }
        },
     }">
    @if ($label)
        <span id="{{ $id }}-label" class="block text-sm font-medium text-slate-700">{{ $label }}</span>
    @endif

    <input type="hidden" name="{{ $name }}" :value="html">

    <div class="mt-1.5 overflow-hidden rounded-xl border-2 bg-slate-100 shadow-sm transition {{ $hasError ? 'border-[#E5352E]' : 'border-slate-300 focus-within:border-[#1B3590] focus-within:ring-4 focus-within:ring-[#1B3590]/10' }}">
        <div class="flex flex-wrap items-center gap-0.5 border-b-2 border-slate-300 bg-slate-100 p-1.5" role="toolbar" aria-label="Format teks">
            <button type="button" class="{{ $btn }}" @click="block('p')" title="Paragraf" aria-label="Paragraf">P</button>
            <button type="button" class="{{ $btn }}" @click="block('h2')" title="Subjudul besar" aria-label="Subjudul besar">H2</button>
            <button type="button" class="{{ $btn }}" @click="block('h3')" title="Subjudul kecil" aria-label="Subjudul kecil">H3</button>
            <span class="mx-1 h-5 w-px bg-slate-300" aria-hidden="true"></span>
            <button type="button" class="{{ $btn }} font-extrabold" @click="cmd('bold')" title="Tebal" aria-label="Tebal">B</button>
            <button type="button" class="{{ $btn }} italic" @click="cmd('italic')" title="Miring" aria-label="Miring">I</button>
            <span class="mx-1 h-5 w-px bg-slate-300" aria-hidden="true"></span>
            <button type="button" class="{{ $btn }}" @click="cmd('insertUnorderedList')" title="Daftar poin" aria-label="Daftar poin">&bull; Daftar</button>
            <button type="button" class="{{ $btn }}" @click="cmd('insertOrderedList')" title="Daftar angka" aria-label="Daftar angka">1. Daftar</button>
            <button type="button" class="{{ $btn }}" @click="block('blockquote')" title="Kutipan" aria-label="Kutipan">&ldquo; &rdquo;</button>
            <span class="mx-1 h-5 w-px bg-slate-300" aria-hidden="true"></span>
            <button type="button" class="{{ $btn }}" @click="link()" title="Sisipkan tautan" aria-label="Sisipkan tautan">Tautan</button>
            <button type="button" class="{{ $btn }}" @click="cmd('unlink')" title="Lepas tautan" aria-label="Lepas tautan">Lepas</button>
            @if ($upload)
                <label class="{{ $btn }} cursor-pointer" title="Sisipkan gambar" :class="busy && 'pointer-events-none opacity-50'">
                    <span x-text="busy ? 'Mengunggah...' : 'Gambar'">Gambar</span>
                    <input type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="image($event)">
                </label>
            @endif
        </div>

        <div class="p-3">
        <div x-ref="area" x-init="$el.innerHTML = html" id="{{ $id }}" contenteditable="true" role="textbox" aria-multiline="true"
             data-placeholder="Mulai menulis di sini..."
             @if ($label) aria-labelledby="{{ $id }}-label" @endif
             @input="sync()" @blur="sync()" @paste.prevent="paste($event)"
             class="min-h-[18rem] max-w-none rounded-lg border border-slate-300 bg-white px-5 py-4 shadow-inner empty:before:pointer-events-none empty:before:text-slate-400 empty:before:content-[attr(data-placeholder)] text-[15px] leading-7 text-slate-800 focus:outline-none [&_a]:text-[#1B3590] [&_a]:underline [&_blockquote]:border-l-4 [&_blockquote]:border-slate-300 [&_blockquote]:pl-4 [&_blockquote]:italic [&_h2]:mt-5 [&_h2]:text-xl [&_h2]:font-bold [&_h3]:mt-4 [&_h3]:text-lg [&_h3]:font-bold [&_img]:my-3 [&_img]:max-h-72 [&_img]:rounded-xl [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mb-3 [&_ul]:list-disc [&_ul]:pl-6"></div>
        </div>
    </div>

    @if ($hasError)
        <p class="mt-1.5 text-sm text-[#E5352E]">{{ $errors->first($key) }}</p>
    @elseif ($hint)
        <p class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>