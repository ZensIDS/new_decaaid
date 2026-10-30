<?php

namespace App\Support;

/**
 * Lapisan konten TERPUSAT (sementara berisi data dummy).
 *
 * Semua view mengambil data lewat class ini, bukan menulis teks langsung.
 * Nanti (Tahap B4) isi tiap method diganti query database tanpa mengubah view.
 *
 * Nilai dwibahasa ditulis ['id' => '...', 'en' => '...'] dan otomatis
 * di-resolve ke bahasa aktif oleh localize().
 *
 * SEMUA ISI DI SINI ADALAH DUMMY, kecuali alamat kantor dan email yang
 * sudah diberikan: ganti dengan data asli setelah desain di-ACC.
 */
class Content
{
    /**
     * Ubah array dwibahasa ['id' => .., 'en' => ..] menjadi nilai bahasa aktif.
     */
    protected static function localize(mixed $value, ?string $locale = null): mixed
    {
        $locale ??= app()->getLocale();

        if (! is_array($value)) {
            return $value;
        }

        $keys = array_keys($value);
        sort($keys);

        if ($keys === ['en', 'id']) {
            return $value[$locale] ?? $value['id'];
        }

        return array_map(fn ($item) => self::localize($item, $locale), $value);
    }

    /* ---------------------------------------------------------------------
     | Perusahaan
     * ------------------------------------------------------------------- */

    public static function company(): array
    {
        return self::localize([
            'name' => 'DECAA.ID',
            'tagline' => [
                'id' => 'Solusi teknologi dan layanan terpadu untuk institusi dan bisnis',
                'en' => 'Integrated technology solutions and services for institutions and businesses',
            ],
            'logo' => [
                'on_light' => 'img/logo/decaaid-black.png',
                'on_dark' => 'img/logo/decaaid-white.png',
            ],
            'email' => [
                'general' => 'info@decaa.id',
                'career' => 'career@decaa.id',
            ],
            'phone' => '+62 21 0000 0000',          // DUMMY
            'whatsapp' => [                          // DUMMY
                'label' => '+62 812 0000 0000',
                'url' => 'https://wa.me/6281200000000',
            ],
            'offices' => [
                [
                    'key' => 'jakarta',
                    'type' => ['id' => 'Kantor Pusat', 'en' => 'Headquarters'],
                    'city' => 'Jakarta',
                    'address' => 'Ad Premier Office Park, Jl. TB Simatupang, Ragunan, Pasar Minggu, Jakarta',
                ],
                [
                    'key' => 'pacitan',
                    'type' => ['id' => 'Kantor Cabang', 'en' => 'Branch Office'],
                    'city' => 'Pacitan',
                    'address' => ['id' => 'Jl. Letjend R. Suprapto, Sidoarjo, Pacitan, Jawa Timur',
                                  'en' => 'Jl. Letjend R. Suprapto, Sidoarjo, Pacitan, East Java'],
                ],
                [
                    'key' => 'ponorogo',
                    'type' => ['id' => 'Kantor Cabang', 'en' => 'Branch Office'],
                    'city' => 'Ponorogo',
                    'address' => ['id' => 'Jl. Sultan Agung, Ponorogo, Jawa Timur',
                                  'en' => 'Jl. Sultan Agung, Ponorogo, East Java'],
                ],
            ],
            'social' => [                            // DUMMY: isi URL asli
                'whatsapp' => 'https://wa.me/6281200000000',
                'instagram' => '#',
                'facebook' => '#',
                'youtube' => '#',
                'tiktok' => '#',
                'linkedin' => '#',
            ],
        ]);
    }

    /* ---------------------------------------------------------------------
     | Pilar bisnis
     * ------------------------------------------------------------------- */

    protected static function rawUnits(): array
    {
        return [
            [
                'slug' => 'software',
                'name' => 'Software',
                'subtitle' => null,
                'logo' => 'img/logo/software.png',
                'summary' => [
                    'id' => 'Pengembangan sistem informasi, aplikasi web, dan solusi digital yang disesuaikan dengan kebutuhan institusi dan bisnis.',
                    'en' => 'Development of information systems, web applications, and digital solutions tailored to the needs of institutions and businesses.',
                ],
            ],
            [
                'slug' => 'robotics',
                'name' => 'Robotics',
                'subtitle' => null,
                'logo' => 'img/logo/robotic.png',
                'summary' => [
                    'id' => 'Edukasi, pengembangan, dan penerapan teknologi robotika serta otomasi untuk sekolah, kampus, dan industri.',
                    'en' => 'Education, development, and application of robotics and automation technology for schools, universities, and industry.',
                ],
            ],
            [
                'slug' => 'hardware',
                'name' => 'Hardware',
                'subtitle' => null,
                'logo' => 'img/logo/hardware.png',
                'summary' => [
                    'id' => 'Pengadaan perangkat teknologi informasi dan perangkat penunjang kerja, lengkap dengan layanan purna jual.',
                    'en' => 'Procurement of information technology equipment and work-support devices, complete with after-sales service.',
                ],
            ],
            [
                'slug' => 'networking-services',
                'name' => 'Networking Services',
                'subtitle' => null,
                'logo' => 'img/logo/networking.png',
                'summary' => [
                    'id' => 'Perancangan, instalasi, dan pemeliharaan jaringan, CCTV, serta infrastruktur pendukung operasional.',
                    'en' => 'Design, installation, and maintenance of networks, CCTV, and supporting operational infrastructure.',
                ],
            ],
            [
                'slug' => 'training-research-centre',
                'name' => 'Training & Research Centre',
                'subtitle' => null,
                'logo' => 'img/logo/trainingcentre.png',
                'summary' => [
                    'id' => 'Pelatihan, workshop, dan riset teknologi untuk meningkatkan kompetensi sumber daya manusia.',
                    'en' => 'Training, workshops, and technology research to improve the competence of human resources.',
                ],
            ],
            [
                'slug' => 'paperking',
                'name' => 'PaperKing',
                'subtitle' => 'Stationery & Office Supply',
                'logo' => 'img/logo/paperking.png',
                'summary' => [
                    'id' => 'Penyediaan alat tulis kantor dan perlengkapan perkantoran untuk instansi dan perusahaan.',
                    'en' => 'Supply of stationery and office equipment for agencies and companies.',
                ],
            ],
            [
                'slug' => 'dapur-kirana',
                'name' => 'Dapur Kirana',
                'subtitle' => 'Food & Beverages',
                'logo' => 'img/logo/dapurkirana.png',
                'summary' => [
                    'id' => 'Layanan makanan dan minuman, termasuk penyediaan konsumsi untuk kegiatan instansi dan korporasi.',
                    'en' => 'Food and beverage services, including catering for agency and corporate events.',
                ],
            ],
        ];
    }

    public static function units(): array
    {
        return self::localize(self::rawUnits());
    }

    public static function unit(string $slug): ?array
    {
        foreach (self::units() as $unit) {
            if ($unit['slug'] === $slug) {
                return $unit;
            }
        }

        return null;
    }

    /* ---------------------------------------------------------------------
     | Komunitas (CSR)
     * ------------------------------------------------------------------- */

    public static function community(): array
    {
        return self::localize([
            'slug' => 'komunitas-peduli-digital',
            'name' => 'Komunitas Peduli Digital',
            'logo' => 'img/logo/kompeddig.png',
            'summary' => [
                'id' => 'Komunitas CSR DECAA.ID yang berfokus pada literasi digital dan pemerataan akses teknologi bagi masyarakat.',
                'en' => 'The CSR community of DECAA.ID, focused on digital literacy and equal access to technology for society.',
            ],
        ]);
    }

    /* ---------------------------------------------------------------------
     | Navigasi utama (nanti dikelola dari admin)
     |   route  : key halaman di config/site.php
     |   params : parameter route (untuk halaman detail)
     * ------------------------------------------------------------------- */

    public static function nav(): array
    {
        $unitChildren = array_map(fn ($unit) => [
            'label' => $unit['name'],
            'route' => 'units.show',
            'params' => ['slug' => $unit['slug']],
            'logo' => $unit['logo'],
        ], self::units());

        return self::localize([
            [
                'label' => ['id' => 'Tentang Kami', 'en' => 'About Us'],
                'route' => 'about.profile',
                'children' => [
                    ['label' => ['id' => 'Profil Perusahaan', 'en' => 'Company Profile'], 'route' => 'about.profile'],
                    ['label' => ['id' => 'Visi & Misi', 'en' => 'Vision & Mission'], 'route' => 'about.vision'],
                    ['label' => ['id' => 'Perjalanan Kami', 'en' => 'Our Journey'], 'route' => 'about.journey'],
                    ['label' => ['id' => 'Jajaran Kepemimpinan', 'en' => 'Leadership'], 'route' => 'about.team'],
                ],
            ],
            [
                'label' => ['id' => 'Pilar Bisnis', 'en' => 'Business Pillars'],
                'route' => 'units.index',
                'children' => $unitChildren,
            ],
            [
                'label' => ['id' => 'Portofolio', 'en' => 'Portfolio'],
                'route' => 'portfolio.index',
                'children' => [
                    ['label' => ['id' => 'Proyek', 'en' => 'Projects'], 'route' => 'portfolio.index'],
                    ['label' => ['id' => 'Klien & Mitra', 'en' => 'Clients & Partners'], 'route' => 'clients'],
                ],
            ],
            [
                'label' => ['id' => 'Berita', 'en' => 'News'],
                'route' => 'news.index',
            ],
            [
                'label' => ['id' => 'Keberlanjutan', 'en' => 'Sustainability'],
                'route' => 'community',
            ],
            [
                'label' => ['id' => 'Karir', 'en' => 'Careers'],
                'route' => 'careers',
            ],
            [
                'label' => ['id' => 'Kontak', 'en' => 'Contact'],
                'route' => 'contact',
            ],
        ]);
    }

    /* ---------------------------------------------------------------------
     | Beranda: teks section (DUMMY, diganti setelah desain di-ACC)
     * ------------------------------------------------------------------- */

