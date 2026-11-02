<?php

namespace App\Support;

/**
 * Data Pilar Bisnis (jenis bisnis) beserta brand di dalamnya.
 *
 * Sumber: Revisi_Website_decaaid.pdf (butir 2 dan 5). Pilar = JENIS BISNIS, bukan nama divisi;
 * nama divisi/brand ada di dalam pilar (kunci 'brands').
 *
 * Aturan isi:
 *  - Teks yang belum ada di dokumen dibiarkan KOSONG (null), bukan diisi karangan.
 *  - 'description' ID dari dokumen apa adanya; versi EN adalah TERJEMAHAN DRAF (tinjau sebelum tayang).
 *  - 'instagram' dan 'tiktok' kosong sampai akun resmi diisi. 'url' = "More Info" di dokumen.
 *  - 'logo': file di public/img/logo/. Bila file belum ada, tampil placeholder rapi.
 *
 * Nilai dwibahasa ditulis ['id' => ..., 'en' => ...] dan di-resolve oleh Content::localize().
 */
class Pillars
{
    public static function all(): array
    {
        return [
            [
                'slug' => 'software-robotics',
                'name' => 'Software & Robotics',
                'icon' => 'code-bracket',
                'brands' => [
                    [
                        'slug' => 'decaa-software',
                        'name' => 'Decaa Software',
                        'logo' => 'img/logo/software.png',
                        'description' => [
                            'id' => 'Decaa Software merupakan home brand dari decaa.id group. Hingga saat ini kami telah memiliki ribuan aplikasi yang telah digunakan oleh client kami di berbagai industri. Beberapa produk kami antara lain adalah aplikasi DESA DIGITAL, aplikasi pasar tradisional digital (PASAR ONLINE), aplikasi WISNU (WISATA NUSANTARA) dan satu satunya marketplace AI di Indonesia (SATU-AI).',
                            'en' => 'Decaa Software is the home brand of the decaa.id group. To date we have built thousands of applications used by our clients across many industries. Our products include the DESA DIGITAL application, the digital traditional market application (PASAR ONLINE), the WISNU (Wisata Nusantara) application, and Indonesia’s only AI marketplace (SATU-AI).',
                        ],
                        'instagram' => null,
                        'tiktok' => null,
                        'url' => 'http://www.software.decaa.id',
                    ],
                    [
                        'slug' => 'decaa-robotics',
                        'name' => 'Decaa Robotics',
                        'logo' => 'img/logo/robotic.png',
                        'description' => [
                            'id' => 'Decaa Robotics berfokus menciptakan solusi teknologi otomasi terpadu untuk meningkatkan efisiensi operasional di berbagai sektor seperti industri, pendidikan, pemerintahan, dan bisnis. Beberapa produk robotika yang dikembangkan antara lain adalah teknologi kebencanaan untuk bencana banjir (early warning system bencana banjir), auto shrimp feeding (perangkat otomatis pemberian pakan udang secara terjadwal dan efisien) serta teknologi terpadu untuk pertanian cerdas yang berkelanjutan (smart farming).',
                            'en' => 'Decaa Robotics focuses on integrated automation technology that improves operational efficiency across industry, education, government, and business. Products developed include disaster technology for floods (a flood early warning system), auto shrimp feeding (an automatic device that feeds shrimp on schedule and efficiently), and integrated technology for sustainable smart farming.',
                        ],
                        'instagram' => null,
                        'tiktok' => null,
                        'url' => 'http://www.robotics.decaa.id',
                    ],
                ],
            ],
            [
                'slug' => 'hardware-peripheral',
                'name' => 'Hardware & Peripheral',
                'icon' => 'computer',
                'brands' => [
                    [
                        'slug' => 'decaa-hardware',
                        'name' => 'Decaa Hardware',
                        'logo' => 'img/logo/hardware.png',
                        'description' => [
                            'id' => 'Brand toko komputer milik DECAA.ID yang berfokus ke penjualan produk laptop, komputer, dan all in one PC sebagai daya tarik utama. Perusahaan ini melayani penjualan PC dan PC all in one secara custom yang dapat disesuaikan dengan kebutuhan dan keinginan pengguna. Kini toko ini telah memiliki 2 cabang di Pacitan, Jawa Timur dan Kabupaten Ponorogo, Jawa Timur.',
                            'en' => 'The computer store brand of DECAA.ID, focused on laptops, computers, and all-in-one PCs as its main draw. It sells custom PCs and all-in-one PCs tailored to each user’s needs and wishes. The store now has 2 branches, in Pacitan, East Java and Ponorogo Regency, East Java.',
                        ],
                        'instagram' => null,
                        'tiktok' => null,
                        'url' => 'http://www.hardware.decaa.id',
                    ],
                    [
                        'slug' => 'mmc-computer',
                        'name' => 'MMC Computer',
                        'logo' => 'img/logo/mmc-computer.png',
                        'description' => [
                            'id' => 'Perusahaan ini bergerak di bidang penjualan laptop, komputer, peripheral, dan aksesoris seperti printer, speaker, proyektor, dan lain sebagainya. Perusahaan ini juga membuka layanan servis untuk segala jenis perangkat elektronik dan komputer.',
                            'en' => 'This company sells laptops, computers, peripherals, and accessories such as printers, speakers, projectors, and more. It also offers repair services for all kinds of electronic devices and computers.',
                        ],
                        'instagram' => null,
                        'tiktok' => null,
                        'url' => 'http://www.mmccomputer.decaa.id',
                    ],
                    [
                        'slug' => 'apple-kingdom',
                        'name' => 'Apple Kingdom',
                        'logo' => 'img/logo/apple-kingdom.png',
                        'description' => [
                            'id' => 'Merupakan sebuah brand usaha di bidang hardware yang berfokus di penjualan perangkat Apple berupa iPhone, iPad, dan MacBook.',
                            'en' => 'A hardware brand focused on selling Apple devices: iPhone, iPad, and MacBook.',
                        ],
                        'instagram' => null,
                        'tiktok' => null,
                        'url' => 'http://www.applekingdom.decaa.id',
                    ],
                ],
            ],
            [
                'slug' => 'networking-services',
                'name' => 'Networking Services',
                'icon' => 'signal',
                'brands' => [
                    [
                        'slug' => 'decaa-networking-services',
                        'name' => 'Decaa Networking Services',
                        'logo' => 'img/logo/networking.png',
                        'description' => [
                            'id' => 'Decaa Networking Services bergerak di bidang infrastruktur jaringan dan kamera CCTV. Unit ini berfokus pada pembangunan infrastruktur jaringan, serta layanan teknologi informasi (TI) yang terintegrasi. Layanan ini dirancang untuk mendukung transformasi digital bagi berbagai sektor, mulai dari instansi pemerintah, perusahaan swasta, institusi pendidikan, pelaku UMKM, hingga fasilitas publik. Ratusan instansi baik pemerintahan maupun industri telah memilih produk kami sebagai salah satu sistem pengamanan dengan menggunakan produk kamera CCTV yang terintegrasi dengan sistem monitoring aplikasi.',
                            'en' => 'Decaa Networking Services works in network infrastructure and CCTV cameras. The unit focuses on building network infrastructure and integrated information technology (IT) services, designed to support digital transformation across sectors, from government agencies, private companies, and educational institutions to SMEs and public facilities. Hundreds of government and industry organizations have chosen our CCTV cameras, integrated with an application monitoring system, as one of their security systems.',
                        ],
                        'instagram' => null,
                        'tiktok' => null,
                        'url' => 'http://www.networking.decaa.id',
                    ],
                ],
            ],
            [
                'slug' => 'commercial-technology',
                'name' => 'Commercial Technology',
                'icon' => 'cpu-chip',
                // Dokumen: "Satu AI, Pasar Online, MBG Food Hub, Desa Digital dan aplikasi lain yang bisa dijual global".
                // Catatan: MBG Food Hub ada di daftar menu (butir 2) tetapi tidak ada di daftar butir 5. Dipakai sesuai menu; konfirmasi (Q11).
                // Deskripsi = narasi buatan (belum ada di dokumen; tinjau sebelum tayang). Sosmed dan tautan sengaja dikosongkan.
                'brands' => [
                    ['slug' => 'satu-ai', 'name' => 'Satu AI', 'logo' => 'img/logo/satu-ai.png', 'description' => ['id' => "Satu AI adalah marketplace AI satu-satunya di Indonesia. Di sini beragam solusi berbasis kecerdasan buatan dapat ditemukan, dicoba, dan dimanfaatkan untuk membantu pekerjaan maupun bisnis sehari-hari, dalam satu platform yang mudah diakses.", 'en' => "Satu AI is Indonesia’s only AI marketplace. Here a wide range of artificial-intelligence-based solutions can be discovered, tried, and used to support everyday work and business, all in one easy-to-access platform."], 'instagram' => null, 'tiktok' => null, 'url' => null],
                    ['slug' => 'pasar-online', 'name' => 'Pasar Online', 'logo' => 'img/logo/pasar-online.png', 'description' => ['id' => "Pasar Online adalah aplikasi pasar tradisional digital yang membantu pedagang dan pasar tradisional tetap bisa berjualan serta memperoleh penghasilan di era digital. Aplikasi ini lahir saat pandemi, ketika pasar tradisional membutuhkan jalur baru untuk menjangkau pembeli.", 'en' => "Pasar Online is a digital traditional-market application that helps vendors and traditional markets keep selling and earning in the digital era. It was born during the pandemic, when traditional markets needed a new way to reach buyers."], 'instagram' => null, 'tiktok' => null, 'url' => null],
                    ['slug' => 'mbg-food-hub', 'name' => 'MBG Food Hub', 'logo' => 'img/logo/mbg-food-hub.png', 'description' => ['id' => "MBG Food Hub adalah platform digital untuk mendukung rantai pasok pangan. Platform ini menghubungkan penyedia bahan pangan dengan dapur dan pengelola layanan makanan, agar pemesanan, pasokan, dan distribusi berjalan lebih terkoordinasi dan transparan.", 'en' => "MBG Food Hub is a digital platform that supports the food supply chain. It connects food suppliers with kitchens and food-service operators so that ordering, supply, and distribution run in a more coordinated and transparent way."], 'instagram' => null, 'tiktok' => null, 'url' => null],
                    ['slug' => 'desa-digital', 'name' => 'Desa Digital', 'logo' => 'img/logo/desa-digital.png', 'description' => ['id' => "Desa Digital adalah aplikasi untuk administrasi dan pelayanan data kependudukan secara digital. Aplikasi ini membantu pemerintah desa mengelola data warga dengan lebih tertib, cepat, dan mudah diakses, sehingga pelayanan kepada masyarakat menjadi lebih baik.", 'en' => "Desa Digital is an application for digital population-data administration and services. It helps village governments manage resident data in a more orderly, faster, and more accessible way, so services to the community improve."], 'instagram' => null, 'tiktok' => null, 'url' => null],
                    ['slug' => 'merchant-erp-system', 'name' => 'Merchant ERP System', 'logo' => 'img/logo/merchant-erp-system.png', 'description' => ['id' => "Merchant ERP System adalah sistem terpadu untuk pelaku usaha yang menyatukan kasir, stok, pembelian, penjualan, dan laporan dalam satu aplikasi. Pemilik usaha dapat memantau bisnisnya secara real time dan mengambil keputusan dengan lebih tepat.", 'en' => "Merchant ERP System is an integrated system for business owners that brings point of sale, stock, purchasing, sales, and reports into one application. Owners can monitor their business in real time and make better decisions."], 'instagram' => null, 'tiktok' => null, 'url' => null],
                    ['slug' => 'wisata-nusantara', 'name' => 'Wisata Nusantara', 'logo' => 'img/logo/wisata-nusantara.png', 'description' => ['id' => "Wisata Nusantara atau WISNU adalah aplikasi pariwisata yang memperkenalkan beragam destinasi, kuliner, dan budaya Nusantara. Aplikasi ini memudahkan wisatawan menemukan tempat menarik dan informasi perjalanan, sekaligus membantu pelaku wisata lokal dan pemerintah daerah menjangkau lebih banyak pengunjung.", 'en' => "Wisata Nusantara, or WISNU, is a tourism application that showcases the destinations, cuisine, and culture of the Indonesian archipelago. It makes it easier for travelers to find interesting places and travel information, while helping local tourism players and regional governments reach more visitors."], 'instagram' => null, 'tiktok' => null, 'url' => null],
                    ['slug' => 'smartmaps', 'name' => 'SmartMaps', 'logo' => 'img/logo/smartmaps.png', 'description' => ['id' => "SmartMaps adalah solusi pemetaan digital berbasis lokasi yang menampilkan data pada peta interaktif. Instansi dan pelaku usaha dapat memantau wilayah, aset, maupun pelanggan secara visual, sehingga perencanaan dan pengambilan keputusan menjadi lebih mudah.", 'en' => "SmartMaps is a location-based digital mapping solution that shows data on interactive maps. Agencies and businesses can visually monitor areas, assets, and customers, making planning and decision-making easier."], 'instagram' => null, 'tiktok' => null, 'url' => null],
                ],
            ],
            [
                'slug' => 'training-research-centre',
                'name' => 'Training & Research Centre',
                'icon' => 'academic-cap',
                'brands' => [
                    [
                        'slug' => 'decaa-training-research-centre',
                        'name' => 'Decaa Training & Research Centre',
                        'logo' => 'img/logo/trainingcentre.png',
                        'description' => ['id' => "Decaa Training & Research Centre adalah unit bisnis yang berfokus pada pengembangan kompetensi sumber daya manusia (SDM), pelatihan berbasis teknologi, serta kegiatan penelitian dan inovasi.", 'en' => "Decaa Training & Research Centre is a business unit focused on human resource (HR) competency development, technology-based training, and research and innovation."],
                        'services' => [
                            'title' => ['id' => "Fokus dan Layanan Utama", 'en' => "Main Focus and Services"],
                            'items' => [
                                ['title' => ['id' => "Pengembangan Kompetensi SDM", 'en' => "HR Competency Development"], 'text' => ['id' => "Menyediakan pelatihan manajemen, kepemimpinan, kolaborasi tim, dan soft skills untuk meningkatkan produktivitas serta membangun lingkungan kerja yang solid.", 'en' => "Provides management, leadership, teamwork, and soft skills training to improve productivity and build a solid working environment."]],
                                ['title' => ['id' => "Pelatihan Berbasis Teknologi", 'en' => "Technology-Based Training"], 'text' => ['id' => "Menyelenggarakan berbagai kursus dan bimbingan teknis, seperti teknologi informasi, desain grafis, pengelolaan database, hingga program pelatihan digital.", 'en' => "Runs a range of courses and technical guidance, such as information technology, graphic design, database management, and digital training programs."]],
                                ['title' => ['id' => "Mitra Kolaborasi", 'en' => "Collaboration Partners"], 'text' => ['id' => "Bekerja sama dengan berbagai pihak, mulai dari sektor pendidikan (sekolah dan perguruan tinggi), dunia industri, instansi pemerintah, hingga masyarakat umum untuk menciptakan ekosistem pembelajaran yang adaptif.", 'en' => "Works with many parties, from the education sector (schools and universities), industry, and government agencies to the general public, to create an adaptive learning ecosystem."]],
                                ['title' => ['id' => "Riset dan Inovasi", 'en' => "Research and Innovation"], 'text' => ['id' => "Menghubungkan antara kebutuhan dunia kerja/industri dengan hasil riset akademis agar dapat diterapkan menjadi solusi nyata di lapangan.", 'en' => "Connects the needs of the workplace and industry with academic research results so they can be applied as real solutions in the field."]],
                            ],
                        ],
                        'instagram' => null,
                        'tiktok' => null,
                        'url' => 'http://www.trainingcentre.decaa.id',
                    ],
                    [
                        'slug' => 'sekolah-robotika',
                        'name' => 'Sekolah Robotika',
                        'logo' => 'img/logo/sekolah-robotika.png',
                        'description' => [
                            'id' => 'Lembaga pendidikan atau tempat kursus non-formal yang secara khusus mengajarkan konsep dasar pemrograman (coding), mekanika, elektronika, dan teknik pembuatan robot kepada siswanya. Sekolah ini dirancang secara interaktif untuk berbagai tingkatan usia, mulai dari anak-anak usia TK, SD, SMP, SMA, hingga perguruan tinggi dan profesional.',
                            'en' => 'A non-formal educational institution or course provider that specifically teaches the basics of programming (coding), mechanics, electronics, and robot building. The school is designed to be interactive for all age levels, from kindergarten, elementary, junior high, and senior high students to university students and professionals.',
                        ],
                        'instagram' => null,
                        'tiktok' => null,
                        'url' => 'http://www.sekolahrobotika.com',
                    ],
                ],
            ],
            [
                'slug' => 'stationery-office-supply',
                'name' => 'Stationery & Office Supply',
                'icon' => 'pencil',
                'brands' => [
                    [
                        'slug' => 'paperking',
                        'name' => 'Paperking',
                        'logo' => 'img/logo/paperking.png',
                        'description' => [
                            'id' => 'Brand ini merupakan bidang usaha yang menyediakan berbagai macam perlengkapan kantor, kebutuhan sekolah, serta pemenuhan alat tulis untuk kebutuhan administrasi lembaga. Kebutuhan yang disediakan tidak terbatas pada pembelian skala kecil (eceran), melainkan juga melayani pemesanan dalam jumlah besar (grosir) untuk instansi pemerintah, perusahaan swasta, hingga lembaga pendidikan. Untuk meningkatkan kenyamanan pelanggan, mereka menyediakan opsi pengantaran langsung agar produk ATK bisa sampai ke lokasi konsumen secara praktis dan tepat waktu.',
                            'en' => 'This brand provides a wide range of office supplies, school needs, and stationery for institutional administration. It serves not only small-scale (retail) purchases but also bulk (wholesale) orders for government agencies, private companies, and educational institutions. For customer convenience, they offer direct delivery so stationery reaches the customer’s location practically and on time.',
                        ],
                        'instagram' => null,
                        'tiktok' => null,
                        'url' => 'http://www.paperking.id',
                    ],
                ],
            ],
            [
                'slug' => 'lifestyle-store',
                'name' => 'Lifestyle Store',
                'icon' => 'gift',
                'brands' => [
                    [
                        'slug' => 'gardenia',
                        'name' => 'Gardenia',
                        'logo' => 'img/logo/gardenia.png',
                        'description' => [
                            'id' => 'Gardenia merupakan brand usaha decaa.id di bidang lifestyle yang beroperasi sebagai penyedia bunga, buket (bouquet), hadiah eksklusif (gift), papan bunga ucapan, hingga kotak memori wisuda (graduation memory box). Toko ini menyediakan layanan untuk perayaan momen spesial seperti pernikahan, wisuda, hadiah ulang tahun, dan kebutuhan dekorasi hadiah lainnya.',
                            'en' => 'Gardenia is a decaa.id lifestyle brand that provides flowers, bouquets, exclusive gifts, congratulatory flower boards, and graduation memory boxes. The store serves special occasions such as weddings, graduations, birthday gifts, and other gift decoration needs.',
                        ],
                        'instagram' => null,
                        'tiktok' => null,
                        'url' => 'http://www.gardenia.decaa.id',
                    ],
                ],
            ],
            [
                'slug' => 'food-beverage',
                'name' => 'Food & Beverage',
                'icon' => 'cake',
                'brands' => [
                    [
                        'slug' => 'hba-supplier',
                        'name' => 'HBA Supplier',
                        'logo' => 'img/logo/hba-supplier.png',
                        'description' => [
                            'id' => 'HBA Supplier adalah pemasok atau distributor yang menyediakan barang-barang sembako dan kebutuhan sehari-hari lainnya.',
                            'en' => 'HBA Supplier is a supplier and distributor of staple goods and other daily necessities.',
                        ],
                        'instagram' => null,
                        'tiktok' => null,
                        'url' => 'http://www.hbasupplier.decaa.id',
                    ],
                    [
                        'slug' => 'dapur-kirana',
                        'name' => 'Dapur Kirana',
                        'logo' => 'img/logo/dapurkirana.png',
                        'description' => [
                            'id' => 'Merupakan bisnis decaa.id di bidang food & beverage yang memiliki unit di beberapa kota di Jawa Timur dan sekitarnya. Dapur ini berfungsi sebagai pusat produksi makanan skala besar yang modern, higienis, dan terstandardisasi.',
                            'en' => 'A decaa.id food & beverage business with units in several cities in East Java and surrounding areas. The kitchen serves as a modern, hygienic, and standardized large-scale food production center.',
                        ],
                        'instagram' => null,
                        'tiktok' => null,
                        'url' => 'http://www.dapurkirana.id',
                    ],
                    [
                        'slug' => 'fruvola',
                        'name' => 'Fruvola',
                        'logo' => 'img/logo/fruvola.png',
                        'description' => [
                            'id' => 'Fruvola adalah merek minuman kesehatan dari salah satu bidang usaha decaa.id di sektor food & beverage. Brand ini juga menyediakan paket kemitraan bagi yang ingin bergabung atau bekerja sama.',
                            'en' => 'Fruvola is a health beverage brand from one of decaa.id’s businesses in the food & beverage sector. The brand also offers partnership packages for those who want to join or collaborate.',
                        ],
                        'instagram' => null,
                        'tiktok' => null,
                        'url' => 'http://www.fruvola.decaa.id',
                    ],
                ],
            ],
        ];
    }
}