{{--
    Notifikasi singkat. Otomatis menampilkan session('status') (hijau) dan session('error') (merah).
    Dari JavaScript/Alpine:  $dispatch('toast', { type: 'success', text: 'Tersimpan.' })
    Dipasang sekali di layout admin.
--}}
@php
    $toasts = array_values(array_filter([
        ['type' => 'success', 'text' => session('status')],
        ['type' => 'error', 'text' => session('error')],
    ], fn ($t) => filled($t['text'])));
@endphp

<div x-data="{
        items: @js($toasts),
        seq: 0,
        add(t) {
            t.id = ++this.seq;
            this.items.push(t);
            setTimeout(() => this.remove(t.id), 6000);
        },
        remove(id) { this.items = this.items.filter(i => i.id !== id); },
     }"
     x-init="items = items.map(i => ({ ...i, id: ++seq })); items.forEach(i => setTimeout(() => remove(i.id), 6000))"
     @toast.window="add($event.detail)"
     class="pointer-events-none fixed inset-x-0 top-4 z-[60] flex flex-col items-center gap-2 px-4 sm:items-end sm:pr-6"
     aria-live="polite">
    <template x-for="t in items" :key="t.id">
        <div x-transition role="status"
             :class="t.type === 'error' ? 'bg-red-50 text-red-800 ring-red-200' : 'bg-emerald-50 text-emerald-800 ring-emerald-200'"
             class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-2xl px-4 py-3.5 text-sm font-medium shadow-xl ring-1">
            <span class="flex-1" x-text="t.text"></span>
            <button type="button" @click="remove(t.id)" aria-label="Tutup" class="-mr-1 shrink-0 opacity-60 hover:opacity-100">
                <x-admin.icon name="close" class="h-4 w-4" />
            </button>
        </div>
    </template>
</div>