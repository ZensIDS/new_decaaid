<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\PageContent;
use App\Support\PageContentRepo;
use App\Support\SystemSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * B3p: Pengaturan Sistem (hanya Super Admin): mode pemeliharaan + tautan rahasia, analitik (GA4/Plausible/Umami),
 * kode verifikasi Search Console dan Bing. Alamat email pengirim tetap di .env (K31); noindex staging tetap di .env (QA17).
 */
class SystemSettingsController extends Controller
{
    public const PROVIDERS = [
        'none'      => ['Tanpa analitik', 'Tidak ada skrip pelacak.'],
        'ga4'       => ['Google Analytics 4', 'ID pengukuran, mis. G-ABC123XYZ.'],
        'plausible' => ['Plausible', 'Nama domain, mis. decaa.id.'],
        'umami'     => ['Umami', 'ID situs (UUID) dari dasbor Umami.'],
    ];

    public function edit(): View
    {
        return view('pages.admin.system.form', [
            'data'      => SystemSettings::get(),
            'row'       => PageContentRepo::available() ? PageContentRepo::row(SystemSettings::PAGE) : null,
            'available' => PageContentRepo::available(),
            'providers' => self::PROVIDERS,
            'fromEmail' => (string) config('mail.from.address'),
            'mailer'    => (string) config('mail.default'),
            'production' => app()->environment('production'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! PageContentRepo::available()) {
            throw ValidationException::withMessages(['maintenance_secret' => 'Tabel belum ada. Jalankan dulu: php artisan migrate']);
        }

        $provider = (string) $request->input('analytics_provider', 'none');

        $idRule = match ($provider) {
            'ga4'       => ['required', 'regex:/^G-[A-Z0-9]{4,15}$/'],
            'plausible' => ['required', 'regex:/^[a-z0-9]([a-z0-9.-]*[a-z0-9])?\.[a-z]{2,}$/i'],
            'umami'     => ['required', 'regex:/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i'],
            default     => ['nullable'],
        };

        $d = $request->validate([
            'maintenance_enabled' => ['required', 'in:0,1'],
            'maintenance_secret'  => ['nullable', 'required_if:maintenance_enabled,1', 'regex:/^[A-Za-z0-9-]{8,60}$/'],
            'maintenance_ips'     => ['nullable', 'string', 'max:2000'],
            'maintenance_title.id'   => ['nullable', 'string', 'max:80'],
            'maintenance_title.en'   => ['nullable', 'string', 'max:80'],
            'maintenance_message.id' => ['nullable', 'string', 'max:400'],
            'maintenance_message.en' => ['nullable', 'string', 'max:400'],
            'analytics_provider'  => ['required', 'in:'.implode(',', array_keys(self::PROVIDERS))],
            'analytics_id'        => $idRule,
            'analytics_script_url' => ['nullable', 'string', 'max:255', 'url', 'regex:/^https:\/\//i'],
            'verification_google' => ['nullable', 'regex:/^[A-Za-z0-9_-]{16,100}$/'],
            'verification_bing'   => ['nullable', 'regex:/^[A-Za-z0-9_-]{16,100}$/'],
        ], [
            'maintenance_secret.required_if' => 'Isi kata rahasia sebelum menyalakan mode pemeliharaan (tombol Buat Otomatis).',
            'maintenance_secret.regex'       => 'Kata rahasia 8-60 karakter: huruf, angka, dan tanda hubung saja.',
            'analytics_id.required'          => 'Isi ID untuk penyedia analitik yang dipilih.',
            'analytics_id.regex'             => 'Format ID tidak sesuai dengan penyedia yang dipilih.',
            'analytics_script_url.url'       => 'Alamat skrip harus berupa URL lengkap.',
            'analytics_script_url.regex'     => 'Alamat skrip harus diawali https://',
            'verification_google.regex'      => 'Isi hanya nilai content dari tag meta (huruf, angka, - dan _), tanpa tag lengkap.',
            'verification_bing.regex'        => 'Isi hanya nilai content dari tag meta (huruf, angka, - dan _), tanpa tag lengkap.',
        ], [
            'maintenance_secret' => 'kata rahasia',
            'maintenance_title.id' => 'judul pemeliharaan (ID)', 'maintenance_title.en' => 'judul pemeliharaan (EN)',
            'maintenance_message.id' => 'pesan pemeliharaan (ID)', 'maintenance_message.en' => 'pesan pemeliharaan (EN)',
            'analytics_id'       => 'ID analitik',
        ]);

        // Pengecualian IP: satu alamat per baris (atau dipisah koma/spasi), IPv4 atau IPv6 yang valid, maks. 20.
        $ips = collect(preg_split('/[\s,;]+/', (string) ($d['maintenance_ips'] ?? ''), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($x) => strtolower(trim($x)))->unique()->values();
        $bad = $ips->first(fn ($x) => ! filter_var($x, FILTER_VALIDATE_IP));
        if ($bad !== null) {
            throw ValidationException::withMessages(['maintenance_ips' => "\"{$bad}\" bukan alamat IP yang valid. Tulis satu alamat IPv4 atau IPv6 per baris."]);
        }
        if ($ips->count() > 20) {
            throw ValidationException::withMessages(['maintenance_ips' => 'Maksimal 20 alamat IP.']);
        }

        $enabled = $d['maintenance_enabled'] === '1';
        $before  = SystemSettings::get()['maintenance']['enabled'] ?? false;

        $content = [
            'maintenance'  => [
                'enabled' => $enabled,
                'secret'  => trim((string) ($d['maintenance_secret'] ?? '')),
                'ips'     => $ips->all(),
                'title'   => ['id' => trim((string) ($d['maintenance_title']['id'] ?? '')), 'en' => trim((string) ($d['maintenance_title']['en'] ?? ''))],
                'message' => ['id' => trim((string) ($d['maintenance_message']['id'] ?? '')), 'en' => trim((string) ($d['maintenance_message']['en'] ?? ''))],
            ],
            'analytics'    => [
                'provider'   => $d['analytics_provider'],
                'id'         => $d['analytics_provider'] === 'none' ? '' : trim((string) ($d['analytics_id'] ?? '')),
                'script_url' => $d['analytics_provider'] === 'none' ? '' : trim((string) ($d['analytics_script_url'] ?? '')),
            ],
            'verification' => [
                'google' => trim((string) ($d['verification_google'] ?? '')),
                'bing'   => trim((string) ($d['verification_bing'] ?? '')),
            ],
        ];

        PageContent::updateOrCreate(
            ['page' => SystemSettings::PAGE],
            ['content' => $content, 'updated_by' => $request->user()->id]
        );
        SystemSettings::forget();

        ActivityLog::record(
            'system.updated',
            $enabled !== (bool) $before
                ? ($enabled ? 'menyalakan mode pemeliharaan' : 'mematikan mode pemeliharaan')
                : 'mengubah Pengaturan Sistem'
        );

        return redirect()->route('admin.system.edit')->with('status', 'Pengaturan sistem disimpan.');
    }
}
