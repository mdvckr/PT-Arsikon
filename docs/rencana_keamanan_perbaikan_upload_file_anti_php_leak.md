# Full-Stack Security Patching Plan: Mitigasi Celah Keamanan File/Foto Upload (Anti-PHP Leak / RCE)

**Proyek:** PT Arsikon Cipta Karya - WMS & Procurement System  
**Tech Stack:** Laravel 12, PHP 8.2+, Nginx/Apache  
**Fokus Modul:** Keamanan Unggah File (Foto Material, Bukti Opname, Dokumen PO/Surat Jalan, Foto Profil/Alat)  
**Target Eksekutor:** AI Coding Agent / Full-Stack Developer  

---

## 1. Analisis Masalah (Mengapa Terjadi "Bocor PHP" saat Upload?)

Celah keamanan pada fitur *upload* foto/file (sering disebut *Unrestricted File Upload* atau *Webshell Upload*) biasanya terjadi karena kesalahan implementasi berikut:
1. **Validasi Hanya di Frontend atau Hanya Cek Ekstensi:** Menggunakan `$request->file('foto')->getClientOriginalExtension()` tanpa memeriksa *MIME Type* asli (isi biner file). Penyerang bisa mengganti nama file `shell.php` menjadi `foto.php.jpg` atau memalsukan header request.
2. **Menyimpan File dengan Nama Asli (`getClientOriginalName()`):** Menyimpan file menggunakan nama bawaan dari user membuka celah *Path Traversal* dan eksekusi script berbahaya.
3. **Menyimpan Langsung di Folder `public/`:** Menggunakan `$file->move(public_path('uploads'), ...)` memungkinkan file diakses dan dieksekusi langsung oleh web server melalui URL jika konfigurasi server tidak memblokir eksekusi PHP di folder tersebut.
4. **File PHP Terselip di Metadata Gambar (EXIF/Polyglot):** Penyerang menyisipkan kode `<?php ... ?>` di dalam komentar metadata gambar JPG/PNG yang valid.

---

## 2. Arsitektur Solusi Pertahanan Berlapis (Defense-in-Depth)

Untuk memastikan sistem 100% aman dari kebocoran eksekusi file PHP, agen pengembang harus menerapkan **5 Lapisan Pertahanan**:

1. **Lapisan 1 - Validasi Ketat (MIME Type + Magic Bytes):** Memakai aturan validasi `File::image()` dan `mimes` + `mimetypes` bawaan Laravel, serta memindai isi file agar tidak mengandung tag `<?php`.
2. **Lapisan 2 - Sanitasi & Randomisasi Nama File (UUID/Hash):** Dilarang keras memakai `getClientOriginalName()`. Semua file wajib di-rename menggunakan `Str::uuid()` dengan ekstensi yang ditentukan dari hasil deteksi MIME server (`extension()`), bukan dari input user.
3. **Lapisan 3 - Isolasi Penyimpanan (`storage/app/private` & `storage/app/public`):**
   * Foto publik (seperti katalog material/alat) disimpan di `storage/app/public` dengan nama teracak.
   * Dokumen sensitif (Invoice PO, Bukti Audit, Surat Jalan) disimpan di `storage/app/private` dan hanya bisa diakses melalui *Controller* yang terautentikasi (bukan URL publik langsung).
4. **Lapisan 4 - Re-encoding Gambar (Opsional/Rekomendasi Tinggi):** Memproses ulang gambar atau membersihkan metadata EXIF sehingga kode PHP yang disisipkan di dalam gambar otomatis hancur.
5. **Lapisan 5 - Blokir Eksekusi PHP di Level Web Server (`.htaccess` & Nginx):** Memastikan folder `storage` dan `uploads` tidak diizinkan mengeksekusi script `.php`, `.phtml`, atau `.phar`.

---

## 3. Implementasi Kode (Step-by-Step untuk Coding Agent)

### 3.1. Buat Custom Validation Rule: Deteksi Tag PHP di Dalam File
Rule ini memastikan bahwa meskipun file memiliki ekstensi dan header `.jpg` atau `.pdf`, jika di dalam biner file terdapat sisipan script `<?php` atau `<?=`, upload akan langsung ditolak.

**File:** `app/Rules/NoExecutableCode.php`

