{{-- 3. UNIT BISNIS ------------------------------------------------------ --}}
    <section class="section-y bg-slate-50" aria-labelledby="units-title">
        <div class="container">
            <x-ui.reveal>
                <div class="flex flex-wrap items-end justify-between gap-6">
                    <div class="max-w-2xl">
                        <p class="eyebrow">{{ $home['units']['eyebrow'] }}</p>
                        <h2 id="units-title" class="mt-3 text-3xl leading-tight md:text-4xl">{{ $home['units']['title'] }}</h2>
                        <p class="mt-4 text-base leading-relaxed md:text-lg">{{ $home['units']['text'] }}</p>
                    </div>
                </div>
            </x-ui.reveal>

            {{-- Semua brand dari semua pilar, 5 per baris (desktop). Logo yang belum ada tampil placeholder. --}}
            <ul class="mt-10 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5 lg:gap-5">
                @foreach ($units as $unit)
                    @foreach ($unit['brands'] as $brand)
                        <li>
                            <x-ui.reveal :delay="($loop->parent->index + $loop->index) % 5 * 70" class="h-full">
                                <a href="{{ lroute('units.brand', ['slug' => $unit['slug'], 'brand' => $brand['slug']]) }}"
                                   class="group flex h-full flex-col items-center gap-4 rounded-2xl bg-white p-5 text-center shadow-card ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-1 hover:shadow-card-hover hover:ring-brand-200">
                                    <x-ui.img :src="$brand['logo']" :alt="$brand['name']" :label="$brand['name']"
                                              class="h-20 w-full rounded-lg object-contain" />
                                    <span class="text-sm font-semibold leading-snug text-ink transition group-hover:text-brand-700">{{ $brand['name'] }}</span>
                                </a>
                            </x-ui.reveal>
                        </li>
                    @endforeach
                @endforeach
            </ul>

            <x-ui.reveal class="mt-10 flex justify-center">
                <x-ui.button :href="lroute('units.index')" variant="outline" icon="arrow-right">{{ t('nav.all_units') }}</x-ui.button>
            </x-ui.reveal>
        </div>
    </section>