    public static function home(): array
    {
        return self::localize([
            'hero' => [
                'eyebrow' => ['id' => 'Mitra teknologi dan layanan terpadu', 'en' => 'Integrated technology and services partner'],
                'title' => ['id' => 'Solusi teknologi yang menggerakkan institusi dan bisnis Anda', 'en' => 'Technology solutions that move your institution and business forward'],
                'text' => [
                    'id' => 'DECAA.ID menghadirkan tujuh pilar bisnis dalam satu ekosistem: perangkat lunak, robotika, perangkat keras, jaringan, pelatihan, perlengkapan kantor, hingga layanan makanan dan minuman.',
                    'en' => 'DECAA.ID brings seven business pillars together in one ecosystem: software, robotics, hardware, networking, training, office supplies, and food and beverage services.',
                ],
            ],
            'about' => [
                'eyebrow' => ['id' => 'Tentang DECAA.ID', 'en' => 'About DECAA.ID'],
                'title' => ['id' => 'Satu mitra untuk beragam kebutuhan teknologi dan layanan', 'en' => 'One partner for a wide range of technology and service needs'],
                'text' => [
                    'id' => 'DECAA.ID menaungi pilar-pilar bisnis di bidang teknologi informasi, robotika, perangkat keras, jaringan, pelatihan, perlengkapan kantor, serta makanan dan minuman.',
                    'en' => 'DECAA.ID oversees business pillars in information technology, robotics, hardware, networking, training, office supplies, and food and beverages.',
                ],
                'text2' => [
                    'id' => 'Dengan kantor pusat di Jakarta dan cabang di Pacitan serta Ponorogo, kami melayani institusi pemerintah, pendidikan, dan swasta.',
                    'en' => 'With our headquarters in Jakarta and branches in Pacitan and Ponorogo, we serve government, education, and private institutions.',
                ],
            ],
            'units' => [
                'eyebrow' => ['id' => 'Pilar Bisnis', 'en' => 'Business Pillars'],
                'title' => ['id' => 'Tujuh pilar, satu ekosistem layanan', 'en' => 'Seven pillars, one service ecosystem'],
                'text' => [
                    'id' => 'Setiap pilar fokus pada keahliannya, dan saling melengkapi untuk kebutuhan Anda.',
                    'en' => 'Each pillar focuses on its own expertise, and they complement each other to meet your needs.',
                ],
            ],
            'projects' => [
                'eyebrow' => ['id' => 'Portofolio', 'en' => 'Portfolio'],
                'title' => ['id' => 'Proyek unggulan kami', 'en' => 'Our featured projects'],
                'text' => [
                    'id' => 'Sebagian proyek yang kami kerjakan bersama institusi dan mitra.',
                    'en' => 'A selection of projects we have delivered with institutions and partners.',
                ],
            ],
            'clients' => [
                'eyebrow' => ['id' => 'Klien & Mitra', 'en' => 'Clients & Partners'],
                'title' => ['id' => 'Dipercaya oleh berbagai institusi dan perusahaan', 'en' => 'Trusted by institutions and companies'],
            ],
            'values' => [
                'eyebrow' => ['id' => 'Mengapa DECAA.ID', 'en' => 'Why DECAA.ID'],
                'title' => ['id' => 'Komitmen kami dalam setiap layanan', 'en' => 'Our commitment in every service'],
                'items' => [
                    [
                        'title' => ['id' => 'Solusi terintegrasi', 'en' => 'Integrated solutions'],
                        'text' => ['id' => 'Dari perangkat, jaringan, hingga aplikasi, ditangani dalam satu ekosistem.', 'en' => 'From devices and networks to applications, handled within one ecosystem.'],
                    ],
                    [
                        'title' => ['id' => 'Tim profesional', 'en' => 'Professional team'],
                        'text' => ['id' => 'Dikerjakan tenaga yang berpengalaman di bidangnya masing-masing.', 'en' => 'Delivered by people experienced in their respective fields.'],
                    ],
                    [
                        'title' => ['id' => 'Layanan purna jual', 'en' => 'After-sales support'],
                        'text' => ['id' => 'Pendampingan dan pemeliharaan berlanjut setelah proyek selesai.', 'en' => 'Support and maintenance continue after a project is completed.'],
                    ],
                    [
                        'title' => ['id' => 'Komitmen pada kualitas', 'en' => 'Commitment to quality'],
                        'text' => ['id' => 'Proses kerja terukur, transparan, dan mudah ditelusuri.', 'en' => 'Measurable, transparent, and traceable work processes.'],
                    ],
                ],
            ],
            'community' => [
                'eyebrow' => ['id' => 'Keberlanjutan', 'en' => 'Sustainability'],
                'points' => [
                    ['id' => 'Literasi digital untuk masyarakat', 'en' => 'Digital literacy for the community'],
                    ['id' => 'Pemerataan akses teknologi', 'en' => 'Equal access to technology'],
                    ['id' => 'Kolaborasi dengan sekolah dan komunitas lokal', 'en' => 'Collaboration with schools and local communities'],
                ],
            ],
            'news' => [
                'eyebrow' => ['id' => 'Berita', 'en' => 'News'],
                'title' => ['id' => 'Kabar terbaru dari DECAA.ID', 'en' => 'Latest from DECAA.ID'],
                'text' => [
                    'id' => 'Informasi kegiatan, proyek, dan perkembangan terbaru kami.',
                    'en' => 'Updates on our activities, projects, and latest developments.',
                ],
            ],
            'cta' => [
                'title' => ['id' => 'Siap berdiskusi tentang kebutuhan Anda?', 'en' => 'Ready to discuss your needs?'],
                'text' => [
                    'id' => 'Tim kami siap membantu menemukan solusi yang tepat. Hubungi kami untuk konsultasi awal.',
                    'en' => 'Our team is ready to help you find the right solution. Contact us for an initial consultation.',
                ],
            ],
        ]);
    }

    /* ---------------------------------------------------------------------
     | Angka pencapaian (DUMMY: ganti dengan angka asli)
     * ------------------------------------------------------------------- */

    public static function stats(): array
    {
        return self::localize([
            ['value' => 10, 'suffix' => '+', 'label' => ['id' => 'Tahun pengalaman', 'en' => 'Years of experience']],
            ['value' => 150, 'suffix' => '+', 'label' => ['id' => 'Klien dan mitra', 'en' => 'Clients and partners']],
            ['value' => 300, 'suffix' => '+', 'label' => ['id' => 'Proyek terselesaikan', 'en' => 'Completed projects']],
            ['value' => count(self::rawUnits()), 'suffix' => '', 'label' => ['id' => 'Pilar bisnis', 'en' => 'Business pillars']],
        ]);
    }

    /* ---------------------------------------------------------------------
     | Portofolio (DUMMY: judul, klien, dan gambar contoh)
     * ------------------------------------------------------------------- */

    /** Semua proyek (DUMMY: judul, klien, dan gambar contoh). */
    public static function portfolio(): array
    {
        $projects = self::localize([
            [
                'slug' => 'sistem-informasi-akademik',
                'unit' => 'software',
                'title' => ['id' => 'Sistem Informasi Akademik Terpadu', 'en' => 'Integrated Academic Information System'],
                'client' => 'Universitas Contoh',
                'year' => 2025,
                'image' => 'img/dummy/project-1.jpg',
                'summary' => ['id' => 'Platform akademik berbasis web untuk pengelolaan data mahasiswa dan perkuliahan.', 'en' => 'A web-based academic platform for managing student and course data.'],
                'description' => ['id' => 'Sistem terpadu yang menggabungkan pendaftaran, jadwal kuliah, nilai, dan laporan akademik dalam satu platform yang mudah diakses civitas akademika.', 'en' => 'An integrated system combining registration, class schedules, grades, and academic reports in one platform that is easy for the academic community to access.'],
                'scope' => [['id' => 'Analisis kebutuhan dan perancangan sistem', 'en' => 'Requirements analysis and system design'], ['id' => 'Pengembangan aplikasi web', 'en' => 'Web application development'], ['id' => 'Pelatihan pengguna dan dukungan', 'en' => 'User training and support']],
            ],
            [
                'slug' => 'laboratorium-robotika-sekolah',
                'unit' => 'robotics',
                'title' => ['id' => 'Laboratorium Robotika Sekolah', 'en' => 'School Robotics Laboratory'],
                'client' => 'SMA Negeri Contoh',
                'year' => 2025,
                'image' => 'img/dummy/project-2.jpg',
                'summary' => ['id' => 'Pengadaan peralatan dan pelatihan guru untuk laboratorium robotika.', 'en' => 'Equipment procurement and teacher training for a robotics laboratory.'],
                'description' => ['id' => 'Penyiapan laboratorium robotika lengkap dengan perangkat, modul belajar, dan pelatihan bagi guru pengampu.', 'en' => 'Setting up a complete robotics laboratory with equipment, learning modules, and training for teaching staff.'],
                'scope' => [['id' => 'Pengadaan perangkat robotika', 'en' => 'Robotics equipment procurement'], ['id' => 'Penyusunan modul belajar', 'en' => 'Learning module development'], ['id' => 'Pelatihan guru', 'en' => 'Teacher training']],
            ],
            [
                'slug' => 'jaringan-dan-cctv-kantor-pemerintah',
                'unit' => 'networking-services',
                'title' => ['id' => 'Jaringan dan CCTV Kantor Pemerintah', 'en' => 'Government Office Network and CCTV'],
                'client' => 'Pemkab Contoh',
                'year' => 2024,
                'image' => 'img/dummy/project-3.jpg',
                'summary' => ['id' => 'Perancangan dan instalasi jaringan serta pengawasan CCTV terintegrasi.', 'en' => 'Design and installation of the network and integrated CCTV monitoring.'],
                'description' => ['id' => 'Pembangunan jaringan kantor dan sistem pengawasan CCTV terpusat untuk mendukung operasional dan keamanan gedung.', 'en' => 'Building the office network and a centralized CCTV system to support operations and building security.'],
                'scope' => [['id' => 'Survei dan perancangan jaringan', 'en' => 'Survey and network design'], ['id' => 'Instalasi jaringan dan CCTV', 'en' => 'Network and CCTV installation'], ['id' => 'Pemeliharaan berkala', 'en' => 'Routine maintenance']],
            ],
            [
                'slug' => 'pengadaan-laptop-dinas-pendidikan',
                'unit' => 'hardware',
                'title' => ['id' => 'Pengadaan Laptop Dinas Pendidikan', 'en' => 'Education Office Laptop Procurement'],
                'client' => 'Dinas Pendidikan Contoh',
                'year' => 2025,
                'image' => 'img/dummy/project-4.jpg',
                'summary' => ['id' => 'Pengadaan perangkat kerja untuk puluhan unit kerja dinas.', 'en' => 'Procurement of work devices for dozens of office units.'],
                'description' => ['id' => 'Penyediaan laptop dan perangkat penunjang sesuai spesifikasi, dengan pengiriman bertahap dan garansi resmi.', 'en' => 'Supply of laptops and accessories to specification, with staged delivery and official warranty.'],
                'scope' => [['id' => 'Pengadaan perangkat', 'en' => 'Device procurement'], ['id' => 'Pengiriman dan konfigurasi awal', 'en' => 'Delivery and initial setup'], ['id' => 'Garansi dan servis', 'en' => 'Warranty and servicing']],
            ],
            [
                'slug' => 'pelatihan-transformasi-digital-asn',
                'unit' => 'training-research-centre',
                'title' => ['id' => 'Pelatihan Transformasi Digital ASN', 'en' => 'Digital Transformation Training for Civil Servants'],
                'client' => 'Pemkab Contoh',
                'year' => 2024,
                'image' => 'img/dummy/project-5.jpg',
                'summary' => ['id' => 'Program pelatihan keterampilan digital bagi aparatur.', 'en' => 'A digital skills training program for civil servants.'],
                'description' => ['id' => 'Rangkaian pelatihan tatap muka dan daring untuk meningkatkan kemampuan aparatur dalam memanfaatkan layanan digital.', 'en' => 'A series of in-person and online sessions to improve civil servants’ ability to use digital services.'],
                'scope' => [['id' => 'Penyusunan kurikulum pelatihan', 'en' => 'Training curriculum design'], ['id' => 'Penyelenggaraan kelas dan workshop', 'en' => 'Classes and workshops'], ['id' => 'Evaluasi dan laporan hasil', 'en' => 'Evaluation and results report']],
            ],
            [
                'slug' => 'aplikasi-layanan-publik-desa',
                'unit' => 'software',
                'title' => ['id' => 'Aplikasi Layanan Publik Desa', 'en' => 'Village Public Service Application'],
                'client' => 'Pemerintah Desa Contoh',
                'year' => 2024,
                'image' => 'img/dummy/project-6.jpg',
                'summary' => ['id' => 'Aplikasi pengajuan layanan administrasi warga secara daring.', 'en' => 'An application for residents to request administrative services online.'],
                'description' => ['id' => 'Aplikasi yang memudahkan warga mengajukan surat dan layanan administrasi, serta membantu perangkat desa mengelola permohonan.', 'en' => 'An application that helps residents request letters and administrative services and helps village officials manage requests.'],
                'scope' => [['id' => 'Perancangan alur layanan', 'en' => 'Service flow design'], ['id' => 'Pengembangan aplikasi', 'en' => 'Application development'], ['id' => 'Pendampingan operasional', 'en' => 'Operational mentoring']],
            ],
            [
                'slug' => 'kompetisi-robotika-pelajar',
                'unit' => 'robotics',
                'title' => ['id' => 'Program Kompetisi Robotika Pelajar', 'en' => 'Student Robotics Competition Program'],
                'client' => 'Dinas Pendidikan Contoh',
                'year' => 2023,
                'image' => 'img/dummy/project-7.jpg',
                'summary' => ['id' => 'Pendampingan tim pelajar menuju kompetisi robotika.', 'en' => 'Coaching student teams toward robotics competitions.'],
                'description' => ['id' => 'Program pembinaan beberapa bulan yang membekali pelajar dengan dasar robotika, pemrograman, dan kerja tim.', 'en' => 'A multi-month coaching program equipping students with robotics fundamentals, programming, and teamwork.'],
                'scope' => [['id' => 'Pembinaan rutin', 'en' => 'Regular coaching'], ['id' => 'Penyediaan perangkat latihan', 'en' => 'Practice equipment supply'], ['id' => 'Pendampingan lomba', 'en' => 'Competition support']],
            ],
            [
                'slug' => 'pengadaan-atk-instansi',
                'unit' => 'paperking',
                'title' => ['id' => 'Pengadaan ATK Tahunan Instansi', 'en' => 'Annual Office Supplies for an Agency'],
                'client' => 'Kantor Contoh',
                'year' => 2025,
                'image' => 'img/dummy/project-8.jpg',
                'summary' => ['id' => 'Pasokan alat tulis kantor terjadwal sepanjang tahun.', 'en' => 'Scheduled supply of office stationery throughout the year.'],
                'description' => ['id' => 'Pemenuhan kebutuhan alat tulis dan perlengkapan kantor dengan jadwal pengiriman rutin dan pelaporan stok.', 'en' => 'Fulfilling stationery and office supply needs with a regular delivery schedule and stock reporting.'],
                'scope' => [['id' => 'Penyusunan daftar kebutuhan', 'en' => 'Needs list preparation'], ['id' => 'Pengiriman berkala', 'en' => 'Scheduled delivery'], ['id' => 'Pelaporan stok', 'en' => 'Stock reporting']],
            ],
            [
                'slug' => 'katering-konferensi-daerah',
                'unit' => 'dapur-kirana',
                'title' => ['id' => 'Katering Konferensi Daerah', 'en' => 'Regional Conference Catering'],
                'client' => 'Panitia Contoh',
                'year' => 2025,
                'image' => 'img/dummy/project-9.jpg',
                'summary' => ['id' => 'Penyediaan konsumsi untuk acara tiga hari dengan ratusan peserta.', 'en' => 'Catering for a three-day event with hundreds of participants.'],
                'description' => ['id' => 'Penyajian makanan dan minuman untuk seluruh sesi acara dengan pengaturan menu, jadwal saji, dan kebersihan yang terjaga.', 'en' => 'Serving food and beverages for all event sessions with managed menus, serving schedules, and maintained hygiene.'],
                'scope' => [['id' => 'Perencanaan menu', 'en' => 'Menu planning'], ['id' => 'Penyajian harian', 'en' => 'Daily service'], ['id' => 'Pengelolaan kebersihan', 'en' => 'Hygiene management']],
            ],
        ]);

        foreach ($projects as &$project) {
            $project['unit_name'] = self::unit($project['unit'])['name'] ?? '';
        }

        return $projects;
    }

