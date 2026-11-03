{{-- 8. BERITA TERBARU --------------------------------------------------- --}}
    <section class="section-y bg-slate-50" aria-labelledby="news-title">
        <div class="container">
            <x-ui.reveal>
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div class="max-w-2xl">
                        <p class="eyebrow">{{ $home['news']['eyebrow'] }}</p>
                        <h2 id="news-title" class="mt-3 text-3xl leading-tight md:text-4xl">{{ $home['news']['title'] }}</h2>
                        <p class="mt-4 text-base leading-relaxed md:text-lg">{{ $home['news']['text'] }}</p>
                    </div>
                    <x-ui.button :href="lroute('news.index')" variant="outline" icon="arrow-right" class="hidden md:inline-flex">
                        {{ t('home.all_news') }}
                    </x-ui.button>
                </div>
            </x-ui.reveal>

            <div class="mt-10 grid gap-6 md:grid-cols-3">
                @foreach ($posts as $post)
                    <x-ui.reveal :delay="$loop->index * 100" class="h-full">
                        <a href="{{ lroute('news.show', ['slug' => $post['slug']]) }}"
                           class="group block h-full overflow-hidden rounded-xl bg-white shadow-card transition duration-300 hover:-translate-y-0.5 hover:shadow-card-hover">
                            <div class="overflow-hidden">
                                <x-ui.img :src="$post['image']" :alt="$post['image_alt']" label="Foto berita"
                                          class="aspect-[16/10] w-full object-cover transition duration-500 group-hover:scale-105" />
                            </div>
                            <div class="p-6">
                                <div class="flex items-center gap-3 text-xs">
                                    <x-ui.badge tone="accent">{{ $post['category'] }}</x-ui.badge>
                                    <time datetime="{{ $post['date'] }}" class="font-semibold text-slate-500">
                                        {{ \Carbon\Carbon::parse($post['date'])->translatedFormat('d F Y') }}
                                    </time>
                                </div>
                                <h3 class="mt-4 text-lg leading-snug">{{ $post['title'] }}</h3>
                                <p class="mt-2 text-sm leading-relaxed">{{ $post['excerpt'] }}</p>
                            </div>
                        </a>
                    </x-ui.reveal>
                @endforeach
            </div>

            <div class="mt-8 md:hidden">
                <x-ui.button :href="lroute('news.index')" variant="outline" icon="arrow-right" class="w-full">
                    {{ t('home.all_news') }}
                </x-ui.button>
            </div>
        </div>
    </section>
