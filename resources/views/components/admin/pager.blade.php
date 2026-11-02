{{--
    Paginasi sederhana untuk tabel admin (tanpa bergantung pada view bawaan Laravel).
    <x-admin.pager :paginator="$users" />
    Tampil hanya bila lebih dari satu halaman. Filter di query string ikut terbawa (withQueryString di controller).
--}}
@props(['paginator'])

@if ($paginator->total() > 0)
    <nav aria-label="Halaman" class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm text-slate-600">
        <p>Menampilkan {{ $paginator->firstItem() }}&ndash;{{ $paginator->lastItem() }} dari {{ $paginator->total() }}</p>

        @if ($paginator->hasPages())
            <div class="flex items-center gap-2">
                @if ($paginator->onFirstPage())
                    <span class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3.5 py-2 text-xs font-semibold text-slate-400" aria-disabled="true">
                        <x-admin.icon name="arrow-left" class="h-4 w-4" /> Sebelumnya
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                       class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 hover:bg-slate-50">
                        <x-admin.icon name="arrow-left" class="h-4 w-4" /> Sebelumnya
                    </a>
                @endif

                <span class="px-1 text-xs font-medium text-slate-500">Hal. {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                       class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 hover:bg-slate-50">
                        Berikutnya <x-admin.icon name="arrow-right" class="h-4 w-4" />
                    </a>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3.5 py-2 text-xs font-semibold text-slate-400" aria-disabled="true">
                        Berikutnya <x-admin.icon name="arrow-right" class="h-4 w-4" />
                    </span>
                @endif
            </div>
        @endif
    </nav>
@endif