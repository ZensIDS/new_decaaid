{{--
    Blok ajakan "Hubungi Kami" yang dipakai ulang di halaman dalam.
    <x-ui.cta-band />
--}}
@php
    $cta = \App\Support\Content::home()['cta'];
    $email = \App\Support\Content::company()['email']['general'];
@endphp

<section class="relative isolate overflow-hidden bg-brand-700 py-16 md:py-20" aria-labelledby="cta-title">
    <div class="absolute -right-24 -top-24 -z-10 h-80 w-80 rounded-full bg-brand-500/40 blur-3xl"></div>
    <div class="absolute -bottom-32 -left-16 -z-10 h-72 w-72 rounded-full bg-brand-900/60 blur-3xl"></div>

    <div class="container">
        <x-ui.reveal>
            <div class="mx-auto max-w-3xl text-center">
                <h2 id="cta-title" class="text-3xl leading-tight text-white md:text-4xl">{{ $cta['title'] }}</h2>
                <p class="mx-auto mt-4 max-w-2xl text-base leading-relaxed text-brand-100 md:text-lg">{{ $cta['text'] }}</p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <x-ui.button :href="lroute('contact')" variant="light" size="lg" icon="arrow-right">
                        {{ t('nav.contact_us') }}
                    </x-ui.button>
                    <x-ui.button :href="'mailto:'.$email" variant="outline-light" size="lg">
                        {{ t('home.cta_email') }}
                    </x-ui.button>
                </div>
            </div>
        </x-ui.reveal>
    </div>
</section>