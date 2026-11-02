{{--
    Judul halaman + breadcrumb + tombol aksi (slot "actions").
    <x-admin.page-header title="Pengguna" description="..." :breadcrumbs="[['label' => 'Sistem'], ['label' => 'Pengguna']]">
        <x-slot name="actions"> <x-admin.button icon="plus">Tambah</x-admin.button> </x-slot>
    </x-admin.page-header>
    Beranda (Dashboard) otomatis jadi crumb pertama. Item terakhir = halaman sekarang (tanpa tautan).
--}}
@props(['title', 'description' => null, 'breadcrumbs' => []])

<div class="mb-6">
    @if ($breadcrumbs)
        <nav aria-label="Breadcrumb" class="mb-2">
            <ol class="flex flex-wrap items-center gap-1.5 text-xs text-slate-500">
                <li><a href="{{ route('admin.dashboard') }}" class="hover:text-[#1B3590]">Dashboard</a></li>
                @foreach ($breadcrumbs as $crumb)
                    <li class="flex items-center gap-1.5">
                        <span aria-hidden="true">/</span>
                        @if (! $loop->last && ! empty($crumb['url']))
                            <a href="{{ $crumb['url'] }}" class="hover:text-[#1B3590]">{{ $crumb['label'] }}</a>
                        @else
                            <span @if ($loop->last) aria-current="page" class="font-medium text-slate-700" @endif>{{ $crumb['label'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    @endif

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $title }}</h1>
            @if ($description)<p class="mt-1.5 text-sm text-slate-600">{{ $description }}</p>@endif
        </div>
        @isset($actions)<div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>@endisset
    </div>
</div>