    /** Proyek unggulan untuk beranda (3 pertama). */
    public static function projects(): array
    {
        return array_slice(self::portfolio(), 0, 3);
    }

    /* ---------------------------------------------------------------------
     | Klien & mitra
     |   Logo asli di public/img/clients/{kategori}/{nomor}.png (1.png, 2.png, ...).
     |   Nama klien belum tersedia, jadi yang tampil hanya logo (alt = "Kategori nomor").
     |   Jumlah logo per kategori diatur di CLIENT_FOLDERS; tambah logo baru = naikkan angkanya.
     * ------------------------------------------------------------------- */

    /** kategori (= nama folder) => jumlah logo. Urutan di sini = urutan tampil. */
    protected const CLIENT_FOLDERS = [
        'kementerian' => 4,
        'pemerintah' => 18,
        'lembaga' => 13,
        'universitas' => 25,
        'organisasi' => 4,
        'perusahaan' => 6,
        'swasta' => 20,
    ];

    /** Path logo; mendukung png/jpg/jpeg/webp/svg dan folder "pemerintahan" sebagai cadangan "pemerintah". */
    protected static function clientLogo(string $category, int $n): string
    {
        $folder = $category;

        if ($category === 'pemerintah'
            && ! is_dir(public_path('img/clients/pemerintah'))
            && is_dir(public_path('img/clients/pemerintahan'))) {
            $folder = 'pemerintahan';
        }

        foreach (['png', 'jpg', 'jpeg', 'webp', 'svg'] as $ext) {
            $path = "img/clients/{$folder}/{$n}.{$ext}";

            if (file_exists(public_path($path))) {
                return $path;
            }
        }

        return "img/clients/{$folder}/{$n}.png";
    }

    /**
     * Semua logo klien. Urutan selang-seling antar kategori (agar strip beranda beragam);
     * di halaman Klien & Mitra urutan relatif per kategori tetap 1, 2, 3, ...
     * $limit membatasi jumlah (dipakai strip beranda).
     */
    public static function clients(?int $limit = null): array
    {
        $labels = self::clientCategories();
        $max = max(self::CLIENT_FOLDERS);
        $items = [];

        for ($n = 1; $n <= $max; $n++) {
            foreach (self::CLIENT_FOLDERS as $category => $count) {
                if ($n > $count) {
                    continue;
                }

                $items[] = [
                    'name' => $labels[$category].' '.$n,
                    'category' => $category,
                    'logo' => self::clientLogo($category, $n),
                ];
            }
        }

        return $limit ? array_slice($items, 0, $limit) : $items;
    }

    /* ---------------------------------------------------------------------
     | Berita (DUMMY)
     * ------------------------------------------------------------------- */

    /** Kategori berita (slug netral bahasa agar tombol ID | EN tetap benar). */
    protected const NEWS_CATEGORIES = [
        'activities' => ['id' => 'Kegiatan', 'en' => 'Activities'],
        'community' => ['id' => 'Komunitas', 'en' => 'Community'],
        'business' => ['id' => 'Bisnis', 'en' => 'Business'],
        'technology' => ['id' => 'Teknologi', 'en' => 'Technology'],
        'education' => ['id' => 'Edukasi', 'en' => 'Education'],
    ];

