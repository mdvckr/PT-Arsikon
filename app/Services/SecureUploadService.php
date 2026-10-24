<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class SecureUploadService
{
    /**
     * Ekstensi berbahaya yang mutlak ditolak untuk mencegah eksekusi webshell dan payload (Layer 5 / High-Security Hardening).
     */
    protected static array $dangerousExtensions = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
        'exe', 'bat', 'cmd', 'sh', 'bash', 'bin',
        'js', 'html', 'htm', 'xhtml', 'svg', 'xml',
        'dll', 'so', 'cgi', 'pl', 'py', 'vbs', 'jsp', 'asp', 'aspx'
    ];

    /**
     * Aturan validasi ketat berlapis untuk form upload (FASE 4: Anti-PHP2Shell).
     * Format: jpg, jpeg, png, pdf (max 2048 KB / 2MB).
     */
    public static function strictRules(bool $required = true, int $maxKb = 2048): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'mimes:jpg,jpeg,png,pdf',
            'mimetypes:image/jpeg,image/png,application/pdf',
            'max:' . $maxKb,
        ];
    }

    /**
     * Menghasilkan aturan validasi fleksibel.
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
     * Validasi anti-ekstensi ganda (mencegah file seperti shell.php.jpg atau dokumen.phtml).
     *
     * @throws ValidationException
     */
    public static function validateSafeFile(UploadedFile $file, string $inputKey = 'image'): void
    {
        $originalName = strtolower($file->getClientOriginalName());

        // 1. Deteksi ekstensi ganda (.php.*, .phtml.*, .phar.*, dll)
        if (preg_match('/\.(php\d?|phtml|phar|pl|py|cgi|sh|bash|exe|bat|cmd|vbs|jsp|asp|aspx)\./i', $originalName)) {
            throw ValidationException::withMessages([
                $inputKey => 'File ditolak: Terdeteksi ekstensi ganda (double-extension) yang berpotensi membahayakan sistem.',
            ]);
        }

        // 2. Deteksi ekstensi akhir terlarang
        $clientExtension = strtolower($file->getClientOriginalExtension());
        $realExtension = strtolower($file->extension() ?: $clientExtension);

        if (in_array($clientExtension, static::$dangerousExtensions) || in_array($realExtension, static::$dangerousExtensions)) {
            throw ValidationException::withMessages([
                $inputKey => 'File ditolak: Tipe berkas tidak diizinkan demi keamanan.',
            ]);
        }
    }

    /**
     * Simpan file secara acak (randomized filename hashName/UUID) ke private storage
     * dan lakukan sanitasi image/GD re-encode bila merupakan gambar.
     */
    public static function processAndStore(
        UploadedFile $file,
        string $directory,
        string $disk = 'private',
        int $maxDimension = 1600,
        int $quality = 78
    ): string {
        // Validasi ekstensi ganda dan karakter berbahaya
        static::validateSafeFile($file);

        $clientExtension = strtolower($file->getClientOriginalExtension());
        $realExtension = strtolower($file->extension() ?: $clientExtension);

        // 1. Cek blacklist ekstensi berbahaya
        if (in_array($clientExtension, static::$dangerousExtensions) || in_array($realExtension, static::$dangerousExtensions)) {
            throw new InvalidArgumentException("Tipe berkas tidak diizinkan demi keamanan.");
        }

        // 2. Jika PDF, simpan langsung dengan randomized filename (hashName)
        if ($realExtension === 'pdf' || $clientExtension === 'pdf') {
            $randomName = Str::random(40) . '.pdf';
            $path = trim($directory, '/') . '/' . $randomName;
            Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));
            return $path;
        }

        // 3. Re-encode file gambar menggunakan GD untuk membuang seluruh metadata & payload PHP webshell
        if (function_exists('imagecreatefromstring')) {
            try {
                $imageContent = file_get_contents($file->getRealPath());
                $srcImage = @imagecreatefromstring($imageContent);

                if ($srcImage !== false) {
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

                    $white = imagecolorallocate($targetImage, 255, 255, 255);
                    imagefilledrectangle($targetImage, 0, 0, $newWidth, $newHeight, $white);

                    imagecopyresampled($targetImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

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

        // Fallback aman: simpan dengan hashName() acak pada private storage
        $randomName = Str::random(40) . '.' . ($realExtension ?: 'jpg');
        $path = trim($directory, '/') . '/' . $randomName;
        Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));
        return $path;
    }
}
