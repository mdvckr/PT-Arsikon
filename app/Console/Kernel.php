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

        // Otomatisasi Audit & Laporan Terpadu
        $schedule->command('audit:daily-reminders')
                 ->dailyAt('08:00')
                 ->timezone('Asia/Jakarta')
                 ->withoutOverlapping();

        $schedule->command('audit:monthly-report')
                 ->monthlyOn(1, '08:00')
                 ->timezone('Asia/Jakarta')
                 ->withoutOverlapping();

        $schedule->command('audit:automate --type=daily')
                 ->dailyAt('08:00')
                 ->timezone('Asia/Jakarta')
                 ->withoutOverlapping();

        $schedule->command('audit:automate --type=monthly')
                 ->monthlyOn(1, '07:00')
                 ->timezone('Asia/Jakarta')
                 ->withoutOverlapping();
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
