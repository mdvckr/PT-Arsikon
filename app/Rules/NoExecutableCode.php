<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class NoExecutableCode implements ValidationRule
{
    /**
     * Run the validation rule.
     * Mencegah upload webshell, double-extension, dan file polyglot yang disisipi kode PHP/eksekusi berbahaya.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile || !$value->isValid()) {
            $fail('File yang diunggah tidak valid.');
            return;
        }

        // 1. Cek ekstensi asli dari client (blokir double extension seperti file.php.jpg)
        $originalName = strtolower($value->getClientOriginalName());
        $dangerousPatterns = [
            '/\.php[0-9]*$/i',
            '/\.phtml$/i',
            '/\.phar$/i',
            '/\.htaccess$/i',
            '/\.sh$/i',
            '/\.bash$/i',
            '/\.pl$/i',
            '/\.py$/i',
            '/\.cgi$/i',
            '/\.exe$/i',
            '/\.bat$/i',
            '/\.cmd$/i',
            '/\.php\./i',   // Double extension: image.php.jpg
            '/\.phtml\./i',
            '/\.phar\./i',
            '/\.sh\./i',
            '/\.pl\./i',
            '/\.cgi\./i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $originalName)) {
                $fail('Nama atau ekstensi file mengandung pola yang dilarang oleh sistem keamanan.');
                return;
            }
        }

        // 2. Pindai isi file (binary stream) dari keberadaan tag PHP atau script berbahaya
        $content = @file_get_contents($value->getRealPath());
        if ($content !== false && $content !== '') {
            $maliciousSignatures = [
                '<?php',
                '<?=',
                '<script',
                'eval(',
                'base64_decode(',
                'shell_exec(',
                'system(',
                'passthru(',
                'popen(',
                'proc_open(',
            ];

            foreach ($maliciousSignatures as $signature) {
                if (stripos($content, $signature) !== false) {
                    $fail('File terdeteksi mengandung kode berbahaya dan ditolak oleh sistem.');
                    return;
                }
            }
        }
    }
}

