<?php
namespace App\Http\Controllers;

use App\Support\SystemSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Tahap E: titik pemeriksaan untuk monitoring uptime (UptimeRobot, Better Stack, Uptime Kuma, dst.).
 * GET /health: 200 {"status":"ok"} bila aplikasi, database, dan folder storage sehat; 503 bila ada yang gagal.
 * Sengaja tidak membocorkan versi, lingkungan, atau pesan galat. Tetap menjawab saat mode pemeliharaan
 * (lihat SiteMaintenance), dengan penanda "maintenance" agar monitor bisa membedakan pemeliharaan terencana.
 */
class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->database(),
            'storage'  => is_dir(storage_path('logs')) && is_writable(storage_path('logs')),
        ];

        $ok = ! in_array(false, $checks, true);

        return response()->json(
            ['status' => $ok ? 'ok' : 'error', 'checks' => $checks, 'maintenance' => SystemSettings::maintenanceOn()],
            $ok ? 200 : 503,
            ['Cache-Control' => 'no-store, max-age=0', 'X-Robots-Tag' => 'noindex']
        );
    }

    protected function database(): bool
    {
        try {
            DB::select('select 1');

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
