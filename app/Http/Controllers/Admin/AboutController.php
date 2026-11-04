<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\PageContent;
use App\Support\Content;
use App\Support\MediaStore;
use App\Support\PageContentRepo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * B3f: Tentang Kami (satu halaman, satu formulir) untuk tiga tab publik:
 * Profil Perusahaan (+ Chairman Message), Visi & Misi (+ nilai), dan Perjalanan Kami (lini waktu).
 * Super Admin dan Admin. Foto (Chairman, profil, per tahun) belum diatur di sini (menunggu Media Library B3b).
 *
 * Bentuk isi tersimpan (tabel page_contents, halaman 'about'):
 *   profile => lead, story_title, story[], chairman{title, name, role, headline, message[]}
 *   vision  => lead, vision, missions[], values_title, values[{title, text}]
 *   journey => lead, intro_title, intro_text, milestones[{year, tag, title, text}], ahead_title, ahead_text
 * Semua teks berupa pasangan {id, en}, kecuali chairman.name dan milestones[].year (teks biasa).
 */
class AboutController extends Controller
{
    protected const PAGE = 'about';

    /** Batas jumlah butir per daftar. */
    public const MAX = [
        'profile.story'      => 6,
        'chairman.message'   => 6,
        'vision.missions'    => 8,
        'vision.values'      => 8,
        'journey.milestones' => 20,
    ];

    /** Slot foto halaman Profil (v78): kunci => [nama, petunjuk, rasio pratinjau]. Berkas bawaan: Content::ABOUT_IMAGES. */
    public const IMAGE_SLOTS = [
        'profile_1' => ['Foto perusahaan', 'Foto pertama di bagian Siapa kami. Rasio 4:3.', 'aspect-[4/3]'],
        'profile_2' => ['Foto kegiatan', 'Foto kedua di bagian Siapa kami. Rasio 4:3.', 'aspect-[4/3]'],
        'hq'        => ['Foto kantor pusat', 'Latar kartu Kantor Pusat. Kosong = foto kantor bawaan (public/img/building.*). Lanskap lebar paling pas.', 'aspect-[16/9]'],
    ];

