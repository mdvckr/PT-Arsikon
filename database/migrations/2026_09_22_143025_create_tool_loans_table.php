<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tool_loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number')->unique();
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->foreignId('assigned_by_user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('borrower_name');
            $table->string('borrower_phone')->nullable();
            $table->string('location_name');
            $table->timestamp('assigned_at')->useCurrent();
            $table->date('expected_return_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->enum('status', ['pending', 'active', 'returned', 'overdue', 'lost', 'rejected', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
        });

        Schema::table('tool_assignments', function (Blueprint $table) {
            $table->foreignId('tool_loan_id')->nullable()->after('id')->constrained('tool_loans')->onDelete('cascade');
        });

        // Migrasi data lama dari tool_assignments ke tool_loans
        $existing = DB::table('tool_assignments')->get();
        // Kelompokkan data lama berdasarkan borrower_name, location_name, assigned_at, from_warehouse_id
        $grouped = $existing->groupBy(function ($item) {
            return ($item->borrower_name ?? 'anon') . '|' . ($item->location_name ?? 'loc') . '|' . substr((string)$item->assigned_at, 0, 16) . '|' . $item->from_warehouse_id;
        });

        $counter = 1;
        foreach ($grouped as $group) {
            $first = $group->first();
            $loanNum = 'TL-' . date('Ymd', strtotime($first->assigned_at ?? 'now')) . '-' . str_pad($counter++, 4, '0', STR_PAD_LEFT);

            $loanId = DB::table('tool_loans')->insertGetId([
                'loan_number'          => $loanNum,
                'from_warehouse_id'    => $first->from_warehouse_id,
                'assigned_by_user_id'  => $first->assigned_by_user_id,
                'approved_by_user_id'  => $first->approved_by_user_id,
                'borrower_name'        => $first->borrower_name ?? 'Peminjam',
                'borrower_phone'       => $first->borrower_phone,
                'location_name'        => $first->location_name ?? '-',
                'assigned_at'          => $first->assigned_at ?? now(),
                'expected_return_at'   => $first->expected_return_at,
                'returned_at'          => $first->returned_at,
                'status'               => $first->status,
                'notes'                => $first->notes,
                'rejection_reason'     => $first->rejection_reason,
                'cancelled_at'         => $first->cancelled_at,
                'cancelled_by_user_id' => $first->cancelled_by_user_id,
                'cancellation_reason'  => $first->cancellation_reason,
                'created_at'           => $first->created_at ?? now(),
                'updated_at'           => $first->updated_at ?? now(),
            ]);

            $ids = $group->pluck('id')->toArray();
            DB::table('tool_assignments')->whereIn('id', $ids)->update(['tool_loan_id' => $loanId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tool_assignments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tool_loan_id');
        });
        Schema::dropIfExists('tool_loans');
    }
};
