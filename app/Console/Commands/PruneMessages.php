<?php

namespace App\Console\Commands;

use App\Models\ContactMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/** K31: pesan Kontak disimpan 12 bulan, lalu dihapus. Dijadwalkan harian di App\Console\Kernel. */
class PruneMessages extends Command
{
    protected $signature   = 'messages:prune {--months=12 : Hapus pesan yang lebih lama dari sekian bulan}';
    protected $description = 'Hapus pesan Kontak yang sudah melewati masa simpan (bawaan 12 bulan)';

    public function handle(): int
    {
        if (! Schema::hasTable('contact_messages')) {
            $this->warn('Tabel contact_messages belum ada. Jalankan php artisan migrate.');

            return self::SUCCESS;
        }

        $months = max(1, (int) $this->option('months'));
        $count  = ContactMessage::where('created_at', '<', now()->subMonthsNoOverflow($months))->delete();

        $this->info("{$count} pesan lebih lama dari {$months} bulan dihapus.");

        return self::SUCCESS;
    }
}
