<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ✅ Jalankan otomatis setiap 1 Januari jam 00:01 WIB
        $schedule->command('cuti:update-tahunan')
                 ->yearlyOn(1, 1, '00:01')
                 ->timezone('Asia/Jakarta')
                 ->appendOutputTo(storage_path('logs/cuti-update.log'));
        
        // 🧪 UNTUK TESTING: Uncomment baris ini (jalan setiap hari jam 00:01)
        // $schedule->command('cuti:update-tahunan')->dailyAt('00:01');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}