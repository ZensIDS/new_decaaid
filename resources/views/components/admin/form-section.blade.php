{{--
    Kartu bagian form dengan ikon bergradasi. Dipakai di halaman tambah/ubah.
    <x-admin.form-section icon="user-circle" title="Informasi Akun" description="..."> ...isian... </x-admin.form-section>
--}}
@props(['icon' => 'document', 'title', 'description' => null])

<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5']) }}>
    <header class="flex items-center gap-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-6 py-4 sm:px-8">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#1B3590] to-[#4A6CF0] text-white shadow-md shadow-blue-900/20">
            <x-admin.icon :name="$icon" class="h-5 w-5" />
        </span>
        <div class="min-w-0">
            <h2 class="text-base font-bold text-slate-900">{{ $title }}</h2>
            @if ($description)<p class="mt-0.5 text-sm text-slate-500">{{ $description }}</p>@endif
        </div>
    </header>
    <div class="space-y-5 px-6 py-6 sm:px-8 sm:py-7">{{ $slot }}</div>
</section>