<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SecureUploadService
{
    /**
     * Ekstensi berbahaya yang mutlak ditolak untuk mencegah eksekusi webshell dan payload (Layer 5).
     */
    protected static array $dangerousExtensions = [
        'php', 'php3', 'php4', 'php5', 'phtml', 'phar',
        'exe', 'bat', 'cmd', 'sh', 'bash', 'bin',
        'js', 'html', 'htm', 'xhtml', 'svg', 'xml',
        'dll', 'so', 'cgi', 'pl', 'py'
    ];

    /**
     * Menghasilkan aturan validasi berlapis untuk upload file (Layer 5.1).
     * Menggabungkan validasi ekstensi (mimes) dan MIME terdeteksi oleh finfo (mimetypes).
     */
    public static function rules(bool $required = true, int $maxKb = 10240, bool $allowPdf = true): array
    {
        $mimes = $allowPdf ? 'jpg,jpeg,png,webp,pdf' : 'jpg,jpeg,png,webp';
        $mimetypes = $allowPdf
            ? 'image/jpeg,image/png,image/webp,application/pdf'
            : 'image/jpeg,image/png,image/webp';

        return [
            $required ? 'required' : 'nullable',
            'file',
            'max:' . $maxKb,
            'mimes:' . $mimes,
            'mimetypes:' . $mimetypes,
        ];
    }

    /**
     * Simpan dan re-encode file gambar untuk membersihkan EXIF, metadata, dan embedded payload (Layer 5.3).
     * Jika PDF, simpan langsung dengan nama acak hashName() (Layer 5.2).
     */
    public static function processAndStore(
        UploadedFile $file,
        string $directory,
        string $disk = 'public',
        int $maxDimension = 1600,
        int $quality = 78
    ): string {
        $clientExtension = strtolower($file->getClientOriginalExtension());
        $realExtension = strtolower($file->extension() ?: $clientExtension);

        // 1. Cek blacklist ekstensi berbahaya
        if (in_array($clientExtension, static::$dangerousExtensions) || in_array($realExtension, static::$dangerousExtensions)) {
            throw new InvalidArgumentException("Tipe berkas tidak diizinkan demi keamanan.");
        }

        // 2. Jika PDF, simpan menggunakan hashName() acak
        if ($realExtension === 'pdf' || $clientExtension === 'pdf') {
            $path = $file->store($directory, $disk);
            return $path;
        }

        // 3. Re-encode file gambar menggunakan GD untuk membuang seluruh metadata & payload tersembunyi
        if (function_exists('imagecreatefromstring')) {
            try {
                $imageContent = file_get_contents($file->getRealPath());
                $srcImage = @imagecreatefromstring($imageContent);

                if ($srcImage !== false) {
                    // Deteksi rotasi kamera EXIF jika tersedia sebelum dibuang
                    if (function_exists('exif_read_data')) {
                        $exif = @exif_read_data($file->getRealPath());
                        if (!empty($exif['Orientation'])) {
                            switch ($exif['Orientation']) {
                                case 3:
                                    $srcImage = imagerotate($srcImage, 180, 0);
                                    break;
                                case 6:
                                    $srcImage = imagerotate($srcImage, -90, 0);
                                    break;
                                case 8:
                                    $srcImage = imagerotate($srcImage, 90, 0);
                                    break;
                            }
                        }
                    }

                    $origWidth = imagesx($srcImage);
                    $origHeight = imagesy($srcImage);

                    $newWidth = $origWidth;
                    $newHeight = $origHeight;

                    if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
                        if ($origWidth > $origHeight) {
                            $newWidth = $maxDimension;
                            $newHeight = (int) round(($origHeight / $origWidth) * $maxDimension);
                        } else {
                            $newHeight = $maxDimension;
                            $newWidth = (int) round(($origWidth / $origHeight) * $maxDimension);
                        }
                    }

                    $targetImage = imagecreatetruecolor($newWidth, $newHeight);

                    // Background putih untuk transparansi
                    $white = imagecolorallocate($targetImage, 255, 255, 255);
                    imagefilledrectangle($targetImage, 0, 0, $newWidth, $newHeight, $white);

                    // Resample gambar
                    imagecopyresampled($targetImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

                    // Output gambar murni JPEG
                    ob_start();
                    imagejpeg($targetImage, null, $quality);
                    $cleanData = ob_get_clean();

                    imagedestroy($srcImage);
                    imagedestroy($targetImage);

                    if (!empty($cleanData)) {
                        $filename = trim($directory, '/') . '/' . Str::random(40) . '.jpg';
                        Storage::disk($disk)->put($filename, $cleanData);
                        return $filename;
                    }
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Fallback aman: simpan dengan hashName() jika GD tidak tersedia
        return $file->store($directory, $disk);
    }
}
