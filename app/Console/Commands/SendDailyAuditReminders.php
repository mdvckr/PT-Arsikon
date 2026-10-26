<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AuditFinding;
use App\Mail\AuditReminderMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendDailyAuditReminders extends Command
{
    protected $signature = 'audit:daily-reminders';
    protected $description = 'Cek temuan audit harian (H-3 menuju deadline dan Overdue) lalu kirim email ke PIC';

    public function handle(): int
    {
        $today = Carbon::today();
        $hMinus3 = Carbon::today()->addDays(3);

        // 1. Temuan H-3 menuju deadline
        $upcomingFindings = AuditFinding::with(['pic', 'warehouse'])
            ->whereIn('status', ['Open', 'In Progress'])
            ->whereBetween('due_date', [$today->toDateString(), $hMinus3->toDateString()])
            ->get();

        $upcomingCount = 0;
        foreach ($upcomingFindings as $finding) {
            if ($finding->pic && $finding->pic->email) {
                try {
                    Mail::to($finding->pic->email)->send(new AuditReminderMail($finding, 'REMINDER_H3'));
                    $finding->update(['last_reminded_at' => now()]);
                    $upcomingCount++;
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        // 2. Temuan yang melewati batas waktu (Overdue)
        $overdueFindings = AuditFinding::with(['pic', 'warehouse'])
            ->whereIn('status', ['Open', 'In Progress', 'Overdue'])
            ->whereDate('due_date', '<', $today->toDateString())
            ->get();

        $overdueCount = 0;
        foreach ($overdueFindings as $finding) {
            if ($finding->pic && $finding->pic->email) {
                try {
                    Mail::to($finding->pic->email)->send(new AuditReminderMail($finding, 'OVERDUE_ALERT'));
                    $finding->update(['last_reminded_at' => now()]);
                    $overdueCount++;
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        $this->info("Pengecekan pengingat audit harian selesai: {$upcomingCount} reminder H-3, {$overdueCount} alert overdue.");
        return Command::SUCCESS;
    }
}