    /** Semua berita (DUMMY), terbaru lebih dulu. */
    public static function news(): array
    {
        return self::localize([
            [
                'slug' => 'peresmian-laboratorium-robotika',
                'category_slug' => 'activities',
                'category' => self::NEWS_CATEGORIES['activities'],
                'date' => '2026-10-12',
                'image' => 'img/dummy/news-1.jpg',
                'title' => ['id' => 'Peresmian Laboratorium Robotika di Sekolah Mitra', 'en' => 'Robotics Laboratory Inauguration at a Partner School'],
                'excerpt' => ['id' => 'Laboratorium baru diharapkan membuka akses belajar robotika bagi lebih banyak siswa.', 'en' => 'The new laboratory is expected to open robotics learning to more students.'],
                'body' => [
                    ['id' => 'Laboratorium robotika baru diresmikan di sekolah mitra DECAA.ID dengan dihadiri guru, siswa, dan perwakilan dinas terkait.', 'en' => 'A new robotics laboratory was inaugurated at a DECAA.ID partner school, attended by teachers, students, and representatives of the education office.'],
                    ['h' => ['id' => 'Fasilitas yang disiapkan', 'en' => 'Facilities provided']],
                    ['id' => 'Laboratorium dilengkapi perangkat robotika, modul belajar bertahap, dan pelatihan bagi guru pengampu agar pemanfaatannya berkelanjutan.', 'en' => 'The laboratory comes with robotics kits, staged learning modules, and training for teachers so its use can be sustained.'],
                ],
            ],
            [
                'slug' => 'pelatihan-literasi-digital',
                'category_slug' => 'community',
                'category' => self::NEWS_CATEGORIES['community'],
                'date' => '2026-09-28',
                'image' => 'img/dummy/news-2.jpg',
                'title' => ['id' => 'Pelatihan Literasi Digital untuk Masyarakat', 'en' => 'Digital Literacy Training for the Community'],
                'excerpt' => ['id' => 'Komunitas Peduli Digital menggelar pelatihan dasar penggunaan teknologi bagi warga.', 'en' => 'Komunitas Peduli Digital held basic technology training for residents.'],
                'body' => [
                    ['id' => 'Komunitas Peduli Digital mengadakan pelatihan dasar penggunaan ponsel, internet, dan layanan digital bagi warga sekitar.', 'en' => 'Komunitas Peduli Digital held basic training on using phones, the internet, and digital services for local residents.'],
                    ['h' => ['id' => 'Materi pelatihan', 'en' => 'Training topics']],
                    ['id' => 'Peserta belajar mengenali informasi yang benar, menjaga keamanan akun, dan memanfaatkan layanan digital untuk kebutuhan sehari-hari.', 'en' => 'Participants learned to recognize reliable information, protect their accounts, and use digital services for everyday needs.'],
                ],
            ],
            [
                'slug' => 'kerja-sama-penyediaan-perangkat',
                'category_slug' => 'business',
                'category' => self::NEWS_CATEGORIES['business'],
                'date' => '2026-09-05',
                'image' => 'img/dummy/news-3.jpg',
                'title' => ['id' => 'Kerja Sama Penyediaan Perangkat TI untuk Instansi', 'en' => 'IT Equipment Supply Partnership for Agencies'],
                'excerpt' => ['id' => 'Kerja sama ini mencakup pengadaan perangkat dan layanan purna jual.', 'en' => 'The partnership covers equipment procurement and after-sales service.'],
                'body' => [
                    ['id' => 'DECAA.ID menandatangani kerja sama penyediaan perangkat teknologi informasi untuk kebutuhan operasional instansi mitra.', 'en' => 'DECAA.ID signed a partnership to supply information technology equipment for a partner agency’s operations.'],
                    ['h' => ['id' => 'Cakupan kerja sama', 'en' => 'Scope of cooperation']],
                    ['id' => 'Selain pengadaan, kerja sama mencakup konfigurasi awal, garansi, dan layanan servis berkala selama masa kontrak.', 'en' => 'Beyond procurement, the partnership covers initial setup, warranty, and routine servicing throughout the contract period.'],
                ],
            ],
            [
                'slug' => 'tips-keamanan-jaringan-kantor',
                'category_slug' => 'technology',
                'category' => self::NEWS_CATEGORIES['technology'],
                'date' => '2026-08-20',
                'image' => 'img/dummy/news-4.jpg',
                'title' => ['id' => 'Lima Langkah Menjaga Keamanan Jaringan Kantor', 'en' => 'Five Steps to Keep Your Office Network Secure'],
                'excerpt' => ['id' => 'Langkah sederhana yang dapat diterapkan kantor untuk mengurangi risiko gangguan jaringan.', 'en' => 'Simple steps any office can take to reduce network risks.'],
                'body' => [
                    ['id' => 'Keamanan jaringan tidak harus rumit. Kebiasaan dasar yang konsisten sudah mengurangi sebagian besar risiko.', 'en' => 'Network security does not have to be complicated. Consistent basic habits already remove most of the risk.'],
                    ['h' => ['id' => 'Mulai dari yang mendasar', 'en' => 'Start with the basics']],
                    ['id' => 'Perbarui perangkat secara berkala, batasi hak akses, pisahkan jaringan tamu, dan lakukan pencadangan rutin.', 'en' => 'Update devices regularly, limit access rights, separate the guest network, and back up routinely.'],
                ],
            ],
            [
                'slug' => 'workshop-guru-pemrograman',
                'category_slug' => 'education',
                'category' => self::NEWS_CATEGORIES['education'],
                'date' => '2026-08-02',
                'image' => 'img/dummy/news-5.jpg',
                'title' => ['id' => 'Workshop Pemrograman Dasar untuk Guru', 'en' => 'Basic Programming Workshop for Teachers'],
                'excerpt' => ['id' => 'Guru dari beberapa sekolah belajar dasar pemrograman untuk diterapkan di kelas.', 'en' => 'Teachers from several schools learned programming basics for classroom use.'],
                'body' => [
                    ['id' => 'Training & Research Centre menyelenggarakan workshop sehari untuk membekali guru dengan dasar pemrograman.', 'en' => 'The Training & Research Centre ran a one-day workshop to equip teachers with programming basics.'],
                    ['h' => ['id' => 'Hasil yang diharapkan', 'en' => 'Expected outcomes']],
                    ['id' => 'Peserta pulang dengan contoh kegiatan kelas yang dapat langsung dipraktikkan bersama siswa.', 'en' => 'Participants left with classroom activities they can use with students right away.'],
                ],
            ],
            [
                'slug' => 'pembaruan-layanan-purna-jual',
                'category_slug' => 'business',
                'category' => self::NEWS_CATEGORIES['business'],
                'date' => '2026-07-15',
                'image' => 'img/dummy/news-6.jpg',
                'title' => ['id' => 'Pembaruan Layanan Purna Jual DECAA.ID', 'en' => 'Update to DECAA.ID After-Sales Service'],
                'excerpt' => ['id' => 'Alur pengaduan dan servis kini lebih jelas dengan satu pintu layanan.', 'en' => 'Complaint and service flows are now clearer with a single service desk.'],
                'body' => [
                    ['id' => 'Mulai bulan ini, seluruh permintaan servis dan bantuan teknis dilayani melalui satu pintu agar penanganan lebih cepat.', 'en' => 'Starting this month, all service and technical support requests go through a single desk for faster handling.'],
                    ['h' => ['id' => 'Apa yang berubah', 'en' => 'What changes']],
                    ['id' => 'Klien mendapat nomor tiket untuk setiap permintaan sehingga perkembangan penanganan dapat dipantau.', 'en' => 'Clients receive a ticket number for each request so progress can be tracked.'],
                ],
            ],
            [
                'slug' => 'relawan-komunitas-peduli-digital',
                'category_slug' => 'community',
                'category' => self::NEWS_CATEGORIES['community'],
                'date' => '2026-07-01',
                'image' => 'img/dummy/news-7.jpg',
                'title' => ['id' => 'Relawan Baru Bergabung di Komunitas Peduli Digital', 'en' => 'New Volunteers Join Komunitas Peduli Digital'],
                'excerpt' => ['id' => 'Puluhan relawan siap mendampingi kegiatan literasi digital di berbagai wilayah.', 'en' => 'Dozens of volunteers are ready to support digital literacy activities across regions.'],
                'body' => [
                    ['id' => 'Komunitas Peduli Digital menyambut relawan baru yang akan mendampingi kegiatan literasi digital di sekolah dan desa.', 'en' => 'Komunitas Peduli Digital welcomed new volunteers who will support digital literacy activities in schools and villages.'],
                    ['h' => ['id' => 'Cara bergabung', 'en' => 'How to join']],
                    ['id' => 'Siapa pun yang ingin berbagi pengetahuan teknologi dapat mendaftar sebagai relawan melalui halaman Komunitas.', 'en' => 'Anyone who wants to share technology knowledge can sign up as a volunteer through the Community page.'],
                ],
            ],
            [
                'slug' => 'tren-otomasi-sekolah',
                'category_slug' => 'technology',
                'category' => self::NEWS_CATEGORIES['technology'],
                'date' => '2026-06-18',
                'image' => 'img/dummy/news-8.jpg',
                'title' => ['id' => 'Otomasi Sederhana yang Mulai Masuk ke Sekolah', 'en' => 'Simple Automation Entering Schools'],
                'excerpt' => ['id' => 'Sekolah mulai memanfaatkan otomasi untuk pembelajaran dan pengelolaan fasilitas.', 'en' => 'Schools are starting to use automation for learning and facility management.'],
                'body' => [
                    ['id' => 'Otomasi sederhana, seperti sensor dan pengendali, kini dipakai untuk pembelajaran sains dan pengelolaan fasilitas sekolah.', 'en' => 'Simple automation, such as sensors and controllers, is now used for science lessons and school facility management.'],
                    ['h' => ['id' => 'Peluang bagi sekolah', 'en' => 'Opportunities for schools']],
                    ['id' => 'Pendekatan ini melatih siswa berpikir sistematis sambil membantu sekolah bekerja lebih efisien.', 'en' => 'This approach trains students to think systematically while helping schools work more efficiently.'],
                ],
            ],
            [
                'slug' => 'kunjungan-sekolah-mitra',
                'category_slug' => 'activities',
                'category' => self::NEWS_CATEGORIES['activities'],
                'date' => '2026-06-04',
                'image' => 'img/dummy/news-9.jpg',
                'title' => ['id' => 'Kunjungan ke Sekolah Mitra Wilayah Pacitan', 'en' => 'Visit to Partner Schools in the Pacitan Area'],
                'excerpt' => ['id' => 'Tim DECAA.ID meninjau penggunaan perangkat dan berdiskusi dengan guru.', 'en' => 'The DECAA.ID team reviewed equipment use and talked with teachers.'],
                'body' => [
                    ['id' => 'Tim DECAA.ID mengunjungi sekolah mitra di wilayah Pacitan untuk melihat langsung pemanfaatan perangkat yang telah terpasang.', 'en' => 'The DECAA.ID team visited partner schools in the Pacitan area to see how installed equipment is being used.'],
                    ['h' => ['id' => 'Masukan dari lapangan', 'en' => 'Feedback from the field']],
                    ['id' => 'Masukan dari guru dan siswa dicatat untuk perbaikan layanan dan bahan pelatihan berikutnya.', 'en' => 'Feedback from teachers and students was recorded to improve services and future training materials.'],
                ],
            ],
        ]);
    }

    /** 3 berita terbaru untuk beranda. */
    public static function posts(): array
    {
        return array_slice(self::news(), 0, 3);
    }

    public static function newsCategories(): array
    {
        $out = [];
        foreach (self::NEWS_CATEGORIES as $slug => $name) {
            $out[$slug] = self::localize($name);
        }

        return $out;
    }

    public static function newsPage(): array
    {
        return self::localize([
            'eyebrow' => ['id' => 'Berita', 'en' => 'News'],
            'title' => ['id' => 'Kabar terbaru dari DECAA.ID', 'en' => 'Latest from DECAA.ID'],
            'lead' => [
                'id' => 'Informasi kegiatan, proyek, dan perkembangan terbaru kami.',
                'en' => 'Updates on our activities, projects, and latest developments.',
            ],
            'all' => ['id' => 'Semua', 'en' => 'All'],
            'category_lead' => ['id' => 'Berita dalam kategori ini.', 'en' => 'News in this category.'],
            'empty' => ['id' => 'Belum ada berita di kategori ini.', 'en' => 'No news in this category yet.'],
            'related' => ['id' => 'Berita terkait', 'en' => 'Related news'],
            'share' => ['id' => 'Bagikan', 'en' => 'Share'],
            'copy_link' => ['id' => 'Salin tautan', 'en' => 'Copy link'],
            'copied' => ['id' => 'Tautan disalin', 'en' => 'Link copied'],
            'back' => ['id' => 'Semua berita', 'en' => 'All news'],
            'read_more' => ['id' => 'Baca selengkapnya', 'en' => 'Read more'],
        ]);
    }

    /* ---------------------------------------------------------------------
     | Detail (diisi lengkap di tahap A6/A7)
     * ------------------------------------------------------------------- */

    public static function project(string $slug): ?array
    {
        foreach (self::portfolio() as $project) {
            if ($project['slug'] === $slug) {
                return $project;
            }
        }

        return null;
    }

    public static function post(string $slug): ?array
    {
        foreach (self::news() as $post) {
            if ($post['slug'] === $slug) {
                return $post;
            }
        }

        return null;
    }

    public static function category(string $slug): ?array
    {
        $name = self::newsCategories()[$slug] ?? null;

        return $name ? ['slug' => $slug, 'name' => $name] : null;
    }

    /* ---------------------------------------------------------------------
     | Label UI kecil untuk halaman dalam (A4 dst.)
     * ------------------------------------------------------------------- */

    public static function ui(): array
    {
        return self::localize([
            'home' => ['id' => 'Beranda', 'en' => 'Home'],
            'learn_more' => ['id' => 'Selengkapnya', 'en' => 'Learn more'],
            'request_docs' => ['id' => 'Minta salinan dokumen', 'en' => 'Request document copies'],
            'available_on_request' => ['id' => 'Tersedia atas permintaan', 'en' => 'Available on request'],
            'our_offices' => ['id' => 'Kantor kami', 'en' => 'Our offices'],
            'business_units' => ['id' => 'Pilar Bisnis', 'en' => 'Business Pillars'],
            'offices' => ['id' => 'Kantor', 'en' => 'Offices'],
            'sectors' => ['id' => 'Sektor Layanan', 'en' => 'Sectors Served'],
            'view_all_units' => ['id' => 'Lihat Semua Pilar Bisnis', 'en' => 'View All Business Pillars'],
            'view_vision' => ['id' => 'Lihat Visi & Misi', 'en' => 'View Vision & Mission'],
            'jump_to_year' => ['id' => 'Loncat ke tahun', 'en' => 'Jump to year'],
        ]);
    }

    /* ---------------------------------------------------------------------
     | Tentang Kami (DUMMY: semua teks, nama, dan tahun adalah contoh)
     * ------------------------------------------------------------------- */

