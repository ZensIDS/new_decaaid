{{-- 4. PORTOFOLIO UNGGULAN ---------------------------------------------- --}}
    <section class="section-y bg-white" aria-labelledby="projects-title">
        <div class="container">
            <x-ui.reveal>
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div class="max-w-2xl">
                        <p class="eyebrow">{{ $home['projects']['eyebrow'] }}</p>
                        <h2 id="projects-title" class="mt-3 text-3xl leading-tight md:text-4xl">{{ $home['projects']['title'] }}</h2>
                        <p class="mt-4 text-base leading-relaxed md:text-lg">{{ $home['projects']['text'] }}</p>
                    </div>
                    <x-ui.button :href="lroute('portfolio.index')" variant="outline" icon="arrow-right" class="hidden md:inline-flex">
                        {{ t('home.all_projects') }}
                    </x-ui.button>
                </div>
            </x-ui.reveal>

            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ($projects as $project)
                    <x-ui.reveal :delay="$loop->index * 100" class="h-full">
                        <a href="{{ lroute('portfolio.show', ['slug' => $project['slug']]) }}"
                           class="group block h-full overflow-hidden rounded-xl bg-white shadow-card transition duration-300 hover:-translate-y-0.5 hover:shadow-card-hover">
                            <div class="overflow-hidden">
                                <x-ui.img :src="$project['image']" :alt="$project['title']" label="Foto proyek (dummy)"
                                          class="aspect-[16/10] w-full object-cover transition duration-500 group-hover:scale-105" />
                            </div>
                            <div class="p-6">
                                <x-ui.badge>{{ $project['unit_name'] }}</x-ui.badge>
                                <h3 class="mt-4 text-lg leading-snug">{{ $project['title'] }}</h3>
                                <p class="mt-2 text-sm">{{ $project['client'] }} &middot; {{ $project['year'] }}</p>
                            </div>
                        </a>
                    </x-ui.reveal>
                @endforeach
            </div>

            <div class="mt-8 md:hidden">
                <x-ui.button :href="lroute('portfolio.index')" variant="outline" icon="arrow-right" class="w-full">
                    {{ t('home.all_projects') }}
                </x-ui.button>
            </div>
        </div>
    </section>