```php
<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class NoExecutableCode implements ValidationRule
{
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
            '/\.pl$/i',
            '/\.py$/i',
            '/\.cgi$/i',
            '/\.php\./i', // Mencegah double extension: image.php.jpg
            '/\.phtml\./i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $originalName)) {
                $fail('Nama atau ekstensi file mengandung pola yang dilarang oleh sistem keamanan.');
                return;
            }
        }

        // 2. Pindai isi file (binary stream) dari keberadaan tag PHP atau script berbahaya
        $content = file_get_contents($value->getRealPath());
        if ($content !== false) {
            $maliciousSignatures = [
                '<?php',
                '<?=',
                '<script',
                'eval(',
                'base64_decode(',
                'shell_exec(',
                'system(',
                'passthru(',
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
```

---

### 3.2. Buat Centralized Secure File Upload Service
Semua proses upload di seluruh Controller (Material, Alat, Stock Opname, PO, dan Audit) **wajib** melewati satu Service ini agar tidak ada developer yang menulis logika upload yang tidak aman secara terpisah.

**File:** `app/Services/SecureFileUploadService.php`

```php
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
    private const ALLOWED_IMAGE_MIMES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * Daftar MIME Type yang diizinkan untuk Dokumen (PO / Audit / Surat Jalan).
     */
    private const ALLOWED_DOC_MIMES = [
        'application/pdf' => 'pdf',
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
    ];

    /**
     * Upload foto dengan validasi berlapis, verifikasi dimensi gambar, dan nama UUID.
     *
     * @param UploadedFile $file
     * @param string $directory (Contoh: 'materials', 'equipments', 'opname_proofs')
     * @param string $disk ('public' atau 'local' untuk private)
     * @return string Path file yang tersimpan
     */
    public function uploadImage(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        // 1. Pastikan MIME type dideteksi dari isi biner (finfo), bukan dari header HTTP
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

        // Fallback jika ekstensi GD tidak aktif: tetap simpan dengan nama UUID yang aman
        return $file->storeAs($storagePath, $safeFilename, ['disk' => $disk]);
    }

    /**
     * Upload dokumen (PDF / Bukti PO / Laporan Audit) ke storage private.
     */
    public function uploadDocument(UploadedFile $file, string $directory, string $disk = 'local'): string
    {
        $realMime = $file->getMimeType();
        if (!array_key_exists($realMime, self::ALLOWED_DOC_MIMES)) {
            throw new RuntimeException('Format dokumen tidak diizinkan. Gunakan PDF, JPG, atau PNG.');
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
```

---

### 3.3. Form Request Standar untuk Validasi Upload
Gunakan Form Request ini pada endpoint yang menerima upload foto/dokumen.

**File:** `app/Http/Requests/SecureUploadRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;
use App\Rules\NoExecutableCode;

class SecureUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // Contoh validasi untuk field 'photo' (Maksimal 2MB)
            'photo' => [
                'nullable',
                'file',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max(2 * 1024), // 2 MB
                'mimetypes:image/jpeg,image/png,image/webp',
                new NoExecutableCode(),
            ],

            // Contoh validasi untuk field 'attachment' / dokumen PO & Audit (Maksimal 5MB)
            'attachment' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'mimetypes:application/pdf,image/jpeg,image/png',
                'max:5120', // 5 MB
                new NoExecutableCode(),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.image'          => 'File yang diunggah harus berupa gambar.',
            'photo.mimetypes'      => 'Format gambar hanya boleh JPG, PNG, atau WEBP.',
            'photo.max'            => 'Ukuran foto maksimal 2MB.',
            'attachment.mimes'     => 'Lampiran dokumen hanya boleh berformat PDF, JPG, atau PNG.',
            'attachment.max'       => 'Ukuran dokumen maksimal 5MB.',
        ];
    }
}
```

---

### 3.4. Contoh Penggunaan di Controller (Mengganti Kode Lama yang Rawan)

Instruksikan kepada *Coding Agent* untuk mencari semua penggunaan `$request->file(...)` atau `move(public_path(...))` di proyek PT Arsikon dan menggantinya dengan pola berikut:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\SecureUploadRequest;
use App\Services\SecureFileUploadService;
use App\Models\Material;

class MaterialController extends Controller
{
    protected SecureFileUploadService $uploadService;