    public function edit(): View
    {
        $row = PageContentRepo::available() ? PageContentRepo::row(self::PAGE) : null;

        return view('pages.admin.about.form', [
            'form'          => $this->formData($row?->content ?? []),
            'images'        => (array) ($row?->content['images'] ?? []),
            'slots'         => self::IMAGE_SLOTS,
            'journeyImages' => (array) ($row?->content['journey_images'] ?? []),
            'row'       => $row,
            'available' => PageContentRepo::available(),
            'max'       => self::MAX,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! PageContentRepo::available()) {
            throw ValidationException::withMessages(['profile.lead.id' => 'Tabel belum ada. Jalankan dulu: php artisan migrate']);
        }

        // Baris daftar yang kosong semua dibuang dulu, supaya aturan min/max menghitung yang benar-benar diisi.
        $input = $request->all();
        data_set($input, 'profile.story', $this->dropEmptyPairs(data_get($input, 'profile.story', [])));
        data_set($input, 'profile.chairman.message', $this->dropEmptyPairs(data_get($input, 'profile.chairman.message', [])));
        data_set($input, 'vision.missions', $this->dropEmptyPairs(data_get($input, 'vision.missions', [])));
        data_set($input, 'vision.values', $this->dropEmptyRows(data_get($input, 'vision.values', []), ['title.id', 'title.en', 'text.id', 'text.en']));
        data_set($input, 'journey.milestones', $this->dropEmptyRows(data_get($input, 'journey.milestones', []), ['year', 'tag.id', 'tag.en', 'title.id', 'title.en', 'text.id', 'text.en']));
        $request->merge($input);

        $rules = [];
        $names = [];

        // Pasangan ID wajib, EN opsional (kosong = pengunjung EN melihat teks ID)
        $pair = function (string $path, string $label, int $max) use (&$rules, &$names) {
            $rules["$path.id"] = ['required', 'string', "max:$max"];
            $rules["$path.en"] = ['nullable', 'string', "max:$max"];
            $names["$path.id"] = strtolower($label) . ' (ID)';
            $names["$path.en"] = strtolower($label) . ' (EN)';
        };

        // Profil
        $pair('profile.lead', 'Kalimat pengantar Profil', 300);
        $pair('profile.story_title', 'Judul "Siapa kami"', 120);
        $rules['profile.story'] = ['required', 'array', 'min:1', 'max:' . self::MAX['profile.story']];
        $pair('profile.story.*', 'Paragraf "Siapa kami"', 800);

        $pair('profile.chairman.title', 'Judul Chairman Message', 120);
        $rules['profile.chairman.photo'] = ['nullable', 'string', 'max:255', 'regex:#^(storage|img|wp-uploads)/[A-Za-z0-9_./-]+$#', 'not_regex:#\.\.#'];
        $names['profile.chairman.photo'] = 'foto Chairman';
        foreach (array_keys(self::IMAGE_SLOTS) as $slot) {
            $rules["images.$slot"] = ['nullable', 'string', 'max:255'];
        }
        $rules['journey_images']   = ['nullable', 'array', 'max:' . self::MAX['journey.milestones']];
        $rules['journey_images.*'] = ['nullable', 'string', 'max:255'];

        $rules['profile.chairman.name'] = ['required', 'string', 'max:80'];
        $names['profile.chairman.name'] = 'nama Chairman';
        $pair('profile.chairman.role', 'Jabatan Chairman', 80);
        $pair('profile.chairman.headline', 'Kalimat utama Chairman Message', 200);
        $rules['profile.chairman.message'] = ['required', 'array', 'min:1', 'max:' . self::MAX['chairman.message']];
        $pair('profile.chairman.message.*', 'Paragraf Chairman Message', 1000);

        // Visi & Misi
        $pair('vision.lead', 'Kalimat pengantar Visi & Misi', 300);
        $pair('vision.vision', 'Visi', 400);
        $rules['vision.missions'] = ['required', 'array', 'min:1', 'max:' . self::MAX['vision.missions']];
        $pair('vision.missions.*', 'Butir misi', 300);
        $pair('vision.values_title', 'Judul nilai-nilai', 120);
        $rules['vision.values'] = ['required', 'array', 'min:1', 'max:' . self::MAX['vision.values']];
        $pair('vision.values.*.title', 'Judul nilai', 80);
        $pair('vision.values.*.text', 'Teks nilai', 200);

        // Perjalanan Kami
        $pair('journey.lead', 'Kalimat pengantar Perjalanan', 300);
        $pair('journey.intro_title', 'Judul pengantar Perjalanan', 200);
        $pair('journey.intro_text', 'Teks pengantar Perjalanan', 300);
        $rules['journey.milestones'] = ['required', 'array', 'min:1', 'max:' . self::MAX['journey.milestones']];
        $rules['journey.milestones.*.year'] = ['required', 'string', 'max:20'];
        $names['journey.milestones.*.year'] = 'tahun peristiwa';
        $pair('journey.milestones.*.tag', 'Label peristiwa', 40);
        $pair('journey.milestones.*.title', 'Judul peristiwa', 100);
        $pair('journey.milestones.*.text', 'Cerita peristiwa', 700);
        $pair('journey.ahead_title', 'Judul penutup Perjalanan', 120);
        $pair('journey.ahead_text', 'Teks penutup Perjalanan', 400);

        $d = $request->validate($rules, [
            'profile.story.required'            => 'Isi minimal satu paragraf "Siapa kami".',
            'profile.chairman.message.required' => 'Isi minimal satu paragraf Chairman Message.',
            'vision.missions.required'          => 'Isi minimal satu butir misi.',
            'vision.values.required'            => 'Isi minimal satu nilai.',
            'journey.milestones.required'       => 'Isi minimal satu peristiwa Perjalanan Kami.',
        ], $names);

        $p  = fn($v) => ['id' => trim((string) ($v['id'] ?? '')), 'en' => trim((string) ($v['en'] ?? ''))];
        $pl = fn($list) => array_values(array_map($p, $list));

        $content = [
            'profile' => [
                'lead'        => $p($d['profile']['lead']),
                'story_title' => $p($d['profile']['story_title']),
                'story'       => $pl($d['profile']['story']),
                'chairman'    => [
                    'title'    => $p($d['profile']['chairman']['title']),
                    'name'     => trim((string) $d['profile']['chairman']['name']),
                    'role'     => $p($d['profile']['chairman']['role']),
                    'headline' => $p($d['profile']['chairman']['headline']),
                    'message'  => $pl($d['profile']['chairman']['message']),
                    'photo'    => trim((string) ($d['profile']['chairman']['photo'] ?? '')),
                ],
            ],
            'vision' => [
                'lead'         => $p($d['vision']['lead']),
                'vision'       => $p($d['vision']['vision']),
                'missions'     => $pl($d['vision']['missions']),
                'values_title' => $p($d['vision']['values_title']),
                'values'       => array_values(array_map(
                    fn($r) => ['title' => $p($r['title']), 'text' => $p($r['text'])],
                    $d['vision']['values']
                )),
            ],
            'journey' => [
                'lead'        => $p($d['journey']['lead']),
                'intro_title' => $p($d['journey']['intro_title']),
                'intro_text'  => $p($d['journey']['intro_text']),
                'milestones'  => array_values(array_map(
                    fn($r) => [
                        'year'  => trim((string) $r['year']),
                        'tag'   => $p($r['tag']),
                        'title' => $p($r['title']),
                        'text'  => $p($r['text']),
                    ],
                    $d['journey']['milestones']
                )),
                'ahead_title' => $p($d['journey']['ahead_title']),
                'ahead_text'  => $p($d['journey']['ahead_text']),
            ],
        ];

        // Foto dari Media Library: hanya path sah yang berkasnya ada. Foto per tahun hanya untuk tahun yang masih ada di lini waktu.
        foreach (array_keys(self::IMAGE_SLOTS) as $slot) {
            if ($picked = MediaStore::pick($d['images'][$slot] ?? null)) {
                $content['images'][$slot] = $picked;
            }
        }

        $years = collect($d['journey']['milestones'] ?? [])->map(fn($m) => Str::slug((string) ($m['year'] ?? '')))->filter()->unique()->all();

        foreach ((array) ($d['journey_images'] ?? []) as $slug => $path) {
            if (in_array((string) $slug, $years, true) && ($picked = MediaStore::pick($path))) {
                $content['journey_images'][(string) $slug] = $picked;
            }
        }

        PageContent::updateOrCreate(
            ['page' => self::PAGE],
            ['content' => $content, 'updated_by' => $request->user()->id]
        );

        ActivityLog::record('page.updated', 'mengubah isi halaman Tentang Kami');

        return redirect()->route('admin.about.edit')->with('status', 'Halaman Tentang Kami disimpan.');
    }

    /** Kembali ke teks contoh bawaan kode. */
    public function reset(): RedirectResponse
    {
        if (PageContentRepo::available()) {
            PageContent::where('page', self::PAGE)->delete();
        }

        ActivityLog::record('page.reset', 'mengembalikan halaman Tentang Kami ke teks contoh');

        return redirect()->route('admin.about.edit')->with('status', 'Halaman Tentang Kami dikembalikan ke teks contoh.');
    }

    /**
     * Data form: isi tersimpan (mentah, EN boleh kosong) menimpa teks contoh per kunci.
     * Hasilnya selalu lengkap dan berbentuk sama, sehingga view tidak perlu memeriksa kunci satu per satu.
     */
    protected function formData(array $stored): array
    {
        $seed = Content::aboutRaw();

        $pick = function (string $page, string $key) use ($stored, $seed) {
            $value = $stored[$page][$key] ?? null;

            return ! empty($value) ? $value : ($seed[$page][$key] ?? null);
        };

        $pair  = fn($v) => ['id' => (string) ($v['id'] ?? ''), 'en' => (string) ($v['en'] ?? '')];
        $pairs = fn($list) => collect($list)->map($pair)->values()->all();

        $chair = fn(string $key) => ! empty($stored['profile']['chairman'][$key])
            ? $stored['profile']['chairman'][$key]
            : ($seed['profile']['chairman'][$key] ?? null);

        return [
            'profile' => [
                'lead'        => $pair($pick('profile', 'lead')),
                'story_title' => $pair($pick('profile', 'story_title')),
                'story'       => $pairs($pick('profile', 'story')),
                'chairman'    => [
                    'title'    => $pair($chair('title')),
                    'name'     => (string) $chair('name'),
                    'role'     => $pair($chair('role')),
                    'headline' => $pair($chair('headline')),
                    'message'  => $pairs($chair('message')),
                    'photo'    => (string) ($stored['profile']['chairman']['photo'] ?? ''), // kosong = foto bawaan
                ],
            ],
            'vision' => [
                'lead'         => $pair($pick('vision', 'lead')),
                'vision'       => $pair($pick('vision', 'vision')),
                'missions'     => $pairs($pick('vision', 'missions')),
                'values_title' => $pair($pick('vision', 'values_title')),
                'values'       => collect($pick('vision', 'values'))
                    ->map(fn($r) => ['title' => $pair($r['title'] ?? []), 'text' => $pair($r['text'] ?? [])])
                    ->values()->all(),
            ],
            'journey' => [
                'lead'        => $pair($pick('journey', 'lead')),
                'intro_title' => $pair($pick('journey', 'intro_title')),
                'intro_text'  => $pair($pick('journey', 'intro_text')),
                'milestones'  => collect($pick('journey', 'milestones'))
                    ->map(fn($r) => [
                        'year'  => (string) ($r['year'] ?? ''),
                        'tag'   => $pair($r['tag'] ?? []),
                        'title' => $pair($r['title'] ?? []),
                        'text'  => $pair($r['text'] ?? []),
                    ])
                    ->values()->all(),
                'ahead_title' => $pair($pick('journey', 'ahead_title')),
                'ahead_text'  => $pair($pick('journey', 'ahead_text')),
            ],
        ];
    }

    /** Daftar pasangan {id, en}: baris yang kedua bahasanya kosong dibuang. */
    protected function dropEmptyPairs(mixed $rows): array
    {
        return $this->dropEmptyRows($rows, ['id', 'en']);
    }

    /** Buang baris yang semua jalur isiannya kosong. */
    protected function dropEmptyRows(mixed $rows, array $paths): array
    {
        return array_values(array_filter((array) $rows, function ($row) use ($paths) {
            foreach ($paths as $path) {
                if (trim((string) data_get($row, $path, '')) !== '') {
                    return true;
                }
            }

            return false;
        }));
    }
}
