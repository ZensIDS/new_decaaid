{{-- 7. KEBERLANJUTAN: KOMUNITAS PEDULI DIGITAL (CSR) ----------------------------------- --}}
    <section class="section-y bg-white" aria-labelledby="community-title">
        <div class="container grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
            <x-ui.reveal>
                {{-- DUMMY: ganti foto kegiatan di public/img/dummy/community.jpg --}}
                <x-ui.img :src="\App\Support\Content::homeImage('community')" :alt="$community['name']" label="Foto kegiatan komunitas (dummy)"
                          class="aspect-[4/3] w-full rounded-2xl object-cover shadow-card" />
            </x-ui.reveal>

            <x-ui.reveal :delay="120">
                <x-ui.img :src="$community['logo']" :alt="$community['name']" :label="$community['name']"
                          class="h-14 w-auto max-w-[18rem] rounded-lg object-contain object-left sm:h-16" />
                <p class="eyebrow mt-6">{{ t('home.sustainability') }}</p>
                <h2 id="community-title" class="mt-3 text-3xl leading-tight md:text-4xl">{{ $community['name'] }}</h2>
                <p class="mt-4 text-base leading-relaxed md:text-lg">{{ $community['summary'] }}</p>

                <ul class="mt-6 space-y-3">
                    @foreach ($home['community']['points'] as $point)
                        <li class="flex items-start gap-3 text-sm font-semibold text-ink">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-700">
                                <x-ui.icon name="check" class="h-3.5 w-3.5" />
                            </span>
                            {{ $point }}
                        </li>
                    @endforeach
                </ul>

                <div class="mt-8">
                    <x-ui.button :href="lroute('community')" variant="outline" icon="arrow-right">
                        {{ t('home.community_more') }}
                    </x-ui.button>
                </div>
            </x-ui.reveal>
        </div>
    </section>
