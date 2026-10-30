{{--
    Paginasi sederhana.
    <x-ui.pagination :current="2" :last="9" pattern="?page=:page" />
    :page pada pattern diganti nomor halaman.
--}}
@props(['current' => 1, 'last' => 1, 'pattern' => '?page=:page'])

@php
    $id = app()->getLocale() === 'id';
    $url = fn (int $p) => str_replace(':page', $p, $pattern);

    // Jendela halaman: 1 ... (current-1) current (current+1) ... last
    $pages = collect([1, $last, $current - 1, $current, $current + 1])
        ->filter(fn ($p) => $p >= 1 && $p <= $last)->unique()->sort()->values();

    $item = 'flex h-10 min-w-10 items-center justify-center rounded-lg px-3 text-sm font-semibold transition';
@endphp

@if ($last > 1)
    <nav aria-label="{{ $id ? 'Paginasi' : 'Pagination' }}" {{ $attributes }}>
        <ul class="flex flex-wrap items-center justify-center gap-1.5">
            <li>
                @if ($current > 1)
                    <a href="{{ $url($current - 1) }}" class="{{ $item }} text-slate-600 hover:bg-brand-50 hover:text-brand-700">
                        <x-ui.icon name="chevron-left" class="h-4 w-4" />
                        <span class="sr-only">{{ $id ? 'Sebelumnya' : 'Previous' }}</span>
                    </a>
                @endif
            </li>

            @foreach ($pages as $p)
                @if (! $loop->first && $p - $pages[$loop->index - 1] > 1)
                    <li class="px-1 text-slate-400" aria-hidden="true">…</li>
                @endif
                <li>
                    @if ($p === $current)
                        <span aria-current="page" class="{{ $item }} bg-brand-700 text-white">{{ $p }}</span>
                    @else
                        <a href="{{ $url($p) }}" class="{{ $item }} text-slate-600 hover:bg-brand-50 hover:text-brand-700">{{ $p }}</a>
                    @endif
                </li>
            @endforeach

            <li>
                @if ($current < $last)
                    <a href="{{ $url($current + 1) }}" class="{{ $item }} text-slate-600 hover:bg-brand-50 hover:text-brand-700">
                        <x-ui.icon name="chevron-right" class="h-4 w-4" />
                        <span class="sr-only">{{ $id ? 'Berikutnya' : 'Next' }}</span>
                    </a>
                @endif
            </li>
        </ul>
    </nav>
@endif
