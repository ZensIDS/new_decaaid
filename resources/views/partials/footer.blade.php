@php
    $company = \App\Support\Content::company();
    $nav = \App\Support\Content::nav();
    $units = \App\Support\Content::units();

    // Tautan perusahaan: anak menu Tentang Kami + halaman utama lain
    $companyLinks = [];
    foreach ($nav as $item) {
        if ($item['route'] === 'about.profile') {
            foreach ($item['children'] as $child) {
                $companyLinks[] = [$child['label'], lroute($child['route'])];
            }
        } elseif (in_array($item['route'], ['news.index', 'community', 'careers', 'contact'], true)) {
            $companyLinks[] = [$item['label'], lroute($item['route'])];
        }
    }
    $companyLinks[] = [t('footer.privacy'), lroute('privacy')];

    $link = 'text-sm text-brand-200 transition hover:text-white';
    $heading = 'text-sm font-bold uppercase tracking-widest text-white';
@endphp

<footer class="bg-brand-950 text-brand-100">
    <div class="container py-14 lg:py-16">
        <div class="grid gap-12 lg:grid-cols-12">

            {{-- Brand + sosmed --}}
            <div class="lg:col-span-4">
                <a href="{{ lroute('home') }}" class="inline-block" aria-label="{{ $company['name'] }}">
                    <img src="{{ logo_url($company['logo']['on_dark']) }}" alt="{{ $company['name'] }}" class="h-9 w-auto sm:h-10" loading="lazy"
                         onerror="this.hidden=true;this.nextElementSibling.hidden=false">
                    <span hidden class="text-2xl font-extrabold text-white">{{ $company['name'] }}</span>
                </a>
                <p class="mt-5 max-w-sm text-sm leading-relaxed text-brand-200">{{ $company['tagline'] }}</p>

                {{-- DUMMY: URL sosmed diisi setelah desain di-ACC --}}
                <p class="{{ $heading }} mt-8">{{ t('footer.follow') }}</p>
                <ul class="mt-4 flex flex-wrap gap-3">
                    @foreach (['instagram' => 'Instagram', 'facebook' => 'Facebook', 'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'linkedin' => 'LinkedIn'] as $net => $netName)
                        @php $url = $company['social'][$net] ?? '#'; @endphp
                        <li>
                            <a href="{{ $url }}" aria-label="{{ $netName }}" title="{{ $netName }}"
                               class="flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-brand-100 ring-1 ring-white/15 transition duration-300 hover:-translate-y-0.5 hover:bg-white hover:text-brand-900"
                               @if ($url !== '#') target="_blank" rel="noopener noreferrer" @endif>
                                <x-ui.social-icon :name="$net" class="h-[1.15rem] w-[1.15rem]" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Perusahaan --}}
            <div class="lg:col-span-2">
                <p class="{{ $heading }}">{{ t('footer.company') }}</p>
                <ul class="mt-4 space-y-2.5">
                    @foreach ($companyLinks as [$label, $url])
                        <li><a href="{{ $url }}" class="{{ $link }}">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Unit bisnis --}}
            <div class="lg:col-span-2">
                <p class="{{ $heading }}">{{ t('footer.units') }}</p>
                <ul class="mt-4 space-y-2.5">
                    @foreach ($units as $unit)
                        <li><a href="{{ lroute('units.show', ['slug' => $unit['slug']]) }}" class="{{ $link }}">{{ $unit['name'] }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Kantor + kontak --}}
            <div class="lg:col-span-4">
                <p class="{{ $heading }}">{{ t('footer.offices') }}</p>
                <ul class="mt-4 space-y-5">
                    @foreach ($company['offices'] as $office)
                        <li class="flex gap-3">
                            <x-ui.icon name="map-pin" class="mt-0.5 h-5 w-5 shrink-0 text-accent-400" />
                            <address class="not-italic">
                                <p class="text-xs font-semibold uppercase tracking-wider text-brand-300">{{ $office['type'] }}</p>
                                <p class="mt-0.5 text-sm font-semibold text-white">{{ $office['city'] }}</p>
                                <p class="mt-1 text-sm leading-relaxed text-brand-200">{{ $office['address'] }}</p>
                            </address>
                        </li>
                    @endforeach
                </ul>

                <p class="{{ $heading }} mt-8">{{ t('footer.contact') }}</p>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li class="flex items-center gap-3">
                        <x-ui.icon name="mail" class="h-5 w-5 shrink-0 text-accent-400" />
                        <a href="mailto:{{ $company['email']['general'] }}" class="{{ $link }}">{{ $company['email']['general'] }}</a>
                    </li>
                    {{-- DUMMY: nomor telepon dan WhatsApp --}}
                    <li class="flex items-center gap-3">
                        <x-ui.icon name="phone" class="h-5 w-5 shrink-0 text-accent-400" />
                        <a href="tel:{{ preg_replace('/[^+\d]/', '', $company['phone']) }}" class="{{ $link }}">{{ $company['phone'] }}</a>
                    </li>
                    <li class="flex items-center gap-3">
                        <x-ui.icon name="phone" class="h-5 w-5 shrink-0 text-accent-400" />
                        <a href="{{ $company['whatsapp']['url'] }}" target="_blank" rel="noopener noreferrer" class="{{ $link }}">
                            WhatsApp {{ $company['whatsapp']['label'] }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="border-t border-white/10">
        <div class="container flex flex-col gap-2 py-5 text-xs text-brand-300 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} {{ $company['name'] }}. {{ t('footer.rights') }}</p>
            <a href="{{ lroute('privacy') }}" class="transition hover:text-white">{{ t('footer.privacy') }}</a>
        </div>
    </div>
</footer>