    public function __construct(SecureFileUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function store(SecureUploadRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            // Simpan dengan aman (otomatis cek MIME, strip EXIF/PHP tag, dan rename ke UUID)
            $data['photo_path'] = $this->uploadService->uploadImage(
                $request->file('photo'),
                'materials',
                'public'
            );
        }

        Material::create($data);

        return redirect()->route('materials.index')
            ->with('success', 'Data material berhasil disimpan dengan aman.');
    }
}
```

---

### 3.5. Controller Khusus Akses Dokumen Private (PO, Surat Jalan, Bukti Audit)
Untuk dokumen internal perusahaan, jangan letakkan di `public/storage`, melainkan di `storage/app/local` dan sajikan melalui route yang dilindungi middleware `auth`.

**File:** `app/Http/Controllers/SecureAttachmentController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecureAttachmentController extends Controller
{
    public function show(Request $request, string $path): StreamedResponse
    {
        // Cegah Path Traversal
        if (str_contains($path, '..') || !Storage::disk('local')->exists($path)) {
            abort(404, 'Dokumen tidak ditemukan.');
        }

        $mimeType = Storage::disk('local')->mimeType($path);

        // Paksa header keamanan agar browser tidak mengeksekusi script (X-Content-Type-Options: nosniff)
        return Storage::disk('local')->response($path, null, [
            'Content-Type'           => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Disposition'    => 'inline',
        ]);
    }
}
```

---

## 4. Hardening Level Web Server (Mematikan Eksekusi PHP di Folder Upload)

Meskipun seseorang berhasil menembus aplikasi dan menaruh file `.php` di dalam folder `public/storage` atau `public/uploads`, web server harus dikonfigurasi agar **menolak mengeksekusi script PHP** di dalam direktori tersebut.

### 4.1. Untuk Apache / Laragon (`.htaccess`)
Buat file `.htaccess` di dalam folder `storage/app/public/.htaccess` dan `public/uploads/.htaccess` (jika ada):

```apache
# Matikan eksekusi PHP dan script CGI di dalam direktori upload/storage
<FilesMatch "\.(?i:php|php[0-9]|phtml|phar|pl|py|jsp|asp|htm|html|sh|cgi)$">
    <IfModule mod_authz_core.c>
        Require all denied
    </IfModule>
    <IfModule !mod_authz_core.c>
        Order allow,deny
        Deny from all
    </IfModule>
</FilesMatch>

# Matikan PHP Engine jika modul mod_php aktif
<IfModule mod_php.c>
    php_flag engine off
</IfModule>
<IfModule mod_php8.c>
    php_flag engine off
</IfModule>

# Paksa browser untuk tidak menebak MIME type (Mencegah MIME Sniffing XSS)
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
</IfModule>

Options -Indexes -ExecCGI
```

### 4.2. Untuk Nginx (`nginx.conf` / konfigurasi site VPS)
Tambahkan blok `location` ini sebelum blok `location ~ \.php$` utama:

```nginx
# Blokir eksekusi file PHP di dalam folder storage atau uploads
location ~* ^/(storage|uploads)/.*\.(php|php[0-9]|phtml|phar|pl|py|sh|cgi)$ {
    deny all;
    return 403;
}
```

---

## 5. Checklist Audit & Refactoring untuk AI Coding Agent

Berikan instruksi berikut kepada *Coding Agent* Anda saat mengeksekusi dokumen ini:

1. **Audit Kode Lama:**
   * Cari seluruh kemunculan `getClientOriginalName()`, `move(public_path`, `storeAs(`, dan `$request->file(` di seluruh folder `app/Http/Controllers/`.
   * Hapus penggunaan `getClientOriginalName()` sebagai nama file penyimpanan fisik di disk. Jika nama asli diperlukan untuk ditampilkan di UI, simpan nama aslinya di kolom database `original_filename` (setelah disanitasi dengan `basename()`), sementara file fisiknya wajib memakai nama `UUID`.
2. **Terapkan Service & Rule Baru:**
   * Buat file `app/Rules/NoExecutableCode.php` dan `app/Services/SecureFileUploadService.php`.
   * Pasang rule `NoExecutableCode` dan `mimetypes` pada semua validasi upload foto material, alat, bukti stock opname, dan dokumen PO.
3. **Pembersihan File Liar (Cleanup):**
   * Periksa folder `public/uploads` dan `storage/app/public`. Pastikan tidak ada file berekstensi `.php`, `.phtml`, atau `.phar` yang tertinggal dari pengujian sebelumnya.
4. **Pasang Proteksi `.htaccess`:**
   * Buat file `storage/app/public/.htaccess` sesuai spesifikasi pada Bagian 4.1 agar otomatis ter-link ke `public/storage/.htaccess`.



pahami dulu jadilah cybersecurity profesional lalu build use context7 dan lanjutkan untuk merapihkan kode 