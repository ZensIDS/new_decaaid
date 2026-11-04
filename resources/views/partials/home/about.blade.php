{{-- 2. RINGKASAN + ANGKA ------------------------------------------------ --}}
    <section class="section-y bg-white" aria-labelledby="about-title">
        <div class="container grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
            <x-ui.reveal>
                <p class="eyebrow">{{ $home['about']['eyebrow'] }}</p>
                <h2 id="about-title" class="mt-3 text-3xl leading-tight md:text-4xl">{{ $home['about']['title'] }}</h2>
                <p class="mt-5 text-base leading-relaxed md:text-lg">{{ $home['about']['text'] }}</p>
                <div class="mt-7">
                    <x-ui.button :href="lroute('about.profile')" variant="outline" icon="arrow-right">
                        {{ t('home.about_more') }}
                    </x-ui.button>
                </div>
            </x-ui.reveal>

            {{-- Komposisi foto. DUMMY: ganti di public/img/dummy/about-1.jpg dan about-2.jpg --}}
            <x-ui.reveal :delay="150">
                <div class="relative mx-auto w-full max-w-xl pb-12 pr-4 pt-4 sm:pr-8 lg:max-w-none">
                    <div class="absolute right-0 top-0 h-[88%] w-[82%] rounded-[2rem] bg-brand-50 ring-1 ring-brand-100"></div>
                    <div class="absolute -right-1 bottom-6 h-24 w-24 text-brand-200 [background-image:radial-gradient(currentColor_1.5px,transparent_1.5px)] [background-size:14px_14px]"></div>

                    <div class="relative ml-auto aspect-[4/3] w-[86%] overflow-hidden rounded-[2rem] shadow-card-hover">
                        <x-ui.img :src="\App\Support\Content::homeImage('about_1')" :alt="$company['name']" label="Foto kantor (dummy)"
                                  class="h-full w-full object-cover" />
                    </div>

                    <div class="absolute bottom-0 left-0 aspect-square w-[42%] overflow-hidden rounded-3xl border-[6px] border-white shadow-card-hover">
                        <x-ui.img :src="\App\Support\Content::homeImage('about_2')" alt="" label="Foto tim (dummy)"
                                  class="h-full w-full object-cover" />
                    </div>
                </div>
            </x-ui.reveal>
        </div>
    </section>
