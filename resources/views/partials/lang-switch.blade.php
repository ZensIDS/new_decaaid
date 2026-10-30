{{-- Switch bahasa ID | EN: mempertahankan halaman yang sedang dibuka --}}
<div class="inline-flex items-center rounded-lg border border-slate-200 p-0.5 text-xs font-bold"
     role="group" aria-label="{{ t('nav.language') }}">
    @foreach (config('site.locales') as $loc)
        @php $on = app()->getLocale() === $loc; @endphp
        <a href="{{ switch_url($loc) }}" lang="{{ $loc }}" hreflang="{{ $loc }}"
           @if ($on) aria-current="true" @endif
           class="rounded-md px-2.5 py-1.5 transition {{ $on ? 'bg-brand-700 text-white' : 'text-slate-500 hover:text-brand-700' }}">
            {{ config('site.locale_labels.'.$loc) }}
        </a>
    @endforeach
</div>