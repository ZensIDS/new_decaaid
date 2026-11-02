{{--
    Kartu putih. Judul/deskripsi opsional, slot "actions" untuk tombol di kanan judul.
    <x-admin.card title="Aktivitas" description="..."> ... </x-admin.card>
    flush = tanpa padding isi (untuk tabel atau daftar yang menempel ke tepi).
--}}
@props(['title' => null, 'description' => null, 'flush' => false])

<section {{ $attributes->merge(['class' => 'rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(16,24,40,0.12)] ring-1 ring-slate-900/5']) }}>
    @if ($title || isset($actions))
        <header class="flex items-start justify-between gap-4 px-6 pb-2 pt-5">
            <div class="min-w-0">
                @if ($title)<h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>@endif
                @if ($description)<p class="mt-0.5 text-sm text-slate-500">{{ $description }}</p>@endif
            </div>
            @isset($actions)<div class="shrink-0">{{ $actions }}</div>@endisset
        </header>
    @endif

    <div @class(['p-6' => ! $flush, 'pb-2' => $flush])>{{ $slot }}</div>
</section>