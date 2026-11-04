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
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * B3a: Pengaturan Situs (satu halaman, satu formulir). Super Admin dan Admin.
 * Bisa diedit: slogan (ID/EN), telepon, nomor WhatsApp, 5 sosial media, dan daftar kantor (maks. 6).
 * Tetap di kode: nama situs, email umum/karir (K31: hanya info@decaa.id), logo. Logo dan favicon menyusul bersama Media Library (B3b).
 * Isian telepon, WhatsApp, dan sosmed yang dikosongkan memakai data contoh bawaan kode.
 */
class SiteSettingsController extends Controller
{
    protected const PAGE = 'site_settings';

    public const MAX_OFFICES = 6;

    public const SOCIALS = [
        'instagram' => 'Instagram',
        'facebook'  => 'Facebook',
        'youtube'   => 'YouTube',
        'tiktok'    => 'TikTok',
        'linkedin'  => 'LinkedIn',
    ];

    public function edit(): View
    {
        $row  = PageContentRepo::available() ? PageContentRepo::row(self::PAGE) : null;
        $seed = Content::companyRaw();
        $c    = $row && is_array($row->content) ? $row->content : [];

        $pair = fn($v) => ['id' => (string) ($v['id'] ?? (is_string($v) ? $v : '')), 'en' => (string) ($v['en'] ?? '')];

        $offices = ! empty($c['offices']) ? $c['offices'] : $seed['offices'];

        $data = [
            'tagline'         => ! empty($c['tagline']['id']) ? $pair($c['tagline']) : $pair($seed['tagline']),
            'phone'           => (string) ($c['phone'] ?? ''),
            'whatsapp_number' => (string) ($c['whatsapp_number'] ?? ''),
            'logo_light'      => (string) ($c['logo_light'] ?? ''),
            'logo_dark'       => (string) ($c['logo_dark'] ?? ''),
            'favicon'         => (string) ($c['favicon'] ?? ''),
            'social'          => [],
            'offices'         => array_values(array_map(fn($o) => [
                'type'    => $pair($o['type'] ?? []),
                'city'    => (string) ($o['city'] ?? ''),
                'address' => $pair($o['address'] ?? ''),
            ], $offices)),
        ];

        foreach (array_keys(self::SOCIALS) as $net) {
            $data['social'][$net] = (string) ($c['social'][$net] ?? '');
        }

        return view('pages.admin.settings.form', [
            'data'       => $data,
            'row'        => $row,
            'available'  => PageContentRepo::available(),
            'socials'    => self::SOCIALS,
            'maxOffices' => self::MAX_OFFICES,
            'sample'     => ['phone' => $seed['phone'], 'whatsapp' => $seed['whatsapp']['label']],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! PageContentRepo::available()) {
            throw ValidationException::withMessages(['tagline.id' => 'Tabel belum ada. Jalankan dulu: php artisan migrate']);
        }

        // Kantor yang semua isiannya kosong dibuang.
        $request->merge(['offices' => array_values(array_filter((array) $request->input('offices', []), function ($o) {
            foreach (['type.id', 'type.en', 'city', 'address.id', 'address.en'] as $path) {
                if (trim((string) data_get($o, $path, '')) !== '') {
                    return true;
                }
            }

            return false;
        }))]);

        $phone = ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s().]{6,30}$/'];
        $url   = ['nullable', 'string', 'max:255', 'url', 'regex:/^https?:\/\//i'];

        $rules = [
            'tagline.id'        => ['required', 'string', 'max:160'],
            'tagline.en'        => ['nullable', 'string', 'max:160'],
            'phone'             => $phone,
            'whatsapp_number'   => $phone,
            'logo_light'        => ['nullable', 'string', 'max:255'],
            'logo_dark'         => ['nullable', 'string', 'max:255'],
            'favicon'           => ['nullable', 'string', 'max:255'],
            'offices'           => ['required', 'array', 'min:1', 'max:'.self::MAX_OFFICES],
            'offices.*.type.id' => ['required', 'string', 'max:60'],
            'offices.*.type.en' => ['nullable', 'string', 'max:60'],
            'offices.*.city'    => ['required', 'string', 'max:80'],
            'offices.*.address.id' => ['required', 'string', 'max:250'],
            'offices.*.address.en' => ['nullable', 'string', 'max:250'],
        ];

        foreach (array_keys(self::SOCIALS) as $net) {
            $rules["social.$net"] = $url;
        }

        $d = $request->validate($rules, [
            'offices.required'  => 'Isi minimal satu kantor.',
            'phone.regex'       => 'Nomor telepon hanya boleh berisi angka, spasi, +, -, (, dan ).',
            'whatsapp_number.regex' => 'Nomor WhatsApp hanya boleh berisi angka, spasi, +, -, (, dan ).',
            'social.*.url'      => 'Tautan harus berupa alamat lengkap yang diawali https://',
            'social.*.regex'    => 'Tautan harus diawali http:// atau https://',
        ], [
            'tagline.id'        => 'slogan (ID)',
            'phone'             => 'telepon',
            'whatsapp_number'   => 'nomor WhatsApp',
            'offices.*.type.id' => 'jenis kantor (ID)',
            'offices.*.city'    => 'kota',
            'offices.*.address.id' => 'alamat (ID)',
        ]);

        $pair = fn(array $p) => ['id' => trim((string) ($p['id'] ?? '')), 'en' => trim((string) ($p['en'] ?? ''))];

        $content = [
            'tagline'         => $pair($d['tagline']),
            'phone'           => trim((string) ($d['phone'] ?? '')),
            'whatsapp_number' => trim((string) ($d['whatsapp_number'] ?? '')),
            'social'          => [],
            'offices'         => array_values(array_map(fn($o) => [
                'type'    => $pair($o['type']),
                'city'    => trim((string) $o['city']),
                'address' => $pair($o['address']),
            ], $d['offices'])),
        ];

        foreach (array_keys(self::SOCIALS) as $net) {
            $content['social'][$net] = trim((string) ($d['social'][$net] ?? ''));
        }

        // Logo dan favicon dari Media Library (B3b). Kosong = bawaan; path harus lolos MediaStore::pick.
        foreach (['logo_light', 'logo_dark', 'favicon'] as $field) {
            $raw = trim((string) ($d[$field] ?? ''));

            if ($raw === '') {
                $content[$field] = '';
                continue;
            }

            $picked = MediaStore::pick($raw);

            if (! $picked) {
                throw ValidationException::withMessages([$field => 'Gambar dari Media Library tidak ditemukan. Pilih lagi, atau lepas untuk memakai bawaan.']);
            }

            $content[$field] = $picked;
        }

        PageContent::updateOrCreate(
            ['page' => self::PAGE],
            ['content' => $content, 'updated_by' => $request->user()->id]
        );

        ActivityLog::record('settings.updated', 'mengubah Pengaturan Situs');

        return redirect()->route('admin.settings.edit')->with('status', 'Pengaturan situs disimpan.');
    }

    /** Kembali ke data contoh bawaan kode. */
    public function reset(): RedirectResponse
    {
        if (PageContentRepo::available()) {
            PageContent::where('page', self::PAGE)->delete();
        }

        ActivityLog::record('settings.reset', 'mengembalikan Pengaturan Situs ke data contoh');

        return redirect()->route('admin.settings.edit')->with('status', 'Pengaturan situs dikembalikan ke data contoh.');
    }
}