    public static function about(): array
    {
        return self::localize([
            'profile' => [
                'eyebrow' => ['id' => 'Tentang Kami', 'en' => 'About Us'],
                'title' => ['id' => 'Profil Perusahaan', 'en' => 'Company Profile'],
                'lead' => [
                    'id' => 'DECAA.ID adalah perusahaan yang menaungi pilar bisnis teknologi dan layanan pendukung bagi institusi pemerintah, pendidikan, dan swasta.',
                    'en' => 'DECAA.ID is a company overseeing technology and support-service business pillars for government, education, and private institutions.',
                ],
                'story_title' => ['id' => 'Siapa kami', 'en' => 'Who we are'],
                'story' => [
                    [
                        'id' => 'Kami memulai langkah dari kebutuhan sederhana: institusi membutuhkan mitra yang bisa dipercaya untuk urusan teknologi, dari perangkat hingga aplikasi. Seiring waktu, kebutuhan itu tumbuh dan kami menjawabnya dengan menghadirkan pilar-pilar bisnis yang saling melengkapi.',
                        'en' => 'We started from a simple need: institutions wanted a trustworthy partner for technology, from devices to applications. As the need grew, we responded by building business pillars that complement one another.',
                    ],
                    [
                        'id' => 'Hari ini DECAA.ID bekerja dari kantor pusat di Jakarta dengan cabang di Pacitan dan Ponorogo, melayani klien dengan pendekatan yang sama: jelas, terukur, dan bertanggung jawab hingga layanan purna jual.',
                        'en' => 'Today DECAA.ID operates from its headquarters in Jakarta with branches in Pacitan and Ponorogo, serving clients with the same approach: clear, measurable, and accountable through after-sales support.',
                    ],
                ],
                // CHAIRMAN MESSAGE. DUMMY: ganti nama, pesan, dan foto (public/img/chairman.jpg, potret vertikal 4:5)
                'chairman' => [
                    'title' => ['id' => 'Chairman Message', 'en' => 'Chairman’s Message'],
                    'name' => 'Nama Chairman',
                    'role' => ['id' => 'Chairman', 'en' => 'Chairman'],
                    'photo' => 'img/chairman.jpg',
                    'headline' => [
                        'id' => 'Teknologi yang baik selalu dimulai dari kepercayaan.',
                        'en' => 'Good technology always starts with trust.',
                    ],
                    'message' => [
                        [
                            'id' => 'Sejak pertama berdiri, kami percaya bahwa teknologi hanya bermakna bila benar-benar membantu orang bekerja lebih baik. Itulah yang menjadi pegangan kami dalam melayani institusi pemerintah, pendidikan, maupun swasta.',
                            'en' => 'Since the day we started, we have believed that technology only matters when it truly helps people work better. That is what guides us in serving government, education, and private institutions alike.',
                        ],
                        [
                            'id' => 'Kami terus membangun pilar-pilar bisnis yang saling melengkapi, memperkuat tim, dan menjaga mutu layanan hingga purna jual. Kepercayaan klien adalah aset terbesar kami, dan kami menjaganya dengan kerja yang jelas, terukur, dan bertanggung jawab.',
                            'en' => 'We keep building business pillars that complement one another, strengthening our team, and maintaining service quality through after-sales support. Client trust is our greatest asset, and we protect it with work that is clear, measurable, and accountable.',
                        ],
                        [
                            'id' => 'Terima kasih kepada seluruh klien, mitra, dan keluarga besar DECAA.ID. Mari terus bertumbuh bersama.',
                            'en' => 'Thank you to all our clients, partners, and the DECAA.ID family. Let us keep growing together.',
                        ],
                    ],
                ],
            ],

            'vision' => [
                'eyebrow' => ['id' => 'Tentang Kami', 'en' => 'About Us'],
                'title' => ['id' => 'Visi & Misi', 'en' => 'Vision & Mission'],
                'lead' => [
                    'id' => 'Arah yang kami tuju dan cara kami mencapainya.',
                    'en' => 'The direction we are heading and how we get there.',
                ],
                'vision_label' => ['id' => 'Visi', 'en' => 'Vision'],
                'vision' => [
                    'id' => 'Menjadi mitra teknologi dan layanan terpadu yang paling dipercaya oleh institusi dan bisnis di Indonesia.',
                    'en' => 'To be the most trusted integrated technology and services partner for institutions and businesses in Indonesia.',
                ],
                'mission_label' => ['id' => 'Misi', 'en' => 'Mission'],
                'missions' => [
                    ['id' => 'Menyediakan solusi teknologi yang tepat guna dan sesuai kebutuhan klien.', 'en' => 'Provide fit-for-purpose technology solutions that match client needs.'],
                    ['id' => 'Membangun ekosistem pilar bisnis yang saling melengkapi dan efisien.', 'en' => 'Build an ecosystem of complementary and efficient business pillars.'],
                    ['id' => 'Meningkatkan kompetensi sumber daya manusia melalui pelatihan dan riset.', 'en' => 'Improve human resource competence through training and research.'],
                    ['id' => 'Menjaga mutu layanan dengan proses kerja yang terukur dan transparan.', 'en' => 'Maintain service quality through measurable and transparent processes.'],
                    ['id' => 'Berkontribusi pada literasi dan pemerataan akses teknologi bagi masyarakat.', 'en' => 'Contribute to digital literacy and equal access to technology for society.'],
                ],
                'values_title' => ['id' => 'Nilai-nilai kami', 'en' => 'Our values'],
                'values' => [
                    ['title' => ['id' => 'Integritas', 'en' => 'Integrity'], 'text' => ['id' => 'Jujur dalam ucapan, tepat janji dalam pekerjaan.', 'en' => 'Honest in word, dependable in delivery.']],
                    ['title' => ['id' => 'Profesional', 'en' => 'Professionalism'], 'text' => ['id' => 'Bekerja dengan standar dan disiplin yang jelas.', 'en' => 'Working to clear standards and discipline.']],
                    ['title' => ['id' => 'Inovasi', 'en' => 'Innovation'], 'text' => ['id' => 'Terus belajar dan mencari cara yang lebih baik.', 'en' => 'Always learning and finding better ways.']],
                    ['title' => ['id' => 'Kolaborasi', 'en' => 'Collaboration'], 'text' => ['id' => 'Bekerja bersama klien, mitra, dan antarpilar.', 'en' => 'Working together with clients, partners, and across pillars.']],
                    ['title' => ['id' => 'Keberlanjutan', 'en' => 'Sustainability'], 'text' => ['id' => 'Memberi manfaat jangka panjang bagi bisnis dan masyarakat.', 'en' => 'Delivering long-term value for business and society.']],
                ],
            ],

            // ---------------------------------------------------------------
            // PERJALANAN KAMI (template, siap diisi).
            // Cara mengisi: tambah / ubah / hapus baris di 'milestones'.
            //   year  : tahun (teks, boleh "2018" atau "2018 - 2020")
            //   tag   : label pendek (mis. Awal, Tumbuh, Ekspansi)
            //   title : judul peristiwa
            //   text  : cerita singkat (2-3 kalimat)
            //   image : foto di public/img/journey/ (rasio 16:10 ideal).
            //           Bila file belum ada, tampil placeholder rapi.
            // Urutan tampil = urutan di sini. Kiri-kanan bergantian otomatis.
            // SEMUA ISI DI BAWAH ADALAH DUMMY.
            // ---------------------------------------------------------------
            'journey' => [
                'eyebrow' => ['id' => 'Tentang Kami', 'en' => 'About Us'],
                'title' => ['id' => 'Perjalanan Kami', 'en' => 'Our Journey'],
                'lead' => [
                    'id' => 'Dari langkah pertama yang sederhana hingga ekosistem yang saling melengkapi.',
                    'en' => 'From a simple first step to an ecosystem where every part supports the others.',
                ],
                'intro_title' => [
                    'id' => 'Setiap tahun membawa kami satu langkah lebih dekat pada klien.',
                    'en' => 'Every year has brought us one step closer to our clients.',
                ],
                'intro_text' => [
                    'id' => 'Berikut peristiwa penting yang membentuk DECAA.ID, dari awal berdiri hingga hari ini.',
                    'en' => 'These are the moments that shaped DECAA.ID, from the day we started until today.',
                ],
                'milestones' => [
                    [
                        'year' => '2018',
                        'tag' => ['id' => 'Awal', 'en' => 'Beginning'],
                        'title' => ['id' => 'DECAA.ID mulai beroperasi', 'en' => 'DECAA.ID begins operating'],
                        'text' => ['id' => 'Kami memulai dengan layanan teknologi informasi untuk institusi di sekitar kami.', 'en' => 'We started with information technology services for institutions around us.'],
                        'image' => 'img/journey/2018.jpg',
                    ],
                    [
                        'year' => '2019',
                        'tag' => ['id' => 'Tumbuh', 'en' => 'Growth'],
                        'title' => ['id' => 'Memperluas ke perangkat keras dan jaringan', 'en' => 'Expanding into hardware and networking'],
                        'text' => ['id' => 'Kebutuhan klien mendorong kami menghadirkan layanan perangkat keras dan infrastruktur jaringan.', 'en' => 'Client needs led us to add hardware and network infrastructure services.'],
                        'image' => 'img/journey/2019.jpg',
                    ],
                    [
                        'year' => '2021',
                        'tag' => ['id' => 'Ekspansi', 'en' => 'Expansion'],
                        'title' => ['id' => 'Kantor cabang pertama dibuka', 'en' => 'First branch office opens'],
                        'text' => ['id' => 'Kehadiran di daerah mendekatkan layanan kami kepada klien dan memperkuat tim lapangan.', 'en' => 'A presence in the regions brought our services closer to clients and strengthened our field team.'],
                        'image' => 'img/journey/2021.jpg',
                    ],
                    [
                        'year' => '2022',
                        'tag' => ['id' => 'Kompetensi', 'en' => 'Capability'],
                        'title' => ['id' => 'Pilar pelatihan dan riset berdiri', 'en' => 'Training and research pillar established'],
                        'text' => ['id' => 'Kami membangun pusat pelatihan agar kompetensi tim dan klien terus bertumbuh.', 'en' => 'We built a training centre so the skills of our team and clients keep growing.'],
                        'image' => 'img/journey/2022.jpg',
                    ],
                    [
                        'year' => '2024',
                        'tag' => ['id' => 'Ekosistem', 'en' => 'Ecosystem'],
                        'title' => ['id' => 'Tujuh pilar bisnis dalam satu ekosistem', 'en' => 'Seven business pillars, one ecosystem'],
                        'text' => ['id' => 'Pilar-pilar bisnis kami kini saling melengkapi dan melayani kebutuhan klien secara terpadu.', 'en' => 'Our business pillars now complement each other and serve client needs in an integrated way.'],
                        'image' => 'img/journey/2024.jpg',
                    ],
                    [
                        'year' => '2026',
                        'tag' => ['id' => 'Hari ini', 'en' => 'Today'],
                        'title' => ['id' => 'Tampil dengan wajah baru', 'en' => 'A fresh look for DECAA.ID'],
                        'text' => ['id' => 'Situs dan identitas digital baru kami hadir untuk melayani klien dengan lebih jelas dan mudah.', 'en' => 'Our new website and digital identity make it clearer and easier to work with us.'],
                        'image' => 'img/journey/2026.jpg',
                    ],
                ],
                'ahead_title' => ['id' => 'Perjalanan ini belum selesai', 'en' => 'This journey is not over'],
                'ahead_text' => [
                    'id' => 'Kami terus belajar, membangun, dan bertumbuh bersama klien serta mitra. Ceritakan kebutuhan Anda, dan mari tulis bab berikutnya bersama.',
                    'en' => 'We keep learning, building, and growing with our clients and partners. Tell us what you need, and let us write the next chapter together.',
                ],
            ],

            'team' => [
                'eyebrow' => ['id' => 'Tentang Kami', 'en' => 'About Us'],
                'title' => ['id' => 'Jajaran Kepemimpinan', 'en' => 'Leadership'],
                'lead' => [
                    'id' => 'Orang-orang yang memimpin DECAA.ID dan pilar-pilar bisnisnya.',
                    'en' => 'The people leading DECAA.ID and its business pillars.',
                ],
                'members_title' => ['id' => 'Board of Executive', 'en' => 'Board of Executive'],
                // DUMMY: nama fiktif. Anggota PERTAMA = CEO, tampil di kartu besar sendiri di atas "Board of Executive".
                // 'role' hanya diisi untuk CEO; kosongkan (null) untuk lainnya.
                // Foto asli: public/img/team/{nomor}.jpg (potret vertikal 4:5, latar terang)
                'members' => [
                    [
                        'name' => 'Nama CEO',
                        'role' => ['id' => 'CEO', 'en' => 'CEO'],
                        // Opsional: ringkasan singkat, hanya tampil di kartu CEO. Hapus baris 'bio' bila tidak diperlukan.
                        'bio' => ['id' => 'Memimpin arah strategis dan pengembangan ekosistem DECAA.ID bersama seluruh pilar bisnis.', 'en' => 'Leads the strategic direction and development of the DECAA.ID ecosystem together with all business pillars.'],
                    ],
                    ['name' => 'Nama Anggota 2', 'role' => null],
                    ['name' => 'Nama Anggota 3', 'role' => null],
                    ['name' => 'Nama Anggota 4', 'role' => null],
                    ['name' => 'Nama Anggota 5', 'role' => null],
                    ['name' => 'Nama Anggota 6', 'role' => null],
                ],
            ],
        ]);
    }

    /* ---------------------------------------------------------------------
     | Detail pilar bisnis (A5, DUMMY: deskripsi dan layanan contoh)
     * ------------------------------------------------------------------- */

