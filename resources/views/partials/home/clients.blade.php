{{-- 5. KLIEN & MITRA ---------------------------------------------------- --}}
    <section class="section-y bg-white" aria-labelledby="clients-title">
        <div class="container">
            <div class="relative isolate overflow-hidden rounded-[2rem] bg-slate-50 px-6 py-12 ring-1 ring-slate-200/70 md:px-12 md:py-16">
                <div class="absolute -left-24 -top-24 -z-10 h-72 w-72 rounded-full bg-brand-100/70 blur-3xl"></div>

                <div class="grid items-center gap-12 lg:grid-cols-12 lg:gap-14">
                    <x-ui.reveal class="lg:col-span-4">
                        <p class="eyebrow">{{ $home['clients']['eyebrow'] }}</p>
                        <h2 id="clients-title" class="mt-3 text-3xl leading-tight md:text-4xl">{{ $home['clients']['title'] }}</h2>

                        <div class="mt-8">
                            <x-ui.button :href="lroute('clients')" variant="outline" icon="arrow-right">
                                {{ t('home.all_clients') }}
                            </x-ui.button>
                        </div>
                    </x-ui.reveal>

                    {{-- DUMMY: nama dan logo klien. Dua baris berjalan berlawanan arah, berhenti saat disorot, statis bila animasi dimatikan --}}
                    @php $rows = array_chunk($clients, max(1, (int) ceil(count($clients) / 2))); @endphp
                    <div class="group space-y-4 overflow-hidden [mask-image:linear-gradient(to_right,transparent,#000_10%,#000_90%,transparent)] motion-reduce:overflow-visible lg:col-span-8">
                        @foreach ($rows as $r => $row)
                            <div class="flex w-max animate-marquee group-hover:[animation-play-state:paused] motion-reduce:w-auto motion-reduce:animate-none motion-reduce:justify-center {{ $r % 2 ? '[animation-direction:reverse]' : '' }}">
                                @foreach ([0, 1] as $copy)
                                    <ul class="flex shrink-0 items-center gap-4 pr-4 motion-reduce:flex-wrap motion-reduce:justify-center {{ $copy ? 'motion-reduce:hidden' : '' }}"
                                        @if ($copy) aria-hidden="true" @endif>
                                        @foreach ($row as $client)
                                            <li class="flex h-20 w-44 shrink-0 items-center justify-center rounded-xl bg-white p-3 opacity-70 shadow-sm ring-1 ring-slate-200/80 grayscale transition duration-300 hover:opacity-100 hover:shadow-card hover:grayscale-0">
                                                <x-ui.img :src="$client['logo']" :alt="$copy ? '' : $client['name']" :label="$client['name']"
                                                          class="h-full w-full rounded-lg object-contain" />
                                            </li>
                                        @endforeach
                                    </ul>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
