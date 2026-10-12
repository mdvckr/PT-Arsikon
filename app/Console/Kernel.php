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
        // T13: Notifikasi harian untuk jadwal kedatangan PO terlambat
        $schedule->command('procurement:check-late-schedules')->daily();

        // T17: Pemeriksaan harian peminjaman alat overdue dan notifikasi satu kali
        $schedule->command('tools:check-overdue-loans')->daily();
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