    public static function unitDetail(string $slug): ?array
    {
        $unit = self::unit($slug);

        if (! $unit) {
            return null;
        }

        $extra = self::localize([
            'software' => [
                'description' => ['id' => 'Kami merancang dan membangun sistem informasi yang sesuai alur kerja institusi Anda, dari analisis kebutuhan hingga pemeliharaan.', 'en' => 'We design and build information systems that fit your institution’s workflow, from requirements analysis through maintenance.'],
                'services' => [
                    ['title' => ['id' => 'Aplikasi web dan mobile', 'en' => 'Web and mobile applications'], 'text' => ['id' => 'Aplikasi khusus yang disesuaikan dengan proses bisnis.', 'en' => 'Custom applications tailored to your business processes.']],
                    ['title' => ['id' => 'Sistem informasi instansi', 'en' => 'Institutional information systems'], 'text' => ['id' => 'Akademik, kepegawaian, keuangan, dan layanan publik.', 'en' => 'Academic, HR, finance, and public service systems.']],
                    ['title' => ['id' => 'Integrasi dan migrasi data', 'en' => 'Integration and data migration'], 'text' => ['id' => 'Menghubungkan sistem lama dan baru dengan aman.', 'en' => 'Safely connecting legacy and new systems.']],
                    ['title' => ['id' => 'Pemeliharaan dan dukungan', 'en' => 'Maintenance and support'], 'text' => ['id' => 'Pembaruan berkala dan bantuan teknis berkelanjutan.', 'en' => 'Regular updates and ongoing technical support.']],
                ],
            ],
            'robotics' => [
                'description' => ['id' => 'Kami membawa robotika dan otomasi ke ruang kelas dan industri, lengkap dengan perangkat, kurikulum, dan pendampingan.', 'en' => 'We bring robotics and automation into classrooms and industry, complete with equipment, curriculum, and mentoring.'],
                'services' => [
                    ['title' => ['id' => 'Laboratorium robotika', 'en' => 'Robotics laboratories'], 'text' => ['id' => 'Perancangan dan pengadaan laboratorium untuk sekolah dan kampus.', 'en' => 'Design and procurement of labs for schools and universities.']],
                    ['title' => ['id' => 'Kurikulum dan modul belajar', 'en' => 'Curriculum and learning modules'], 'text' => ['id' => 'Materi bertahap dari dasar hingga lanjutan.', 'en' => 'Staged materials from beginner to advanced.']],
                    ['title' => ['id' => 'Pelatihan guru dan instruktur', 'en' => 'Teacher and instructor training'], 'text' => ['id' => 'Meningkatkan kemampuan pengajar menggunakan perangkat.', 'en' => 'Building educators’ ability to use the equipment.']],
                    ['title' => ['id' => 'Otomasi sederhana untuk industri', 'en' => 'Light industrial automation'], 'text' => ['id' => 'Solusi otomasi skala kecil dan menengah.', 'en' => 'Small and medium-scale automation solutions.']],
                ],
            ],
            'hardware' => [
                'description' => ['id' => 'Kami menyediakan perangkat teknologi informasi yang andal dan sesuai spesifikasi, dengan dukungan purna jual.', 'en' => 'We supply reliable IT equipment that meets specifications, backed by after-sales support.'],
                'services' => [
                    ['title' => ['id' => 'Komputer dan laptop', 'en' => 'Computers and laptops'], 'text' => ['id' => 'Perangkat kerja untuk kantor, sekolah, dan laboratorium.', 'en' => 'Workstations for offices, schools, and laboratories.']],
                    ['title' => ['id' => 'Server dan penyimpanan', 'en' => 'Servers and storage'], 'text' => ['id' => 'Perangkat pusat data skala instansi.', 'en' => 'Institution-scale data center equipment.']],
                    ['title' => ['id' => 'Periferal dan perangkat penunjang', 'en' => 'Peripherals and accessories'], 'text' => ['id' => 'Printer, proyektor, layar interaktif, dan lainnya.', 'en' => 'Printers, projectors, interactive displays, and more.']],
                    ['title' => ['id' => 'Garansi dan servis', 'en' => 'Warranty and servicing'], 'text' => ['id' => 'Layanan perbaikan dan penggantian yang jelas.', 'en' => 'Clear repair and replacement services.']],
                ],
            ],
            'networking-services' => [
                'description' => ['id' => 'Kami merancang, memasang, dan merawat infrastruktur jaringan agar operasional Anda berjalan stabil dan aman.', 'en' => 'We design, install, and maintain network infrastructure so your operations run stably and securely.'],
                'services' => [
                    ['title' => ['id' => 'Jaringan LAN dan Wi-Fi', 'en' => 'LAN and Wi-Fi networks'], 'text' => ['id' => 'Perancangan jaringan kabel dan nirkabel.', 'en' => 'Wired and wireless network design.']],
                    ['title' => ['id' => 'CCTV dan pengawasan', 'en' => 'CCTV and surveillance'], 'text' => ['id' => 'Instalasi dan integrasi sistem pemantauan.', 'en' => 'Installation and integration of monitoring systems.']],
                    ['title' => ['id' => 'Keamanan jaringan', 'en' => 'Network security'], 'text' => ['id' => 'Pengaturan akses, firewall, dan pemantauan.', 'en' => 'Access control, firewalls, and monitoring.']],
                    ['title' => ['id' => 'Pemeliharaan berkala', 'en' => 'Routine maintenance'], 'text' => ['id' => 'Pemeriksaan rutin dan penanganan gangguan.', 'en' => 'Routine checks and incident handling.']],
                ],
            ],
            'training-research-centre' => [
                'description' => ['id' => 'Kami menyelenggarakan pelatihan dan riset teknologi untuk meningkatkan kompetensi tim dan institusi Anda.', 'en' => 'We run technology training and research to raise the competence of your teams and institutions.'],
                'services' => [
                    ['title' => ['id' => 'Pelatihan teknologi informasi', 'en' => 'IT training'], 'text' => ['id' => 'Kelas untuk pemula hingga profesional.', 'en' => 'Classes from beginners to professionals.']],
                    ['title' => ['id' => 'Workshop dan bimbingan teknis', 'en' => 'Workshops and technical guidance'], 'text' => ['id' => 'Sesi praktik langsung dengan studi kasus.', 'en' => 'Hands-on sessions with real case studies.']],
                    ['title' => ['id' => 'Riset dan kajian teknologi', 'en' => 'Technology research and studies'], 'text' => ['id' => 'Kajian kebutuhan dan rekomendasi solusi.', 'en' => 'Needs assessments and solution recommendations.']],
                    ['title' => ['id' => 'Sertifikasi kompetensi', 'en' => 'Competency certification'], 'text' => ['id' => 'Pendampingan menuju sertifikasi yang relevan.', 'en' => 'Guidance toward relevant certifications.']],
                ],
            ],
            'paperking' => [
                'description' => ['id' => 'Kami memasok alat tulis dan perlengkapan kantor yang lengkap, tepat waktu, dan dengan harga yang jelas.', 'en' => 'We supply complete office stationery and equipment, on time and at clear prices.'],
                'services' => [
                    ['title' => ['id' => 'Alat tulis kantor', 'en' => 'Office stationery'], 'text' => ['id' => 'Kebutuhan harian perkantoran dan sekolah.', 'en' => 'Everyday needs for offices and schools.']],
                    ['title' => ['id' => 'Kertas dan cetakan', 'en' => 'Paper and printing'], 'text' => ['id' => 'Kertas, formulir, dan bahan cetak.', 'en' => 'Paper, forms, and printed materials.']],
                    ['title' => ['id' => 'Perlengkapan arsip dan ruang kerja', 'en' => 'Filing and workspace supplies'], 'text' => ['id' => 'Penataan dokumen dan area kerja.', 'en' => 'Document filing and workspace organization.']],
                    ['title' => ['id' => 'Pengadaan berkala', 'en' => 'Scheduled procurement'], 'text' => ['id' => 'Pasokan rutin sesuai jadwal instansi.', 'en' => 'Regular supply on your institution’s schedule.']],
                ],
            ],
            'dapur-kirana' => [
                'description' => ['id' => 'Kami menyajikan makanan dan minuman berkualitas untuk kegiatan instansi dan perusahaan, dikelola dengan standar kebersihan yang baik.', 'en' => 'We serve quality food and beverages for institutional and corporate events, managed to good hygiene standards.'],
                'services' => [
                    ['title' => ['id' => 'Katering kegiatan', 'en' => 'Event catering'], 'text' => ['id' => 'Konsumsi rapat, pelatihan, dan acara.', 'en' => 'Meals for meetings, training, and events.']],
                    ['title' => ['id' => 'Konsumsi harian kantor', 'en' => 'Daily office meals'], 'text' => ['id' => 'Paket makan rutin untuk karyawan.', 'en' => 'Regular meal packages for staff.']],
                    ['title' => ['id' => 'Kudapan dan minuman', 'en' => 'Snacks and beverages'], 'text' => ['id' => 'Pilihan kudapan dan minuman untuk berbagai acara.', 'en' => 'Snack and drink options for various occasions.']],
                    ['title' => ['id' => 'Menu sesuai kebutuhan', 'en' => 'Tailored menus'], 'text' => ['id' => 'Penyesuaian menu dan jumlah porsi.', 'en' => 'Menus and portions adjusted to your needs.']],
                ],
            ],
        ])[$slug] ?? ['description' => $unit['summary'], 'services' => []];

        $projects = array_values(array_filter(self::portfolio(), fn ($p) => $p['unit'] === $slug));

        return $unit + $extra + [
            'projects' => $projects,
            'steps' => self::localize([
                ['title' => ['id' => 'Konsultasi', 'en' => 'Consultation'], 'text' => ['id' => 'Memahami kebutuhan dan kendala Anda.', 'en' => 'Understanding your needs and constraints.']],
                ['title' => ['id' => 'Perancangan', 'en' => 'Planning'], 'text' => ['id' => 'Menyusun solusi, jadwal, dan anggaran yang jelas.', 'en' => 'Preparing a clear solution, schedule, and budget.']],
                ['title' => ['id' => 'Pelaksanaan', 'en' => 'Delivery'], 'text' => ['id' => 'Mengerjakan sesuai rencana dengan laporan berkala.', 'en' => 'Executing to plan with regular reporting.']],
                ['title' => ['id' => 'Pendampingan', 'en' => 'Support'], 'text' => ['id' => 'Layanan purna jual setelah proyek selesai.', 'en' => 'After-sales support once the project is complete.']],
            ]),
        ];
    }

    public static function unitsPage(): array
    {
        return self::localize([
            'eyebrow' => ['id' => 'Pilar Bisnis', 'en' => 'Business Pillars'],
            'title' => ['id' => 'Tujuh pilar, satu ekosistem layanan', 'en' => 'Seven pillars, one service ecosystem'],
            'lead' => [
                'id' => 'Setiap pilar fokus pada keahliannya dan saling melengkapi, sehingga kebutuhan Anda dapat dipenuhi dalam satu pintu.',
                'en' => 'Each pillar focuses on its own expertise and complements the others, so your needs can be met in one place.',
            ],
            'services_title' => ['id' => 'Layanan utama', 'en' => 'Key services'],
            'process_title' => ['id' => 'Cara kami bekerja', 'en' => 'How we work'],
            'projects_title' => ['id' => 'Proyek terkait', 'en' => 'Related projects'],
            'projects_empty' => ['id' => 'Proyek untuk pilar ini akan segera ditampilkan.', 'en' => 'Projects for this pillar will be shown soon.'],
            'others_title' => ['id' => 'Pilar bisnis lainnya', 'en' => 'Other business pillars'],
            'about_title' => ['id' => 'Tentang pilar ini', 'en' => 'About this pillar'],
        ]);
    }

    /* ---------------------------------------------------------------------
     | Halaman Portofolio dan Klien & Mitra (A6)
     * ------------------------------------------------------------------- */

