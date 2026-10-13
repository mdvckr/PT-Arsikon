<?php

namespace App\Console\Commands;

use App\Models\ToolLoan;
use App\Models\User;
use App\Services\NotificationHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckOverdueToolLoans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tools:check-overdue-loans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa peminjaman alat yang melewati batas tanggal pengembalian, tandai sebagai overdue, dan kirim notifikasi.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today()->toDateString();

        $loans = ToolLoan::with([
            'items.tool',
            'items.assignedTo',
            'fromWarehouse',
            'assignedBy'
        ])
        ->where(function ($q) use ($today) {
            $q->where('status', 'active')
              ->whereNotNull('expected_return_at')
              ->whereDate('expected_return_at', '<', $today);
        })
        ->orWhere(function ($q) {
            $q->where('status', 'overdue')
              ->whereNull('overdue_notified_at');
        })
        ->get();

        if ($loans->isEmpty()) {
            $this->info('Tidak ada peminjaman alat yang terlambat (overdue).');
            return Command::SUCCESS;
        }

        $processedCount = 0;
        foreach ($loans as $loan) {
            $wasActive = ($loan->status === 'active');
            $needsNotification = is_null($loan->overdue_notified_at);

            // Tandai status sebagai 'overdue'
            $loan->status = 'overdue';
            if ($needsNotification) {
                $loan->overdue_notified_at = now();
            }
            $loan->save();

            // Sinkronkan status pada item penugasan
            $loan->items()->where('status', 'active')->update([
                'status' => 'overdue',
            ]);

            if ($needsNotification) {
                $expectedDate = $loan->expected_return_at ? $loan->expected_return_at->format('d/m/Y') : '-';

                // 1. Notifikasi ke peminjam (bila punya akun terdaftar)
                $borrowerUser = $loan->items->first()?->assignedTo;
                if (!$borrowerUser && !empty($loan->borrower_name)) {
                    $borrowerUser = User::where('name', $loan->borrower_name)->first();
                }
                if (!$borrowerUser && !empty($loan->borrower_phone)) {
                    $borrowerUser = User::where('phone', $loan->borrower_phone)->first();
                }

                if ($borrowerUser) {
                    NotificationHelper::notifyUser(
                        $borrowerUser,
                        "Peringatan: Peminjaman Alat Terlambat #{$loan->loan_number}",
                        "Peminjaman alat #{$loan->loan_number} telah melewati batas pengembalian ({$expectedDate}). Mohon segera kembalikan alat ke {$loan->fromWarehouse?->name}.",
                        "danger",
                        route('tool-assignments.show', $loan->id)
                    );
                }

                // 2. Notifikasi ke pengaju
                if ($loan->assignedBy) {
                    NotificationHelper::notifyUser(
                        $loan->assignedBy,
                        "Peringatan: Peminjaman Alat Terlambat #{$loan->loan_number}",
                        "Peminjaman alat #{$loan->loan_number} untuk {$loan->borrower_name} telah melewati batas pengembalian ({$expectedDate}).",
                        "danger",
                        route('tool-assignments.show', $loan->id)
                    );
                }

                // 3. Notifikasi ke admin gudang asal peminjaman
                $wh = $loan->fromWarehouse;
                if ($wh) {
                    if ($wh->is_central) {
                        NotificationHelper::notifyCentralWarehouseAdmins(
                            "Peminjaman Alat Terlambat: #{$loan->loan_number}",
                            "Peminjaman alat #{$loan->loan_number} dari {$wh->name} oleh {$loan->borrower_name} telah melewati batas pengembalian ({$expectedDate}).",
                            "warning",
                            route('tool-assignments.show', $loan->id)
                        );
                    } else {
                        NotificationHelper::notifyProjectWarehouseAdmins(
                            $wh->id,
                            "Peminjaman Alat Terlambat: #{$loan->loan_number}",
                            "Peminjaman alat #{$loan->loan_number} dari {$wh->name} oleh {$loan->borrower_name} telah melewati batas pengembalian ({$expectedDate}).",
                            "warning",
                            route('tool-assignments.show', $loan->id)
                        );
                    }
                }

                $processedCount++;
            }
        }

        $this->info("Berhasil memperbarui status dan mengirim notifikasi untuk {$processedCount} peminjaman alat yang terlambat.");
        return Command::SUCCESS;
    }
}
