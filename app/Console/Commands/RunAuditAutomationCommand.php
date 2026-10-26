<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AuditFinding;
use App\Models\User;
use App\Mail\AuditDeadlineReminderMail;
use App\Mail\MonthlyAuditExecutiveSummaryMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class RunAuditAutomationCommand extends Command
{
    protected $signature = 'audit:automate {--type=daily : daily atau monthly}';
    protected $description = 'Menjalankan pengingat deadline audit harian dan kompilasi laporan audit bulanan otomatis';

    public function handle(): int
    {
        $type = $this->option('type') ?? 'daily';

        if ($type === 'monthly') {
            $this->processMonthlyReport();
        } else {
            $this->processDailyReminders();
        }

        return Command::SUCCESS;
    }

    private function processDailyReminders(): void
    {
        $today = Carbon::today();
        $threeDaysAhead = Carbon::today()->addDays(3);

        // 1. Cari temuan yang H-3 sampai hari ini menuju deadline
        $upcomingFindings = AuditFinding::with(['pic', 'warehouse'])
            ->whereIn('status', ['Open', 'In Progress'])
            ->whereBetween('due_date', [$today->toDateString(), $threeDaysAhead->toDateString()])
            ->get();

        $upcomingCount = 0;
        foreach ($upcomingFindings as $finding) {
            if ($finding->pic?->email) {
                try {
                    Mail::to($finding->pic->email)->send(new AuditDeadlineReminderMail($finding, 'UPCOMING'));
                    $finding->update(['last_reminded_at' => now()]);
                    $upcomingCount++;
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        // 2. Cari temuan yang sudah melewati deadline (Overdue)
        $overdueFindings = AuditFinding::with(['pic', 'warehouse'])
            ->whereIn('status', ['Open', 'In Progress'])
            ->whereDate('due_date', '<', $today->toDateString())
            ->get();

        $overdueCount = 0;
        foreach ($overdueFindings as $finding) {
            if ($finding->pic?->email) {
                try {
                    Mail::to($finding->pic->email)->send(new AuditDeadlineReminderMail($finding, 'OVERDUE'));
                    $finding->update(['last_reminded_at' => now()]);
                    $overdueCount++;
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        $this->info("Daily reminders processed: {$upcomingCount} upcoming emails sent, {$overdueCount} overdue emails sent.");
    }

    private function processMonthlyReport(): void
    {
        $lastMonth = Carbon::now()->subMonth();
        $periodLabel = $lastMonth->translatedFormat('F Y');

        $totalFindings = AuditFinding::whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->count();

        $closedFindings = AuditFinding::whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->where('status', 'Closed')
            ->count();

        $openExposure = AuditFinding::where('status', '!=', 'Closed')->sum('financial_impact');

        $bySource = AuditFinding::whereMonth('created_at', $lastMonth->month)
            ->whereYear('created_at', $lastMonth->year)
            ->selectRaw('source_type, count(*) as total, sum(financial_impact) as impact')
            ->groupBy('source_type')
            ->get();

        $criticalUnresolved = AuditFinding::with(['pic', 'warehouse'])
            ->where('status', '!=', 'Closed')
            ->whereIn('risk_level', ['High', 'Critical'])
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        $summaryData = [
            'total_findings'      => $totalFindings,
            'closed_findings'     => $closedFindings,
            'resolution_rate'     => $totalFindings > 0 ? round(($closedFindings / $totalFindings) * 100, 1) : 100,
            'open_exposure'       => $openExposure,
            'by_source'           => $bySource,
            'critical_unresolved' => $criticalUnresolved,
        ];

        // Cari penerima: Owner, Admin Pusat, atau Admin
        $recipients = [];
        try {
            $recipients = User::role(['Owner', 'Admin Pusat'])
                ->whereNotNull('email')
                ->pluck('email')
                ->unique()
                ->toArray();
        } catch (\Throwable $e) {
            // In case role table is empty in certain environments
        }

        if (empty($recipients)) {
            $recipients = User::whereNotNull('email')
                ->limit(5)
                ->pluck('email')
                ->unique()
                ->toArray();
        }

        if (!empty($recipients)) {
            try {
                Mail::to($recipients)->send(new MonthlyAuditExecutiveSummaryMail($summaryData, $periodLabel));
                $this->info("Monthly executive audit report sent to " . count($recipients) . " recipients for period {$periodLabel}.");
            } catch (\Throwable $e) {
                $this->error("Failed to send monthly report: " . $e->getMessage());
                report($e);
            }
        } else {
            $this->warn("No recipients found for monthly executive report.");
        }
    }
}