    public static function portfolioPage(): array
    {
        return self::localize([
            'eyebrow' => ['id' => 'Portofolio', 'en' => 'Portfolio'],
            'title' => ['id' => 'Proyek yang telah kami kerjakan', 'en' => 'Projects we have delivered'],
            'lead' => [
                'id' => 'Sebagian pekerjaan kami bersama instansi, sekolah, dan perusahaan di berbagai bidang.',
                'en' => 'A selection of our work with agencies, schools, and companies across many fields.',
            ],
            'filter_unit' => ['id' => 'Pilar bisnis', 'en' => 'Business pillar'],
            'filter_year' => ['id' => 'Tahun', 'en' => 'Year'],
            'all_units' => ['id' => 'Semua pilar', 'en' => 'All pillars'],
            'all_years' => ['id' => 'Semua tahun', 'en' => 'All years'],
            'shown' => ['id' => 'proyek ditampilkan', 'en' => 'projects shown'],
            'empty' => ['id' => 'Tidak ada proyek untuk filter ini.', 'en' => 'No projects match this filter.'],
            'reset' => ['id' => 'Atur ulang filter', 'en' => 'Reset filters'],
            'client' => ['id' => 'Klien', 'en' => 'Client'],
            'year' => ['id' => 'Tahun', 'en' => 'Year'],
            'unit' => ['id' => 'Pilar bisnis', 'en' => 'Business pillar'],
            'scope_title' => ['id' => 'Lingkup pekerjaan', 'en' => 'Scope of work'],
            'overview_title' => ['id' => 'Ringkasan proyek', 'en' => 'Project overview'],
            'more_title' => ['id' => 'Proyek lainnya', 'en' => 'More projects'],
            'back' => ['id' => 'Semua proyek', 'en' => 'All projects'],
        ]);
    }

    public static function clientCategories(): array
    {
        return self::localize([
            'kementerian' => ['id' => 'Kementerian', 'en' => 'Ministries'],
            'pemerintah' => ['id' => 'Pemerintahan', 'en' => 'Government'],
            'lembaga' => ['id' => 'Lembaga', 'en' => 'Institutions'],
            'universitas' => ['id' => 'Universitas', 'en' => 'Universities'],
            'organisasi' => ['id' => 'Organisasi', 'en' => 'Organizations'],
            'perusahaan' => ['id' => 'Perusahaan', 'en' => 'Corporations'],
            'swasta' => ['id' => 'Swasta', 'en' => 'Private Sector'],
        ]);
    }

    public static function clientsPage(): array
    {
        return self::localize([
            'eyebrow' => ['id' => 'Portofolio', 'en' => 'Portfolio'],
            'title' => ['id' => 'Klien & Mitra', 'en' => 'Clients & Partners'],
            'lead' => [
                'id' => 'Institusi, lembaga, dan perusahaan yang mempercayakan kebutuhan teknologinya kepada kami.',
                'en' => 'The institutions, agencies, and companies that trust us with their technology needs.',
            ],
            'intro_eyebrow' => ['id' => 'Dipercaya oleh', 'en' => 'Trusted by'],
            'intro_title' => ['id' => 'Beragam sektor, satu komitmen layanan', 'en' => 'Many sectors, one commitment to service'],
            'intro_text' => [
                'id' => 'Dari kementerian dan pemerintah daerah hingga universitas dan dunia usaha, kami bekerja bersama beragam pihak dengan standar layanan yang sama.',
                'en' => 'From ministries and local governments to universities and the business world, we work with many parties to the same standard of service.',
            ],
            'filter_label' => ['id' => 'Saring berdasarkan kategori', 'en' => 'Filter by category'],
            'all' => ['id' => 'Semua', 'en' => 'All'],
            'note' => [
                'id' => 'Logo ditampilkan atas izin masing-masing pihak.',
                'en' => 'Logos are shown with the permission of each party.',
            ],
        ]);
    }

    /* ---------------------------------------------------------------------
     | Komunitas, Karir, Kontak (A8, DUMMY: teks contoh)
     * ------------------------------------------------------------------- */

    public static function communityPage(): array
    {
        return self::localize([
            'eyebrow' => ['id' => 'Keberlanjutan', 'en' => 'Sustainability'],
            'about_title' => ['id' => 'Tentang komunitas', 'en' => 'About the community'],
            'about' => [
                ['id' => 'Komunitas Peduli Digital adalah wadah CSR DECAA.ID untuk berbagi pengetahuan dan akses teknologi kepada masyarakat, terutama di sekolah dan desa.', 'en' => 'Komunitas Peduli Digital is the CSR platform of DECAA.ID for sharing technology knowledge and access with society, especially in schools and villages.'],
                ['id' => 'Kegiatannya dijalankan bersama relawan, sekolah, dan komunitas lokal, dengan prinsip sederhana: teknologi harus dapat dimanfaatkan oleh semua orang.', 'en' => 'Its activities are run with volunteers, schools, and local communities, on a simple principle: technology should be usable by everyone.'],
            ],
            'pillars_title' => ['id' => 'Fokus kami', 'en' => 'Our focus'],
            'pillars' => [
                ['title' => ['id' => 'Literasi digital', 'en' => 'Digital literacy'], 'text' => ['id' => 'Membekali warga dengan kemampuan dasar menggunakan teknologi secara aman dan bijak.', 'en' => 'Equipping residents with basic skills to use technology safely and wisely.']],
                ['title' => ['id' => 'Akses teknologi', 'en' => 'Access to technology'], 'text' => ['id' => 'Mendukung pemerataan perangkat dan sarana belajar digital.', 'en' => 'Supporting equal access to devices and digital learning facilities.']],
                ['title' => ['id' => 'Kolaborasi lokal', 'en' => 'Local collaboration'], 'text' => ['id' => 'Bekerja bersama sekolah, perangkat desa, dan komunitas setempat.', 'en' => 'Working with schools, village officials, and local communities.']],
            ],
            'programs_title' => ['id' => 'Program kegiatan', 'en' => 'Programs'],
            // DUMMY: ganti dengan program asli
            'programs' => [
                ['title' => ['id' => 'Kelas Literasi Digital', 'en' => 'Digital Literacy Classes'], 'text' => ['id' => 'Pelatihan dasar ponsel, internet, dan layanan digital untuk warga.', 'en' => 'Basic training on phones, the internet, and digital services for residents.']],
                ['title' => ['id' => 'Klinik Perangkat Warga', 'en' => 'Community Device Clinic'], 'text' => ['id' => 'Pemeriksaan dan bantuan perawatan perangkat secara gratis.', 'en' => 'Free device checks and maintenance help.']],
                ['title' => ['id' => 'Sekolah Aman Digital', 'en' => 'Digital Safe Schools'], 'text' => ['id' => 'Edukasi keamanan daring bagi siswa dan guru.', 'en' => 'Online safety education for students and teachers.']],
                ['title' => ['id' => 'Relawan Teknologi', 'en' => 'Technology Volunteers'], 'text' => ['id' => 'Wadah bagi siapa pun yang ingin berbagi keahlian teknologi.', 'en' => 'A platform for anyone who wants to share technology skills.']],
            ],
            'join_title' => ['id' => 'Ingin terlibat?', 'en' => 'Want to get involved?'],
            'join_text' => ['id' => 'Sekolah, komunitas, maupun individu dapat bergabung sebagai peserta, mitra, atau relawan.', 'en' => 'Schools, communities, and individuals can join as participants, partners, or volunteers.'],
            'join_cta' => ['id' => 'Hubungi komunitas', 'en' => 'Contact the community'],
            'news_cta' => ['id' => 'Lihat kabar komunitas', 'en' => 'See community news'],
        ]);
    }

    public static function careersPage(): array
    {
        return self::localize([
            'eyebrow' => ['id' => 'Karir', 'en' => 'Careers'],
            'title' => ['id' => 'Bangun karier bersama DECAA.ID', 'en' => 'Build your career with DECAA.ID'],
            'lead' => [
                'id' => 'Kami selalu terbuka bagi talenta yang ingin bertumbuh di bidang teknologi dan layanan.',
                'en' => 'We are always open to talent who want to grow in technology and services.',
            ],
            'why_title' => ['id' => 'Mengapa bergabung', 'en' => 'Why join us'],
            'why' => [
                ['title' => ['id' => 'Beragam bidang', 'en' => 'Diverse fields'], 'text' => ['id' => 'Tujuh pilar bisnis membuka banyak jalur belajar dan berkarier.', 'en' => 'Seven business pillars open many paths to learn and build a career.']],
                ['title' => ['id' => 'Belajar berkelanjutan', 'en' => 'Continuous learning'], 'text' => ['id' => 'Pelatihan dan pendampingan lewat Training & Research Centre.', 'en' => 'Training and mentoring through our Training & Research Centre.']],
                ['title' => ['id' => 'Pekerjaan bermakna', 'en' => 'Meaningful work'], 'text' => ['id' => 'Proyek yang berdampak bagi sekolah, instansi, dan masyarakat.', 'en' => 'Projects that make a difference for schools, agencies, and communities.']],
                ['title' => ['id' => 'Lingkungan kolaboratif', 'en' => 'Collaborative environment'], 'text' => ['id' => 'Tim yang saling mendukung lintas pilar dan kantor.', 'en' => 'Teams that support one another across pillars and offices.']],
            ],
            'process_title' => ['id' => 'Proses seleksi', 'en' => 'Selection process'],
            'process' => [
                ['title' => ['id' => 'Kirim CV', 'en' => 'Send your CV'], 'text' => ['id' => 'Kirim CV dan ringkasan diri melalui email.', 'en' => 'Send your CV and a short introduction by email.']],
                ['title' => ['id' => 'Seleksi administrasi', 'en' => 'Screening'], 'text' => ['id' => 'Tim kami meninjau kesesuaian profil Anda.', 'en' => 'Our team reviews how your profile fits.']],
                ['title' => ['id' => 'Wawancara', 'en' => 'Interview'], 'text' => ['id' => 'Diskusi tentang pengalaman dan harapan Anda.', 'en' => 'A conversation about your experience and expectations.']],
                ['title' => ['id' => 'Keputusan', 'en' => 'Decision'], 'text' => ['id' => 'Kami mengabari hasilnya melalui email.', 'en' => 'We let you know the outcome by email.']],
            ],
            'cv_title' => ['id' => 'Kirim CV Anda', 'en' => 'Send your CV'],
            'cv_text' => [
                'id' => 'Pilih bidang yang Anda minati, lalu kirim CV (disarankan PDF) ke alamat email berikut. Subjek email akan terisi otomatis.',
                'en' => 'Choose the field you are interested in, then send your CV (PDF recommended) to the address below. The email subject is filled in automatically.',
            ],
            'field_label' => ['id' => 'Bidang yang diminati', 'en' => 'Field of interest'],
            'field_other' => ['id' => 'Lainnya / umum', 'en' => 'Other / general'],
            'subject_prefix' => ['id' => 'Lamaran - ', 'en' => 'Application - '],
            'send_cv' => ['id' => 'Kirim CV', 'en' => 'Send CV'],
            'copy_email' => ['id' => 'Salin alamat email', 'en' => 'Copy email address'],
            'copied' => ['id' => 'Alamat disalin', 'en' => 'Address copied'],
            'why_text' => [
                'id' => 'Di DECAA.ID, setiap orang punya ruang untuk belajar, berkontribusi, dan berkembang bersama tim lintas bidang.',
                'en' => 'At DECAA.ID, everyone has room to learn, contribute, and grow with teams across many fields.',
            ],
            'open_note' => ['id' => 'Lamaran terbuka sepanjang waktu', 'en' => 'Applications are always open'],
            'fields_title' => ['id' => 'Bidang yang bisa Anda tekuni', 'en' => 'Fields you can grow in'],
            'fields_text' => [
                'id' => 'Pilih bidang yang paling dekat dengan minat Anda, atau kirim lamaran umum.',
                'en' => 'Pick the field closest to your interests, or send a general application.',
            ],
            'process_text' => [
                'id' => 'Alur seleksi dibuat sederhana dan transparan dari awal sampai akhir.',
                'en' => 'A simple, transparent selection flow from start to finish.',
            ],
            'subject_label' => ['id' => 'Subjek email', 'en' => 'Email subject'],
            'send_to' => ['id' => 'Kirim ke', 'en' => 'Send to'],
            'step_label' => ['id' => 'Langkah', 'en' => 'Step'],
        ]);
    }

