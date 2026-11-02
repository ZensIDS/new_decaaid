{{--
    Dialog. Dibuka dengan event:
      @click="$dispatch('open-modal', 'nama')"                       (teks)
      @click="$dispatch('open-modal', { name: 'nama', action: url })"  (membawa data, dibaca lewat `payload`)
    Ditutup dengan tombol Batal / Esc / klik latar.
    Slot "footer" berisi tombol aksi. Fokus keyboard dikunci di dalam dialog (x-focustrap).
--}}
@props(['name', 'title', 'maxWidth' => 'max-w-lg'])

<div x-data="{ open: false, payload: {} }"
     x-on:open-modal.window="if (($event.detail.name ?? $event.detail) === '{{ $name }}') { payload = typeof $event.detail === 'object' ? $event.detail : {}; open = true }"
     x-on:close-modal.window="open = false"
     x-on:keydown.escape.window="open = false"
     x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
     role="dialog" aria-modal="true" aria-labelledby="modal-{{ $name }}-title">

    <div x-show="open" x-transition.opacity @click="open = false" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

    <div x-show="open" x-transition x-focustrap
         class="relative w-full {{ $maxWidth }} rounded-3xl bg-white shadow-2xl ring-1 ring-slate-900/5">
        <div class="flex items-start justify-between gap-4 px-6 pt-6">
            <h2 id="modal-{{ $name }}-title" class="text-lg font-bold text-slate-900">{{ $title }}</h2>
            <button type="button" @click="open = false" aria-label="Tutup"
                    class="-mr-2 -mt-1 rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <x-admin.icon name="close" class="h-5 w-5" />
            </button>
        </div>

        <div class="px-6 py-4 text-sm text-slate-600">{{ $slot }}</div>

        @isset($footer)
            <div class="flex flex-col-reverse gap-2 border-t border-slate-100 px-6 py-4 sm:flex-row sm:justify-end">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>