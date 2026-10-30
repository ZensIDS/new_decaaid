<?php

namespace App\Support;

/**
 * Kamus kata/label antarmuka (tombol, menu, judul kecil) dalam ID dan EN.
 *
 * SEMUA ISI DI SINI ADALAH KATA DUMMY yang mudah direvisi: cukup ubah teks
 * di sisi kanan. Dipakai lewat helper t('kunci'), contoh: t('nav.contact_us').
 * Kunci yang belum ada tidak menampilkan kunci mentah, melainkan teks
 * bawaan yang terbaca (mis. "Contact us").
 *
 * Teks panjang halaman (judul, paragraf) ada di App\Support\Content.
 */
class Labels
{
    protected const MAP = [
        // a11y
        'a11y.skip' => ['id' => 'Lewati ke konten utama', 'en' => 'Skip to main content'],

        // nav
        'nav.main' => ['id' => 'Navigasi utama', 'en' => 'Main navigation'],
        'nav.menu' => ['id' => 'Menu', 'en' => 'Menu'],
        'nav.menu_open' => ['id' => 'Buka menu', 'en' => 'Open menu'],
        'nav.menu_close' => ['id' => 'Tutup menu', 'en' => 'Close menu'],
        'nav.submenu' => ['id' => 'Buka submenu', 'en' => 'Open submenu'],
        'nav.search' => ['id' => 'Cari', 'en' => 'Search'],
        'nav.search_title' => ['id' => 'Cari di situs', 'en' => 'Search the site'],
        'nav.search_placeholder' => ['id' => 'Cari berita, pilar bisnis, proyek…', 'en' => 'Search news, business pillars, projects…'],
        'nav.search_quick' => ['id' => 'Pintasan pilar bisnis', 'en' => 'Business pillar shortcuts'],
        'nav.language' => ['id' => 'Bahasa', 'en' => 'Language'],
        'nav.contact_us' => ['id' => 'Hubungi Kami', 'en' => 'Contact Us'],
        'nav.all_units' => ['id' => 'Lihat semua pilar bisnis', 'en' => 'View all business pillars'],

        // footer
        'footer.company' => ['id' => 'Perusahaan', 'en' => 'Company'],
        'footer.units' => ['id' => 'Pilar Bisnis', 'en' => 'Business Pillars'],
        'footer.offices' => ['id' => 'Kantor', 'en' => 'Offices'],
        'footer.contact' => ['id' => 'Kontak', 'en' => 'Contact'],
        'footer.follow' => ['id' => 'Ikuti kami', 'en' => 'Follow us'],
        'footer.rights' => ['id' => 'Hak cipta dilindungi.', 'en' => 'All rights reserved.'],
        'footer.privacy' => ['id' => 'Kebijakan Privasi', 'en' => 'Privacy Policy'],
        'footer.email' => ['id' => 'Email', 'en' => 'Email'],
        'footer.phone' => ['id' => 'Telepon', 'en' => 'Phone'],

        // setup
        'setup.title' => ['id' => 'Fondasi proyek siap', 'en' => 'Project foundation is ready'],
        'setup.subtitle' => ['id' => 'Halaman uji sementara. Akan diganti dengan beranda sebenarnya di tahap A3.', 'en' => 'Temporary test page. It will be replaced by the real homepage in stage A3.'],
        'setup.language' => ['id' => 'Bahasa', 'en' => 'Language'],
        'setup.routes' => ['id' => 'Daftar halaman', 'en' => 'Page list'],
        'setup.placeholder_title' => ['id' => 'Halaman dalam pengerjaan', 'en' => 'Page under construction'],
        'setup.placeholder_text' => ['id' => 'Routing untuk halaman ini sudah aktif. Tampilan akan dibuat pada tahap berikutnya.', 'en' => 'Routing for this page is already active. The design will be built in a later stage.'],

        // ui
        'ui.read_more' => ['id' => 'Selengkapnya', 'en' => 'Read more'],

        // home
        'home.hero_cta_units' => ['id' => 'Jelajahi Pilar Bisnis', 'en' => 'Explore Business Pillars'],
        'home.badge_units' => ['id' => 'Pilar Bisnis', 'en' => 'Business Pillars'],
        'home.about_more' => ['id' => 'Tentang kami', 'en' => 'About us'],
        'home.all_projects' => ['id' => 'Lihat semua proyek', 'en' => 'View all projects'],
        'home.all_clients' => ['id' => 'Lihat klien & mitra', 'en' => 'View clients & partners'],
        'home.all_news' => ['id' => 'Lihat semua berita', 'en' => 'View all news'],
        'home.community_more' => ['id' => 'Pelajari Keberlanjutan', 'en' => 'Learn About Sustainability'],
        'home.sustainability' => ['id' => 'Keberlanjutan', 'en' => 'Sustainability'],
        'home.cta_email' => ['id' => 'Kirim Email', 'en' => 'Send Email'],

        // berita (halaman detail)
        'news.min_read' => ['id' => 'menit baca', 'en' => 'min read'],
        'news.older' => ['id' => 'Berita sebelumnya', 'en' => 'Previous story'],
        'news.newer' => ['id' => 'Berita berikutnya', 'en' => 'Next story'],
        'news.keep_reading' => ['id' => 'Baca juga', 'en' => 'Keep reading'],

        // pilar bisnis (daftar + detail). DUMMY: teks band dan lead mudah direvisi
        'units.prev' => ['id' => 'Pilar sebelumnya', 'en' => 'Previous pillar'],
        'units.next' => ['id' => 'Pilar berikutnya', 'en' => 'Next pillar'],
        'units.services_lead' => ['id' => 'Cakupan layanan yang dapat kami bantu untuk institusi dan bisnis Anda.', 'en' => 'The range of services we can support for your institution and business.'],
        'units.process_eyebrow' => ['id' => 'Alur Kerja', 'en' => 'Workflow'],
        'units.process_lead' => ['id' => 'Empat tahap yang menjaga setiap pekerjaan tetap jelas, terukur, dan tepat waktu.', 'en' => 'Four stages that keep every engagement clear, measurable, and on schedule.'],
        'units.projects_eyebrow' => ['id' => 'Portofolio', 'en' => 'Portfolio'],
        'units.view_project' => ['id' => 'Lihat Proyek', 'en' => 'View Project'],
        'units.band_eyebrow' => ['id' => 'Mengapa DECAA.ID', 'en' => 'Why DECAA.ID'],
        'units.band_title' => ['id' => 'Satu pintu untuk kebutuhan teknologi Anda', 'en' => 'One door for your technology needs'],
        'units.band_1_title' => ['id' => 'Terintegrasi', 'en' => 'Integrated'],
        'units.band_1_text' => ['id' => 'Perangkat lunak, perangkat keras, jaringan, hingga pelatihan dikoordinasikan dalam satu tim.', 'en' => 'Software, hardware, networking, and training coordinated by a single team.'],
        'units.band_2_title' => ['id' => 'Terukur', 'en' => 'Measurable'],
        'units.band_2_text' => ['id' => 'Perencanaan, jadwal, dan pelaporan yang jelas di setiap tahap pekerjaan.', 'en' => 'Clear planning, schedules, and reporting at every stage of the work.'],
        'units.band_3_title' => ['id' => 'Berkelanjutan', 'en' => 'Long-lasting'],
        'units.band_3_text' => ['id' => 'Pendampingan purna jual agar solusi tetap berjalan setelah serah terima.', 'en' => 'After-sales support so solutions keep running after handover.'],
    ];

    public static function get(string $key, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        if (isset(self::MAP[$key])) {
            return self::MAP[$key][$locale] ?? self::MAP[$key]['id'];
        }

        // Cadangan: ubah "nav.contact_us" menjadi "Contact us"
        $last = substr(strrchr('.'.$key, '.'), 1);

        return ucfirst(str_replace('_', ' ', $last));
    }
}