    public static function contactPage(): array
    {
        return self::localize([
            'eyebrow' => ['id' => 'Kontak', 'en' => 'Contact'],
            'title' => ['id' => 'Hubungi kami', 'en' => 'Get in touch'],
            'lead' => [
                'id' => 'Sampaikan kebutuhan atau pertanyaan Anda. Tim kami akan menanggapi secepatnya.',
                'en' => 'Tell us what you need or ask a question. Our team will respond as soon as possible.',
            ],
            'form_title' => ['id' => 'Kirim pesan', 'en' => 'Send a message'],
            'name' => ['id' => 'Nama lengkap', 'en' => 'Full name'],
            'email' => ['id' => 'Email', 'en' => 'Email'],
            'phone' => ['id' => 'Telepon (opsional)', 'en' => 'Phone (optional)'],
            'subject' => ['id' => 'Subjek', 'en' => 'Subject'],
            'message' => ['id' => 'Pesan', 'en' => 'Message'],
            'send' => ['id' => 'Kirim pesan', 'en' => 'Send message'],
            'subjects' => [
                'general' => ['id' => 'Pertanyaan umum', 'en' => 'General inquiry'],
                'quotation' => ['id' => 'Permintaan penawaran', 'en' => 'Quotation request'],
                'documents' => ['id' => 'Permintaan Dokumen', 'en' => 'Document Request'],
                'partnership' => ['id' => 'Kerja sama', 'en' => 'Partnership'],
                'community' => ['id' => 'Komunitas Peduli Digital', 'en' => 'Komunitas Peduli Digital'],
            ],
            // DUMMY: hapus catatan ini saat form diaktifkan (Tahap B4)
            'preview_note' => [
                'id' => 'Ini tampilan awal formulir. Pengiriman pesan akan diaktifkan pada tahap berikutnya; sementara itu silakan hubungi kami lewat email.',
                'en' => 'This is a preview of the form. Message sending will be enabled in a later stage; in the meantime please contact us by email.',
            ],
            'channels_title' => ['id' => 'Kanal resmi', 'en' => 'Official channels'],
            'offices_title' => ['id' => 'Kantor kami', 'en' => 'Our offices'],
            'open_map' => ['id' => 'Buka di Google Maps', 'en' => 'Open in Google Maps'],
            'whatsapp' => 'WhatsApp',
            'form_text' => [
                'id' => 'Isi formulir di bawah ini. Kolom bertanda * wajib diisi.',
                'en' => 'Fill in the form below. Fields marked * are required.',
            ],
            'subject_hint' => ['id' => 'Topik pesan Anda', 'en' => 'What is your message about?'],
            'err_required' => ['id' => 'Kolom ini wajib diisi.', 'en' => 'This field is required.'],
            'err_email' => ['id' => 'Format email belum benar.', 'en' => 'Please enter a valid email address.'],
            'sending' => ['id' => 'Memproses', 'en' => 'Processing'],
            'consent' => ['id' => 'Dengan mengirim pesan, Anda menyetujui', 'en' => 'By sending a message, you agree to our'],
            'success_title' => ['id' => 'Pesan Anda sudah siap', 'en' => 'Your message is ready'],
            'via_email' => ['id' => 'Kirim lewat email', 'en' => 'Send via email'],
            'write_again' => ['id' => 'Tulis pesan baru', 'en' => 'Write another message'],
            'channels_text' => [
                'id' => 'Pilih kanal yang paling nyaman bagi Anda. Kami siap membantu.',
                'en' => 'Choose the channel that suits you best. We are ready to help.',
            ],
            'offices_text' => [
                'id' => 'Kunjungi kantor kami di Jakarta, Pacitan, dan Ponorogo.',
                'en' => 'Visit our offices in Jakarta, Pacitan, and Ponorogo.',
            ],
        ]);
    }

    /* ---------------------------------------------------------------------
     | Pencarian dan Kebijakan Privasi (A9)
     * ------------------------------------------------------------------- */

    public static function searchPage(): array
    {
        return self::localize([
            'eyebrow' => ['id' => 'Pencarian', 'en' => 'Search'],
            'title' => ['id' => 'Hasil pencarian', 'en' => 'Search results'],
            'placeholder' => ['id' => 'Cari pilar bisnis, proyek, atau berita', 'en' => 'Search business pillars, projects, or news'],
            'button' => ['id' => 'Cari', 'en' => 'Search'],
            'prompt' => ['id' => 'Ketik kata kunci (minimal 2 huruf) untuk mulai mencari.', 'en' => 'Type a keyword (at least 2 characters) to start searching.'],
            'count' => ['id' => 'hasil untuk', 'en' => 'results for'],
            'none_title' => ['id' => 'Tidak ada hasil', 'en' => 'No results'],
            'none_text' => ['id' => 'Coba kata kunci lain, atau jelajahi halaman berikut.', 'en' => 'Try another keyword, or explore these pages.'],
            'groups' => [
                'pages' => ['id' => 'Halaman', 'en' => 'Pages'],
                'units' => ['id' => 'Pilar Bisnis', 'en' => 'Business Pillars'],
                'projects' => ['id' => 'Portofolio', 'en' => 'Portfolio'],
                'news' => ['id' => 'Berita', 'en' => 'News'],
            ],
        ]);
    }

    /**
     * Pencarian sederhana di data dummy (diganti pencarian database di Tahap B4).
     * Mengembalikan grup hasil: [['label' => .., 'items' => [['title','excerpt','url'], ...]], ...]
     */
    public static function search(string $q): array
    {
        $q = trim($q);

        if (mb_strlen($q) < 2) {
            return [];
        }

        $needle = mb_strtolower($q);
        $hit = fn (array $fields) => collect($fields)->contains(
            fn ($f) => is_string($f) && $f !== '' && str_contains(mb_strtolower($f), $needle)
        );
        $labels = self::searchPage()['groups'];
        $groups = [];

        // Halaman (menu, tanpa anak unit karena sudah ada grup Pilar Bisnis)
        $pages = [];
        foreach (self::nav() as $item) {
            $pages[] = $item;
            foreach ($item['children'] ?? [] as $child) {
                if (! isset($child['logo'])) {
                    $pages[] = $child;
                }
            }
        }
        $pageHits = [];
        foreach ($pages as $item) {
            if ($hit([$item['label']])) {
                $pageHits[$item['route']] = ['title' => $item['label'], 'excerpt' => '', 'url' => lroute($item['route'], $item['params'] ?? [])];
            }
        }
        if ($pageHits) {
            $groups[] = ['label' => $labels['pages'], 'items' => array_values($pageHits)];
        }

        $units = [];
        foreach (self::units() as $u) {
            if ($hit([$u['name'], $u['subtitle'], $u['summary']])) {
                $units[] = ['title' => $u['name'], 'excerpt' => $u['summary'], 'url' => lroute('units.show', ['slug' => $u['slug']])];
            }
        }
        if ($units) {
            $groups[] = ['label' => $labels['units'], 'items' => $units];
        }

        $projects = [];
        foreach (self::portfolio() as $p) {
            if ($hit([$p['title'], $p['summary'], $p['client'], $p['unit_name']])) {
                $projects[] = ['title' => $p['title'], 'excerpt' => $p['summary'], 'url' => lroute('portfolio.show', ['slug' => $p['slug']])];
            }
        }
        if ($projects) {
            $groups[] = ['label' => $labels['projects'], 'items' => $projects];
        }

        $news = [];
        foreach (self::news() as $n) {
            if ($hit([$n['title'], $n['excerpt'], $n['category']])) {
                $news[] = ['title' => $n['title'], 'excerpt' => $n['excerpt'], 'url' => lroute('news.show', ['slug' => $n['slug']])];
            }
        }
        if ($news) {
            $groups[] = ['label' => $labels['news'], 'items' => $news];
        }

        return $groups;
    }

    // DUMMY: draf awal, wajib ditinjau pihak hukum sebelum tayang
    public static function privacyPage(): array
    {
        return self::localize([
            'eyebrow' => ['id' => 'Legal', 'en' => 'Legal'],
            'title' => ['id' => 'Kebijakan Privasi', 'en' => 'Privacy Policy'],
            'lead' => [
                'id' => 'Kami menghargai privasi Anda. Kebijakan ini menjelaskan data apa yang kami kumpulkan melalui situs ini dan bagaimana kami menggunakannya.',
                'en' => 'We value your privacy. This policy explains what data we collect through this website and how we use it.',
            ],
            'updated_label' => ['id' => 'Terakhir diperbarui', 'en' => 'Last updated'],
            'updated' => '2026-10-30',
            'toc' => ['id' => 'Daftar isi', 'en' => 'Contents'],
            'sections' => [
                ['title' => ['id' => 'Data yang kami kumpulkan', 'en' => 'Data we collect'], 'text' => [
                    'id' => 'Data yang Anda berikan secara langsung, seperti nama, email, nomor telepon, dan isi pesan saat menghubungi kami. Kami juga dapat menerima data teknis dasar, seperti jenis peramban dan halaman yang dibuka.',
                    'en' => 'Data you give us directly, such as your name, email, phone number, and message when you contact us. We may also receive basic technical data, such as browser type and pages visited.']],
                ['title' => ['id' => 'Cara kami menggunakan data', 'en' => 'How we use data'], 'text' => [
                    'id' => 'Untuk menanggapi pertanyaan, memproses permintaan, meningkatkan situs, dan menjaga keamanan layanan. Kami tidak menjual data pribadi Anda.',
                    'en' => 'To respond to inquiries, process requests, improve the site, and keep our services secure. We do not sell your personal data.']],
                ['title' => ['id' => 'Cookie dan analitik', 'en' => 'Cookies and analytics'], 'text' => [
                    'id' => 'Situs ini menggunakan cookie yang diperlukan agar berfungsi, seperti preferensi bahasa. Analitik, bila digunakan, bersifat ringkas dan tidak melacak identitas pribadi.',
                    'en' => 'This site uses cookies needed to function, such as language preference. Analytics, if used, are lightweight and do not track personal identity.']],
                ['title' => ['id' => 'Berbagi dengan pihak lain', 'en' => 'Sharing with third parties'], 'text' => [
                    'id' => 'Data hanya dibagikan kepada penyedia layanan yang membantu operasional situs, atau bila diwajibkan oleh hukum.',
                    'en' => 'Data is only shared with service providers that help operate the site, or where required by law.']],
                ['title' => ['id' => 'Penyimpanan dan keamanan', 'en' => 'Storage and security'], 'text' => [
                    'id' => 'Kami menyimpan data selama diperlukan untuk tujuan di atas dan menerapkan langkah keamanan yang wajar untuk melindunginya.',
                    'en' => 'We keep data as long as needed for the purposes above and apply reasonable security measures to protect it.']],
                ['title' => ['id' => 'Hak Anda', 'en' => 'Your rights'], 'text' => [
                    'id' => 'Anda dapat meminta akses, perbaikan, atau penghapusan data pribadi Anda dengan menghubungi kami.',
                    'en' => 'You may request access to, correction of, or deletion of your personal data by contacting us.']],
                ['title' => ['id' => 'Perubahan kebijakan', 'en' => 'Policy changes'], 'text' => [
                    'id' => 'Kebijakan ini dapat diperbarui sewaktu-waktu. Tanggal pembaruan terakhir tercantum di bagian atas halaman.',
                    'en' => 'This policy may be updated from time to time. The last update date is shown at the top of the page.']],
                ['title' => ['id' => 'Hubungi kami', 'en' => 'Contact us'], 'text' => [
                    'id' => 'Pertanyaan tentang kebijakan ini dapat dikirim ke info@decaa.id.',
                    'en' => 'Questions about this policy can be sent to info@decaa.id.']],
            ],
        ]);
    }
}