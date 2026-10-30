@extends('layouts.app')

@section('title', config('site.name'))

@section('content')
    <section class="container py-24">
        <p class="text-sm font-semibold uppercase tracking-widest text-accent-500">{{ $pageKey }}</p>
        <h1 class="mt-3 text-3xl md:text-5xl">{{ t('setup.placeholder_title') }}</h1>
        <p class="mt-4 max-w-xl">{{ t('setup.placeholder_text') }}</p>

        <div class="mt-8 flex gap-3 text-sm font-semibold">
            <a href="{{ lroute('home') }}" class="rounded-lg bg-brand-700 px-4 py-2 text-white hover:bg-brand-800">Home</a>
            @foreach (config('site.locales') as $loc)
                <a href="{{ switch_url($loc) }}"
                   class="rounded-lg border border-slate-300 px-4 py-2 hover:border-brand-700 hover:text-brand-700">
                    {{ config('site.locale_labels.'.$loc) }}
                </a>
            @endforeach
        </div>
    </section>
@endsection