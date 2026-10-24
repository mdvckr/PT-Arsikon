<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SecurityAuditCommand extends Command
{
    protected $signature = 'security:check';
    protected $description = 'Audit keamanan konfigurasi dan kode aplikasi PT-Arsikon (Laravel 12)';

    public function handle()
    {
        $this->info('====================================================');
        $this->info('   LAPORAN AUDIT KEAMANAN SISTEM — PT ARSIKON       ');
        $this->info('====================================================');

        $results = [];

        // 1. Cek APP_DEBUG vs APP_ENV
        $debug = config('app.debug');
        $env = config('app.env');
        $results[] = [
            'Pemeriksaan' => '1. Mode Debug & Environment',
            'Status' => ($env === 'production' && $debug === true) ? '❌ BAHAYA' : ($debug ? '⚠️ DEV MODE' : '✅ AMAN'),
            'Keterangan' => $debug 
                ? "APP_DEBUG=true (Matikan ke false saat diakses lewat Cloudflare Tunnel/Online)" 
                : "APP_DEBUG=false (Aman dari kebocoran .env)",
        ];

        // 2. Cek Keamanan Cookie Session (Anti-XSS & Session Hijacking)
        $httpOnly = config('session.http_only');
        $sameSite = config('session.same_site');
        $results[] = [
            'Pemeriksaan' => '2. Proteksi Cookie & Session',
            'Status' => ($httpOnly && in_array($sameSite, ['lax', 'strict'])) ? '✅ AMAN' : '❌ RAWAN',
            'Keterangan' => "http_only: " . ($httpOnly ? 'Ya' : 'Tidak') . " | same_site: " . ($sameSite ?? 'kosong'),
        ];

        // 3. Cek Model Tanpa Proteksi Mass Assignment ($guarded = [])
        $modelsPath = app_path('Models');
        $unguardedModels = [];
        if (File::exists($modelsPath)) {
            foreach (File::allFiles($modelsPath) as $file) {
                $content = File::get($file->getRealPath());
                if (preg_match('/\$guarded\s*=\s*\[\s*\]/', $content)) {
                    $unguardedModels[] = $file->getFilenameWithoutExtension();
                }
            }
        }
        $results[] = [
            'Pemeriksaan' => '3. Proteksi Model (Mass Assignment)',
            'Status' => count($unguardedModels) === 0 ? '✅ AMAN' : '⚠️ PERIKSA',
            'Keterangan' => count($unguardedModels) === 0 
                ? 'Semua Model menggunakan proteksi $fillable' 
                : 'Model memakai $guarded=[]: ' . implode(', ', $unguardedModels),
        ];

        // 4. Cek Apakah Ada File .php Mencurigakan di public/storage (Anti-WebShell)
        $publicStorage = public_path('storage');
        $suspiciousFiles = [];
        if (File::exists($publicStorage)) {
            foreach (File::allFiles($publicStorage) as $file) {
                if (in_array(strtolower($file->getExtension()), ['php', 'phtml', 'phar', 'sh', 'cgi'])) {
                    $suspiciousFiles[] = $file->getRelativePathname();
                }
            }
        }
        $results[] = [
            'Pemeriksaan' => '4. Scan File .php di Folder Upload',
            'Status' => count($suspiciousFiles) === 0 ? '✅ AMAN' : '❌ BAHAYA',
            'Keterangan' => count($suspiciousFiles) === 0 
                ? 'Tidak ditemukan file script (.php/.phtml) di public/storage' 
                : 'Ditemukan file berbahaya: ' . implode(', ', $suspiciousFiles),
        ];

        // 5. Cek Potensi Raw Output XSS ({!! !!}) di Blade
        $viewsPath = resource_path('views');
        $rawBladeCount = 0;
        if (File::exists($viewsPath)) {
            foreach (File::allFiles($viewsPath) as $file) {
                $content = File::get($file->getRealPath());
                if (preg_match('/\{!!.*!!\}/', $content)) {
                    $rawBladeCount++;
                }
            }
        }
        $results[] = [
            'Pemeriksaan' => '5. Scan Raw Output Blade ({!! !!})',
            'Status' => $rawBladeCount === 0 ? '✅ AMAN' : 'ℹ️ INFO',
            'Keterangan' => $rawBladeCount === 0 
                ? 'Semua variabel Blade ter-escape otomatis dengan {{ }}' 
                : "Ada {$rawBladeCount} file Blade memakai {!! !!} (Pastikan bukan input user)",
        ];

        $this->table(['Pemeriksaan', 'Status', 'Keterangan'], $results);
        return Command::SUCCESS;
    }
}