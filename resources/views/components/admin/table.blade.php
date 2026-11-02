{{--
    Pembungkus tabel (gulir mendatar di HP). Slot "head" berisi <tr> judul kolom.
    <x-admin.table>
        <x-slot name="head"><tr><th scope="col" class="px-4 py-3">Nama</th></tr></x-slot>
        <tr><td class="px-4 py-3">...</td></tr>
    </x-admin.table>
    Untuk keadaan kosong, taruh <x-admin.empty> di luar tabel (@if ($rows->isEmpty())).
--}}
<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(16,24,40,0.12)] ring-1 ring-slate-900/5']) }}>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            @isset($head)
                <thead class="bg-slate-50/80 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                    {{ $head }}
                </thead>
            @endisset
            <tbody class="divide-y divide-slate-100 text-slate-700">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>