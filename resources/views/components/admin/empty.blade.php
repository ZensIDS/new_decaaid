{{--
    Keadaan kosong. <x-admin.empty icon="newspaper" title="Belum ada berita" text="...">  <x-admin.button>Tambah</x-admin.button> </x-admin.empty>
--}}
@props(['icon' => 'inbox', 'title', 'text' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center rounded-2xl border border-dashed border-slate-300 bg-white/70 px-6 py-12 text-center']) }}>
    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 text-[#1B3590]">
        <x-admin.icon :name="$icon" class="h-6 w-6" />
    </span>
    <h3 class="mt-4 text-sm font-semibold text-slate-900">{{ $title }}</h3>
    @if ($text)<p class="mt-1 max-w-sm text-sm text-slate-500">{{ $text }}</p>@endif
    @if (trim((string) $slot) !== '')<div class="mt-5">{{ $slot }}</div>@endif
</div>