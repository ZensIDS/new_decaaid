{{-- B3p: kode verifikasi mesin pencari + skrip analitik (hanya di lingkungan production). Diatur di admin Pengaturan Sistem. --}}
@php
    $__sys = \App\Support\SystemSettings::get();
    $__a   = $__sys['analytics'];
    $__v   = $__sys['verification'];
@endphp
@if (filled($__v['google']))
    <meta name="google-site-verification" content="{{ $__v['google'] }}">
@endif
@if (filled($__v['bing']))
    <meta name="msvalidate.01" content="{{ $__v['bing'] }}">
@endif
@if (app()->environment('production') && filled($__a['id']))
    @if ($__a['provider'] === 'ga4')
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $__a['id'] }}"></script>
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config',@js($__a['id']),{anonymize_ip:true});</script>
    @elseif ($__a['provider'] === 'plausible')
        <script defer data-domain="{{ $__a['id'] }}" src="{{ $__a['script_url'] ?: 'https://plausible.io/js/script.js' }}"></script>
    @elseif ($__a['provider'] === 'umami')
        <script defer src="{{ $__a['script_url'] ?: 'https://cloud.umami.is/script.js' }}" data-website-id="{{ $__a['id'] }}"></script>
    @endif
@endif
