<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class SecureFileUploadService
{
    /**
     * Daftar MIME Type yang diizinkan secara ketat untuk Gambar.
     */
    public const ALLOWED_IMAGE_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * Daftar MIME Type yang diizinkan untuk Dokumen (PO / Audit / Bukti / Surat Jalan).
     */
    public const ALLOWED_DOC_MIMES = [
        'application/pdf' => 'pdf',
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
        'image/webp'      => 'webp',
    ];

    /**
     * Ekstensi berbahaya yang mutlak ditolak (Layer 5: Defense-in-depth).
     */
    protected const DANGEROUS_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
        'exe', 'bat', 'cmd', 'sh', 'bash', 'bin', 'js', 'html', 'htm',
        'xhtml', 'svg', 'xml', 'dll', 'so', 'cgi', 'pl', 'py', 'vbs',
        'jsp', 'asp', 'aspx', 'htaccess'
    ];

    /**
     * Upload foto dengan validasi berlapis, verifikasi dimensi gambar, re-encoding GD, dan nama UUID.
     *
     * @param UploadedFile $file
     * @param string $directory (Contoh: 'materials', 'equipments', 'opname_proofs', 'purchase_receipts')
     * @param string $disk ('public' atau 'local'/'private')
     * @return string Path file yang tersimpan
     */
    public function uploadImage(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $this->ensureSafeNameAndExtension($file);

        // 1. Pastikan MIME type dideteksi dari isi biner (finfo), bukan dari header client
        $realMime = $file->getMimeType();
        if (!array_key_exists($realMime, self::ALLOWED_IMAGE_MIMES)) {
            throw new RuntimeException('Tipe file gambar tidak valid atau tidak diizinkan.');
        }

        // 2. Verifikasi struktur gambar menggunakan getimagesize() bawaan PHP
        $imageInfo = @getimagesize($file->getRealPath());
        if ($imageInfo === false || $imageInfo[0] <= 0 || $imageInfo[1] <= 0) {
            throw new RuntimeException('File bukan gambar yang valid atau file gambar rusak.');
        }

        // 3. Re-encode gambar (Menghapus metadata EXIF & menghancurkan sisipan payload PHP)
        $cleanExtension = self::ALLOWED_IMAGE_MIMES[$realMime];
        $safeFilename = Str::uuid()->toString() . '.' . $cleanExtension;
        $storagePath = trim($directory, '/') . '/' . date('Y/m');

        $reEncodedImage = $this->stripImageMetadata($file->getRealPath(), $realMime);

        if ($reEncodedImage !== null) {
            $fullPath = $storagePath . '/' . $safeFilename;
            Storage::disk($disk)->put($fullPath, $reEncodedImage);
            return $fullPath;
        }

        // Fallback jika ekstensi GD tidak aktif: simpan dengan UUID aman
        return $file->storeAs($storagePath, $safeFilename, ['disk' => $disk]);
    }

    /**
     * Upload dokumen (PDF / Bukti PO / Laporan Audit) ke storage aman.
     */
    public function uploadDocument(UploadedFile $file, string $directory, string $disk = 'local'): string
    {
        $this->ensureSafeNameAndExtension($file);

        $realMime = $file->getMimeType();
        if (!array_key_exists($realMime, self::ALLOWED_DOC_MIMES)) {
            throw new RuntimeException('Format dokumen tidak diizinkan. Gunakan PDF, JPG, PNG, atau WEBP.');
        }

        // Jika dokumen adalah gambar, re-encode untuk membersihkan EXIF
        if (array_key_exists($realMime, self::ALLOWED_IMAGE_MIMES)) {
            return $this->uploadImage($file, $directory, $disk);
        }

        $cleanExtension = self::ALLOWED_DOC_MIMES[$realMime];
        $safeFilename = Str::uuid()->toString() . '.' . $cleanExtension;
        $storagePath = trim($directory, '/') . '/' . date('Y/m');

        return $file->storeAs($storagePath, $safeFilename, ['disk' => $disk]);
    }

    /**
     * Hapus file lama dengan aman saat update/delete data.
     */
    public function deleteFile(?string $path, string $disk = 'public'): bool
    {
        if (empty($path)) {
            return false;
        }

        // Cegah Path Traversal
        if (str_contains($path, '..')) {
            return false;
        }

        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Sanitasi dan validasi pencegahan double extension.
     */
    protected function ensureSafeNameAndExtension(UploadedFile $file): void
    {
        $originalName = strtolower($file->getClientOriginalName());

        if (preg_match('/\.(php\d?|phtml|phar|pl|py|cgi|sh|bash|exe|bat|cmd|vbs|jsp|asp|aspx)\./i', $originalName)) {
            throw new RuntimeException('Terdeteksi pola double-extension berbahaya.');
        }

        $clientExt = strtolower($file->getClientOriginalExtension());
        $realExt = strtolower($file->extension() ?: $clientExt);

        if (in_array($clientExt, self::DANGEROUS_EXTENSIONS, true) || in_array($realExt, self::DANGEROUS_EXTENSIONS, true)) {
            throw new RuntimeException('Ekstensi berkas tidak diizinkan demi keamanan.');
        }
    }

    /**
     * Membersihkan metadata EXIF dan merender ulang gambar menggunakan GD Library.
     */
    private function stripImageMetadata(string $sourcePath, string $mimeType): ?string
    {
        if (!extension_loaded('gd')) {
            return null;
        }

        $image = match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png'  => @imagecreatefrompng($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            default      => false,
        };

        if ($image === false) {
            return null;
        }

        // Pertahankan transparansi PNG & WebP
        if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        ob_start();
        match ($mimeType) {
            'image/jpeg' => imagejpeg($image, null, 85),
            'image/png'  => imagepng($image, null, 8),
            'image/webp' => imagewebp($image, null, 85),
            default      => null,
        };
        $cleanBuffer = ob_get_clean();
        imagedestroy($image);

        return $cleanBuffer ?: null;
    }
}

