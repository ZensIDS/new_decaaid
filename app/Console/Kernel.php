<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // K31: pesan Kontak disimpan 12 bulan (butuh cron `php artisan schedule:run` tiap menit di produksi)
        $schedule->command('messages:prune')->dailyAt('02:00');
        // Tahap C: cadangan harian database + media ke storage/app/backups (14 terakhir disimpan)
        $schedule->command('site:backup')->dailyAt('03:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
