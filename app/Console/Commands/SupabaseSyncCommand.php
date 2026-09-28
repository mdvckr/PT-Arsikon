<?php

namespace App\Console\Commands;

use App\Services\SupabaseSyncService;
use Illuminate\Console\Command;
use Throwable;

class SupabaseSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'supabase:sync 
                            {--push : Cadangkan data lokal (Material & Alat) ke Supabase}
                            {--pull : Tarik/pulihkan data dari Supabase ke database lokal}
                            {--test : Uji status koneksi ke Supabase Cloud}';

    /**
     * The console command description.
     */
    protected $description = 'Sinkronisasi dua arah katalog Material & Alat antara MySQL lokal dan Supabase Cloud';

    public function __construct(protected SupabaseSyncService $syncService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('====================================================');
        $this->info('   PT-Arsikon — Supabase Cloud Catalog Sync');
        $this->info('====================================================');

        if ($this->option('test')) {
            return $this->handleTest();
        }

        if ($this->option('push')) {
            return $this->handlePush();
        }

        if ($this->option('pull')) {
            return $this->handlePull();
        }

        // Jika tidak ada opsi, tanyakan kepada pengguna di CLI
        $choice = $this->choice('Pilih aksi sinkronisasi yang ingin dijalankan:', [
            '1' => 'Uji Koneksi Supabase',
            '2' => 'PUSH: Cadangkan data lokal ke Supabase',
            '3' => 'PULL: Tarik data dari Supabase ke lokal (Restore)',
            '4' => 'Keluar',
        ], '1');

        return match ($choice) {
            '1', 'Uji Koneksi Supabase' => $this->handleTest(),
            '2', 'PUSH: Cadangkan data lokal ke Supabase' => $this->handlePush(),
            '3', 'PULL: Tarik data dari Supabase ke lokal (Restore)' => $this->handlePull(),
            default => Command::SUCCESS,
        };
    }

    protected function handleTest(): int
    {
        $this->comment('Memeriksa koneksi ke Supabase Cloud...');
        $result = $this->syncService->testConnection();

        if ($result['connected']) {
            $this->info('✔ ' . $result['message']);
            $this->line("  Latensi: {$result['latency_ms']} ms");
            $this->line("  Versi Server: {$result['version']}");
            return Command::SUCCESS;
        }

        $this->error('✖ ' . $result['message']);
        return Command::FAILURE;
    }

    protected function handlePush(): int
    {
        $this->comment('Memulai proses PUSH ke Supabase Cloud...');

        try {
            $result = $this->syncService->pushToSupabase();
            $this->info('✔ ' . $result['message']);
            $this->line("  Kategori dicadangkan         : {$result['categories_count']} item");
            $this->line("  Satuan dicadangkan           : {$result['units_count']} item");
            $this->line("  Material dicadangkan         : {$result['materials_count']} item");
            $this->line("  Alat dicadangkan             : {$result['tools_count']} item");
            $this->line("  Stok Material Gudang         : {$result['inventories_count']} item");
            $this->line("  Stok Alat Gudang             : {$result['tool_inventories_count']} item");
            $this->line("  Timestamp                    : {$result['synced_at']}");
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error('✖ Gagal melakukan PUSH: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function handlePull(): int
    {
        $this->comment('Memulai proses PULL (Restore) dari Supabase Cloud ke MySQL lokal...');

        try {
            $result = $this->syncService->pullFromSupabase();
            $this->info('✔ ' . $result['message']);
            $this->line("  Kategori dipulihkan         : {$result['categories_restored']} item");
            $this->line("  Satuan dipulihkan           : {$result['units_restored']} item");
            $this->line("  Material dipulihkan         : {$result['materials_restored']} item");
            $this->line("  Alat dipulihkan             : {$result['tools_restored']} item");
            $this->line("  Stok Material Gudang        : {$result['inventories_restored']} item");
            $this->line("  Stok Alat Gudang            : {$result['tool_inventories_restored']} item");
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error('✖ Gagal melakukan PULL: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
