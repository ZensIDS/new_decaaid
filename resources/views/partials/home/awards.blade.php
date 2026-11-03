{{-- 7b. PENGHARGAAN: carousel kartu (4 per tampilan di desktop, 2 di tablet, 1 di ponsel). Data: Content::awardsPage() --}}
    @if (! empty($awards['items']))
        <section class="relative isolate overflow-hidden bg-brand-950 py-12 text-white md:py-16" aria-labelledby="awards-title"
                 x-data="{ i: 0, pv: 4, n: {{ count($awards['items']) }}, timer: null,
                           get max() { return Math.max(0, this.n - this.pv) },
                           calc() { this.pv = window.innerWidth >= 1024 ? 4 : (window.innerWidth >= 640 ? 2 : 1); if (this.i > this.max) this.i = this.max },
                           next() { this.i = this.i >= this.max ? 0 : this.i + 1 },
                           prev() { this.i = this.i <= 0 ? this.max : this.i - 1 },
                           start() { if (this.max > 0 && ! window.matchMedia('(prefers-reduced-motion: reduce)').matches) { this.stop(); this.timer = setInterval(() => this.next(), 4500) } },
                           stop() { clearInterval(this.timer) } }"
                 x-init="calc(); start()" @resize.window.debounce.150ms="calc()"
                 @mouseenter="stop()" @mouseleave="start()" @focusin="stop()" @focusout="start()">
            <div class="absolute inset-0 -z-10 bg-gradient-to-br from-brand-900 via-brand-950 to-brand-950"></div>
            <div class="absolute inset-0 -z-10 opacity-50 [background-image:radial-gradient(rgba(255,255,255,0.14)_1px,transparent_1px)] [background-size:26px_26px] [mask-image:radial-gradient(ellipse_at_85%_15%,#000,transparent_70%)]"></div>
            <div class="absolute -left-28 top-0 -z-10 h-72 w-72 rounded-full bg-brand-600/30 blur-3xl"></div>
            <div class="absolute -bottom-28 right-1/4 -z-10 h-64 w-64 rounded-full bg-accent-500/10 blur-3xl"></div>

            <div class="container">
                <x-ui.reveal>
                    <div class="flex flex-wrap items-end justify-between gap-5">
                        <div class="max-w-xl">
                            <p class="eyebrow inline-flex items-center gap-3 text-accent-400">
                                <span class="h-0.5 w-8 rounded-full bg-accent-500"></span>{{ $awards['eyebrow'] }}
                            </p>
                            <h2 id="awards-title" class="mt-3 text-2xl leading-tight text-white md:text-3xl">{{ $awards['title'] }}</h2>
                        </div>

                        <div class="flex items-center gap-3">
                            <x-ui.button :href="lroute('awards')" variant="outline-light" size="sm" icon="arrow-right" class="hidden sm:inline-flex">{{ $awards['view_all'] }}</x-ui.button>
                            <button type="button" @click="prev()" aria-label="{{ $awards['slide_prev'] }}"
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white ring-1 ring-white/20 transition hover:bg-white hover:text-brand-800">
                                <x-ui.icon name="chevron-left" class="h-5 w-5" />
                            </button>
                            <button type="button" @click="next()" aria-label="{{ $awards['slide_next'] }}"
                                    class="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white ring-1 ring-white/20 transition hover:bg-white hover:text-brand-800">
                                <x-ui.icon name="chevron-right" class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </x-ui.reveal>

                <x-ui.reveal :delay="100">
                    {{-- Jendela geser: -mx/px menjaga bayangan dan jarak antar kartu --}}
                    <div class="-mx-2.5 mt-8 overflow-hidden py-2">
                        <ul class="flex transition-transform duration-700 ease-out" :style="`transform: translateX(-${i * 100 / pv}%)`">
                            @foreach ($awards['items'] as $award)
                                <li class="shrink-0 px-2.5" :style="`width: ${100 / pv}%`">
                                    <a href="{{ lroute('awards') }}"
                                       class="group flex h-full flex-col overflow-hidden rounded-2xl bg-white/[0.06] ring-1 ring-white/10 backdrop-blur transition duration-300 hover:-translate-y-1 hover:bg-white/[0.1] hover:ring-white/25">
                                        <div class="relative overflow-hidden">
                                            <x-ui.img :src="$award['image']" :alt="$award['image_alt'] ?? $award['title']" :label="$award['title']"
                                                      class="aspect-[4/3] w-full object-cover transition duration-700 group-hover:scale-105" />
                                            <span class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-white/90 px-2.5 py-1 text-xs font-bold text-brand-800 shadow-sm backdrop-blur">
                                                <svg class="h-3.5 w-3.5 text-accent-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4zM17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/></svg>
                                                {{ $award['year'] }}
                                            </span>
                                        </div>
                                        <div class="flex flex-1 flex-col p-4">
                                            <h3 class="line-clamp-2 text-base font-bold leading-snug text-white">{{ $award['title'] }}</h3>
                                            <p class="mt-1.5 line-clamp-2 text-sm leading-relaxed text-brand-100">{{ $award['text'] }}</p>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </x-ui.reveal>

                <div class="mt-5 flex items-center justify-between gap-4" x-show="max > 0">
                    {{-- Titik penunjuk posisi --}}
                    <div class="flex items-center gap-2">
                        <template x-for="d in max + 1" :key="d">
                            <button type="button" @click="i = d - 1" :aria-label="`${d}`"
                                    class="h-2 rounded-full transition-all duration-300"
                                    :class="i === d - 1 ? 'w-7 bg-accent-500' : 'w-2 bg-white/30 hover:bg-white/60'"></button>
                        </template>
                    </div>
                    <x-ui.button :href="lroute('awards')" variant="link" size="sm" icon="arrow-right" class="text-white sm:hidden">{{ $awards['view_all'] }}</x-ui.button>
                </div>
            </div>
        </section>
    @endif
