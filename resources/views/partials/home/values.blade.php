{{-- 6. KEUNGGULAN / NILAI ------------------------------------------------- --}}
    <section class="section-y bg-brand-900" aria-labelledby="values-title">
        <div class="container">
            <x-ui.reveal>
                <p class="eyebrow text-accent-400">{{ $home['values']['eyebrow'] }}</p>
                <h2 id="values-title" class="mt-3 max-w-2xl text-3xl leading-tight text-white md:text-4xl">{{ $home['values']['title'] }}</h2>
            </x-ui.reveal>

            <div class="mt-12 grid gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($home['values']['items'] as $item)
                    <x-ui.reveal :delay="$loop->index * 100">
                        <div class="border-t border-white/20 pt-6">
                            <p class="text-sm font-bold tracking-widest text-accent-400">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</p>
                            <h3 class="mt-3 text-lg text-white">{{ $item['title'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-brand-200">{{ $item['text'] }}</p>
                        </div>
                    </x-ui.reveal>
                @endforeach
            </div>
        </div>
    </section>
