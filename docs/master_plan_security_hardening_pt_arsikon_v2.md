# Master Plan v2: Security Hardening Sistem Informasi PT Arsikon

**Proyek:** Sistem Informasi Manajemen PT Arsikon (`mdvckr/PT-Arsikon`, Laravel)
**Target:** Hardening baseline yang terukur, diverifikasi, dan dapat dipelihara (mengacu OWASP Top 10 dan OWASP ASVS Level 2)
**Perubahan utama dari v1:** koreksi teknis (CVE, `disable_functions`, sesi), penambahan lapisan server/OS, 2FA, deployment, respons insiden, dan verifikasi. Setiap kontrol punya cara uji.

> **Catatan istilah.** Dokumen ini disebut *hardening baseline*, bukan "zero-trust penuh". Zero-trust adalah arsitektur yang lebih luas (identitas per request, segmentasi, dsb.). Yang diterapkan di sini adalah prinsip-prinsipnya: akses minimum, verifikasi eksplisit, dan asumsi bahwa lapisan lain bisa bocor.

---

## LAYER 0: SCOPE, ASET, DAN THREAT MODEL (Kerjakan Dulu)

Tanpa ini, kontrol di bawah hanya daftar tebakan.

| Aset | Contoh | Dampak jika bocor/rusak |
|---|---|---|
| Data keuangan & PO | Tabel PO, barang, nota | Kerugian finansial, manipulasi |
| Akun & kredensial | Tabel users, `.env`, `APP_KEY` | Pengambilalihan akun/server |
| Dokumen & upload | Bukti nota, lampiran | Kebocoran dokumen perusahaan |
| Backup | Dump database | Kebocoran total jika tidak terenkripsi |

**Isi tabel berikut:**
- **Siapa penyerang realistis:** bot/scanner otomatis, pengguna internal yang nakal, akun staf yang dicuri (phishing).
- **Peran pengguna:** daftar role (admin, manajer, staf) dan apa yang boleh dilakukan masing-masing.
- **Di mana server berjalan:** VPS Linux, server lokal + Cloudflare Tunnel, atau Windows (XAMPP/Laragon). Ini menentukan bagian mana yang relevan.
- **Target pemulihan:** RPO (maksimal data hilang yang bisa diterima, mis. 24 jam) dan RTO (maksimal waktu pemulihan, mis. 4 jam).

---

## LAYER 1: PHP RUNTIME

### 1.1 Versi dan CVE-2024-4577

- **CVE-2024-4577 (PHP-CGI argument injection)** hanya berdampak pada **PHP di Windows** yang berjalan dalam mode CGI (umum pada XAMPP default) dengan locale tertentu. Pada Linux dengan PHP-FPM, risikonya praktis tidak ada.
- Versi yang sudah di-patch: **8.1.29, 8.2.20, 8.3.8** ke atas.
- Namun jangan berhenti di versi patch minimum. Gunakan versi yang **masih mendapat security support** (cek `https://www.php.net/supported-versions.php`; PHP 8.1 sudah EOL). Rekomendasi: PHP 8.3 atau lebih baru, dan versi Laravel yang masih didukung (cek `https://laravel.com/docs/releases`).
- **Produksi Linux:** Nginx/Apache + **PHP-FPM**. Jangan pakai PHP-CGI.
- **Jika terpaksa Windows:** jangan tampilkan XAMPP/Laragon langsung ke internet, jangan pakai mode CGI, pastikan versi sudah di-patch, dan tempatkan di belakang Cloudflare Tunnel/Access. Untuk produksi, Linux sangat disarankan.
- Cek versi: `php -v`.

### 1.2 Hapus klaim "Celah #2"

Pada v1, "celah `proc_open`/bypass filter" tidak punya CVE dan deskripsi yang bisa diverifikasi. Mematikan fungsi eksekusi adalah **defense-in-depth**, bukan penambalan bug spesifik. Di laporan/presentasi, tuliskan sebagai: *"pembatasan fungsi berbahaya untuk memperkecil dampak jika terjadi eksploitasi/upload webshell"*.

### 1.3 `php.ini` untuk **FPM/web** (bukan CLI)

Pisahkan konfigurasi web dan CLI. Banyak perintah penting (`spatie/laravel-backup`, queue worker, scheduler) berjalan lewat **CLI** dan membutuhkan fungsi yang dimatikan di web. Contoh lokasi di Linux: `/etc/php/8.3/fpm/php.ini` (web) dan `/etc/php/8.3/cli/php.ini` (CLI, jangan terapkan `disable_functions` ketat di sini).

```ini
; --- Fungsi berbahaya (hanya untuk FPM/web) ---
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,pcntl_exec,pcntl_fork,show_source

; --- Informasi & error ---
expose_php = Off
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php/arsikon-error.log

; --- Include dari URL ---
allow_url_include = Off
; allow_url_fopen dibiarkan On bila aplikasi/paket butuh (mis. HTTP client). Matikan hanya jika sudah diuji.

; --- Batasan path (lapisan tambahan, bukan sandbox kuat) ---
open_basedir = "/var/www/pt-arsikon:/tmp"

; --- Batas sumber daya ---
upload_max_filesize = 5M
post_max_size = 8M
max_execution_time = 30
memory_limit = 256M

; --- Opcache produksi (reload PHP-FPM setiap deploy) ---
opcache.enable = 1
opcache.validate_timestamps = 0
```

**Koreksi dari v1 (dan alasannya):**
- `curl_multi_exec` **dikeluarkan** dari daftar: dipakai Guzzle/HTTP Client Laravel, jadi bisa merusak panggilan API.
- `parse_ini_file`, `dl`, `eval`, `assert` **dikeluarkan**: `eval` adalah *language construct* sehingga tidak bisa dinonaktifkan lewat `disable_functions`; `assert` di PHP 8 tidak lagi mengeksekusi string; dua lainnya tidak membawa manfaat berarti.
- `session.*` di `php.ini` **dihapus**: Laravel memakai `config/session.php` / `.env`, bukan sesi native PHP, jadi pengaturan itu tidak berpengaruh.
- Komentar `allow_url_fopen` diperbaiki: yang mencegah Remote File Inclusion adalah `allow_url_include = Off`.
- Setelah mengubah `disable_functions`, **uji semua fitur** yang memakai export, backup, PDF, dan email.

### 1.4 Pool PHP-FPM terpisah
Jalankan aplikasi dengan pool dan user sendiri (mis. `arsikon`), bukan `www-data` bersama aplikasi lain.

---

## LAYER 2: SERVER / OS (BARU)

| Kontrol | Tindakan |
|---|---|
| Firewall | `ufw default deny incoming`; izinkan hanya 80/443 (atau tidak ada sama sekali jika memakai Cloudflare Tunnel) dan SSH dari IP tertentu |
| SSH | Login **key-only**, `PermitRootLogin no`, `PasswordAuthentication no`; pertimbangkan ganti port atau batasi IP |
| Brute-force host | Pasang **fail2ban** untuk SSH dan Nginx |
| Update OS | Aktifkan `unattended-upgrades` untuk patch keamanan |
| Kepemilikan file | Kode dimiliki user deploy (read-only bagi PHP-FPM); hanya `storage/` dan `bootstrap/cache/` yang bisa ditulis oleh user `arsikon` |
| Izin | File 644, folder 755; `.env` **640** |
| Document root | Selalu `/var/www/pt-arsikon/public`, **bukan** root proyek |
| Layanan | Matikan layanan yang tidak dipakai; `server_tokens off` di Nginx |

### Contoh Nginx (potongan inti)

```nginx
server {
    listen 443 ssl;
    http2 on;
    server_name domain-anda.com;
    root /var/www/pt-arsikon/public;
    index index.php;
    server_tokens off;
    client_max_body_size 8M;

    # Tolak file tersembunyi (.env, .git, dst), kecuali .well-known
    location ~ /\.(?!well-known) { deny all; }

    location / { try_files $uri $uri/ /index.php?$query_string; }

    # Hanya index.php yang boleh dieksekusi
    location = /index.php {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.3-fpm-arsikon.sock;
    }
    # File .php/.phtml/.phar lain (termasuk di folder upload) tidak pernah dieksekusi
    location ~ \.(php|phtml|phar)$ { return 404; }
}
```

Dengan pola ini, bahkan jika webshell berhasil terunggah di `public/storage`, file itu tidak akan dieksekusi.

---

## LAYER 3: JARINGAN, CLOUDFLARE, DAN HTTP HEADER

### 3.1 TLS
- HTTPS wajib, redirect HTTP ke HTTPS, TLS minimal 1.2.
- Jika memakai Cloudflare: mode SSL **Full (strict)**.

### 3.2 Cloudflare WAF

1. **Aktifkan Managed Rules** (Cloudflare Managed Ruleset + OWASP Core) sebagai dasar. Ini lebih andal daripada daftar string manual.
2. **Custom Rule: blokir path sensitif.** Contoh ekspresi:
   ```
   (http.request.uri.path contains "/.env") or
   (http.request.uri.path contains "/.git") or
   (http.request.uri.path contains "/phpmyadmin") or
   (http.request.uri.path contains "/wp-admin") or
   (http.request.uri.query contains "auto_prepend_file") or
   (http.request.uri.query contains "allow_url_include") or
   (http.request.uri.query contains "php://")
   ```
   Action: **Block**. (Aturan memblokir `-d` atau `../` mentah dihapus karena rawan *false positive*.)
3. **Rate limiting** untuk `/login`, `/forgot-password`, dan `/register` (jika ada).
4. **Geo-restriction:** jika bukan Indonesia → **Managed Challenge** (bukan Block penuh), agar staf yang bepergian atau memakai VPN tidak terkunci. Kecualikan IP kantor.
5. **Cloudflare Access (disarankan):** OTP email perusahaan sebelum halaman login. Catatan: endpoint API/webhook perlu *bypass policy* atau *service token*.

### 3.3 Menyembunyikan origin
- Cloudflare Tunnel: tidak ada port masuk yang terbuka.
- VPS biasa: izinkan 80/443 **hanya dari IP Cloudflare** di firewall, dan jangan bocorkan IP asli (cek DNS history/subdomain).

### 3.4 Security Headers (Middleware Laravel)

```php
// app/Http/Middleware/SecurityHeaders.php
public function handle($request, \Closure $next)
{
    $response = $next($request);
    $response->headers->add([
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'X-Content-Type-Options'    => 'nosniff',
        'Referrer-Policy'           => 'strict-origin-when-cross-origin',
        'Permissions-Policy'        => 'camera=(), microphone=(), geolocation=()',
        'Content-Security-Policy-Report-Only' =>
            "default-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'",
    ]);
    $response->headers->set('X-Frame-Options', 'DENY');
    return $response;
}
```

**Catatan penerapan:**
- Mulai dengan **CSP Report-Only**, perbaiki pelanggaran (skrip inline, Vite, CDN, font), baru ganti ke `Content-Security-Policy`. CSP yang terlalu ketat langsung merusak UI.
- **HSTS `preload` ditunda** sampai semua subdomain dipastikan HTTPS; pembatalannya sulit.

---

## LAYER 4: APLIKASI LARAVEL

### 4.1 Konfigurasi produksi (`.env`)

```env
APP_ENV=production
APP_DEBUG=false

SESSION_DRIVER=database
SESSION_LIFETIME=60          # idle timeout (menit)
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax        # strict sering mengganggu link dari email/chat
```

- `APP_KEY`: simpan di tempat aman dan **jangan masuk repo**. Jika bocor, ganti dan batalkan sesi/data terenkripsi.
- `.env` dan kredensial **tidak boleh** ada di Git. Jika pernah ter-commit, anggap bocor dan ganti semuanya.
- Deploy: `composer install --no-dev --optimize-autoloader`, lalu `php artisan config:cache route:cache view:cache`.

### 4.2 Autentikasi

**Rate limiting login per IP dan per email:**

```php
// app/Providers/AppServiceProvider.php (boot)
RateLimiter::for('login', function (Request $request) {
    $email = Str::lower((string) $request->input('email'));
    return [
        Limit::perMinutes(15, 5)->by('ip:'.$request->ip()),
        Limit::perMinutes(15, 5)->by('email:'.$email.'|'.$request->ip()),
    ];
});
// route: ->middleware('throttle:login')
```
Artinya maksimal 5 percobaan per 15 menit; setelah itu terkunci sampai jendela habis. (`throttle:5,5` bawaan tidak memberi kunci 15 menit dan tidak per email.)

**Lainnya:**
1. **Logout perangkat lain:** `Auth::logoutOtherDevices($password)` **hanya bekerja** jika middleware `Illuminate\Session\Middleware\AuthenticateSession` dipasang pada route terautentikasi.
2. **Aturan password:** `Password::min(10)->mixedCase()->numbers()->symbols()->uncompromised()`.
3. **2FA/MFA wajib untuk admin** (TOTP, mis. lewat Laravel Fortify atau paket 2FA lain). Cloudflare Access saja tidak melindungi dari akun yang sudah dicuri di level aplikasi.
4. **Reset password:** token berumur pendek, respons yang sama baik email ada maupun tidak (cegah *user enumeration*).
5. Regenerasi sesi saat login (bawaan Laravel; jangan dimatikan).

### 4.3 Otorisasi (penyebab kebocoran paling umum)

- Terapkan **Policy/Gate** di setiap aksi Controller (`$this->authorize(...)` atau `authorizeResource`). **UUID/ULID bukan pengganti otorisasi**, hanya menyulitkan penebakan.
- Gunakan role/permission yang jelas (mis. `spatie/laravel-permission`) sesuai tabel Layer 0.
- **Tulis test otorisasi:** pengguna A tidak bisa melihat/mengubah data pengguna B, dan staf tidak bisa mengakses route admin.

### 4.4 Proteksi injeksi

1. **Mass Assignment:** definisikan `$fillable` eksplisit; **jangan** masukkan `role`, `is_admin`, `status_approval`. Hindari `$guarded = []`. Gunakan `$request->validated()` dari **FormRequest**, bukan `$request->all()`.
2. **SQL Injection:** Eloquent/Query Builder dengan binding. Parameter binding **tidak melindungi nama kolom/arah urut**, jadi untuk `orderBy($request->sort)` gunakan *whitelist*. Hindari `DB::raw()` dengan input pengguna.
3. **XSS:** pakai `{{ }}` di Blade; `{!! !!}` hanya untuk konten yang sudah disanitasi (mis. HTML Purifier).
4. **CSRF:** jangan kecualikan route dari `VerifyCsrfToken` tanpa alasan kuat; webhook memakai verifikasi tanda tangan.
5. **Path traversal & redirect:** jangan pakai input pengguna langsung untuk path file atau URL redirect.
6. **Data sangat sensitif** (jika ada, mis. nomor rekening/NPWP): pakai cast `encrypted` pada model.

---

## LAYER 5: UPLOAD FILE

1. **Validasi berlapis:** ekstensi, MIME terdeteksi, dan ukuran, mis. `['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp,pdf', 'mimetypes:image/jpeg,image/png,image/webp,application/pdf']`.
2. **Nama acak:** simpan memakai `hashName()`; jangan pakai nama file dari pengguna.
3. **Re-encode gambar** (mis. Intervention Image/GD) agar payload tersembunyi di metadata hilang. **PDF tidak bisa di-re-encode** dengan cara ini; batasi ukuran, simpan privat, dan pertimbangkan pemindaian **ClamAV**.
4. **Tolak SVG** dan format yang bisa berisi skrip kecuali benar-benar dibutuhkan dan disanitasi.
5. **Private storage:** simpan di `storage/app/private`; tampilkan lewat route/controller yang memeriksa `auth` **dan Policy** (bukan hanya login), dengan header `X-Content-Type-Options: nosniff` dan `Content-Disposition` yang sesuai.
6. **Folder publik tanpa eksekusi PHP:** sudah ditangani konfigurasi Nginx di Layer 2. Hanya file yang memang boleh publik yang masuk `public/storage`.

---

## LAYER 6: DATABASE DAN BACKUP

### 6.1 Akun database terpisah

```sql
-- Akun aplikasi: hanya DML
CREATE USER 'arsikon_app'@'localhost' IDENTIFIED BY '<password-kuat>';
GRANT SELECT, INSERT, UPDATE, DELETE ON pt_arsikon.* TO 'arsikon_app'@'localhost';

-- Akun migrasi: hanya dipakai saat deploy
CREATE USER 'arsikon_migrate'@'localhost' IDENTIFIED BY '<password-kuat-lain>';
GRANT ALL PRIVILEGES ON pt_arsikon.* TO 'arsikon_migrate'@'localhost';

-- Akun backup: hak baca untuk dump
CREATE USER 'arsikon_backup'@'localhost' IDENTIFIED BY '<password-kuat-lain>';
GRANT SELECT, SHOW VIEW, TRIGGER, LOCK TABLES, EVENT ON pt_arsikon.* TO 'arsikon_backup'@'localhost';
```

> Akun aplikasi hanya DML, sehingga **migrasi tidak bisa memakainya**. Gunakan kredensial migrasi hanya saat deploy (lewat environment sementara, bukan `.env` permanen).

### 6.2 Pengerasan MySQL
- `bind-address = 127.0.0.1` (port 3306 tidak terbuka ke luar; verifikasi dengan `ss -tlnp`).
- Jalankan `mysql_secure_installation`; hapus user anonim dan database `test`.
- Jangan pakai `root` di aplikasi.

### 6.3 Backup
- Jadwalkan harian (mis. `spatie/laravel-backup`), enkripsi, dan kirim ke penyimpanan **di luar server** dengan kredensial terpisah.
- Backup dijalankan dari **CLI** (scheduler), yang tidak terkena `disable_functions` web.
- Terapkan **3-2-1**: 3 salinan, 2 media berbeda, 1 di luar lokasi.
- **Uji restore** minimal sekali sebulan ke lingkungan terpisah. Backup yang belum pernah dipulihkan belum bisa dianggap ada.
- Tentukan masa retensi (mis. 7 harian, 4 mingguan, 3 bulanan) dan lindungi akses ke storage backup (MFA).

---

## LAYER 7: AUDIT LOG, MONITORING, DAN DEPENDENSI

### 7.1 Security log
Catat di tabel `security_logs`:
- Login berhasil/gagal (IP, user agent, waktu, user ID).
- Perubahan data sensitif (nilai lama dan baru, siapa, kapan).
- Perubahan role/permission dan pembuatan akun admin.
- Respons 403/401 berulang.
- **Jangan** mencatat password, token, atau isi data sangat sensitif.

### 7.2 Perlindungan log
- Akun aplikasi **tidak boleh** mengubah/menghapus baris log: tabel ini sebaiknya hanya diberi `INSERT` untuk akun aplikasi (dan `SELECT` untuk modul laporan).
- Idealnya kirim juga ke tujuan terpisah (file log terpusat atau layanan eksternal).
- Rotasi log dan retensi (mis. 90 hari–1 tahun sesuai kebutuhan).

### 7.3 Alert
Notifikasi (email/Telegram/Slack) untuk:
- Lonjakan login gagal atau terkunci.
- Akun admin baru atau perubahan role.
- Error 500 beruntun.
- Backup gagal.
- Pantau uptime dengan layanan eksternal (mis. UptimeRobot).

### 7.4 Dependensi dan patch
- `composer audit` dan `npm audit` dijadwalkan (CI mingguan atau Dependabot/Renovate).
- Jadwalkan pembaruan rutin untuk PHP, Laravel, paket, dan OS; catat tanggalnya.

---

## LAYER 8: RESPONS INSIDEN DAN PEMULIHAN (BARU)

Siapkan satu halaman prosedur **sebelum** insiden terjadi:

1. **Deteksi & konfirmasi:** dari alert/log; catat waktu dan gejala.
2. **Isolasi:** aktifkan maintenance mode, blokir IP/akun terkait, putuskan akses jika perlu.
3. **Amankan bukti:** salin log dan snapshot sebelum membersihkan apa pun.
4. **Rotasi kredensial:** `APP_KEY`, password database, API key, token Cloudflare, kunci SSH, dan paksa logout semua sesi.
5. **Pemulihan:** pulihkan dari backup bersih atau deploy ulang dari repo; patch penyebabnya.
6. **Pelaporan & evaluasi:** beri tahu pihak terkait, tulis post-mortem, perbarui plan ini.

Cantumkan **kontak darurat** (penanggung jawab teknis, manajemen) dan lokasi kredensial cadangan.

---

## LAYER 9: PENGUJIAN DAN VERIFIKASI (BARU)

| Yang diuji | Cara | Hasil yang diharapkan |
|---|---|---|
| File sensitif | `curl -I https://domain/.env` dan `/.git/config` | 403/404 |
| Header | `curl -I https://domain` atau securityheaders.com | Header Layer 3.4 ada |
| TLS | SSL Labs | Grade A atau A+ |
| Port terbuka | `nmap` dari luar jaringan | Hanya 80/443 (atau tidak ada untuk Tunnel) |
| MySQL | `ss -tlnp \| grep 3306` | Hanya `127.0.0.1` |
| Debug | Picu error 500 | Tidak ada stack trace |
| `disable_functions` | `php -r` di CLI vs. `phpinfo` di FPM (lalu hapus) | Berbeda sesuai rancangan |
| Rate limit | 6 login salah berturut-turut | Terkunci (429) |
| Otorisasi | Test otomatis lintas-role | User A tidak bisa akses data user B |
| Upload | Coba unggah `.php`, `.phtml`, gambar polyglot | Ditolak / tidak dieksekusi |
| Dependensi | `composer audit`, `npm audit` | Tanpa temuan kritis |
| Restore | Pulihkan backup ke lingkungan uji | Aplikasi berjalan |
| Pemindaian | OWASP ZAP baseline scan (di staging) | Temuan ditinjau dan ditutup |

Untuk tingkat lanjut, lakukan **pentest** oleh pihak independen dan gunakan **OWASP ASVS Level 2** sebagai daftar periksa.

---

## CHECKLIST EKSEKUSI (URUTAN PRIORITAS)

### Prioritas 0: Persiapan
- [ ] Isi Layer 0: aset, peran, lokasi server, RPO/RTO.
- [ ] Cek apakah `.env` atau kredensial pernah masuk Git; jika ya, rotasi semuanya.

### Prioritas 1: Darurat dan infrastruktur
- [ ] Naikkan PHP ke versi yang masih disupport dan sudah di-patch; pastikan tidak memakai PHP-CGI.
- [ ] Terapkan `php.ini` FPM (Layer 1.3) dan pisahkan dari CLI; uji fitur backup/export/email.
- [ ] `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_ENCRYPT=true`, `SESSION_SAME_SITE=lax`.
- [ ] Document root ke `public/`; `.env`/`.git` tidak bisa diakses dari browser.
- [ ] Firewall, SSH key-only, fail2ban, dan unattended-upgrades aktif.
- [ ] Izin file benar (`.env` 640, hanya `storage/` dan `bootstrap/cache/` yang bisa ditulis).

### Prioritas 2: Aplikasi Laravel
- [ ] Middleware Security Headers (CSP mulai dari Report-Only).
- [ ] Rate limit login per IP dan email; pasang `AuthenticateSession`.
- [ ] 2FA untuk admin.
- [ ] Audit semua Model: `$fillable` ketat, tanpa `$guarded = []`; pakai FormRequest.
- [ ] Policy di semua Controller; tulis test otorisasi.
- [ ] Perketat upload (validasi, `hashName()`, private storage, route berpolicy).

### Prioritas 3: Database, jaringan, dan monitoring
- [ ] Buat akun DB terpisah (aplikasi, migrasi, backup); hentikan pemakaian `root`.
- [ ] MySQL hanya `127.0.0.1`.
- [ ] Cloudflare: Managed Rules, custom rule path sensitif, rate limit login, geo Managed Challenge, (opsional) Access.
- [ ] Backup terenkripsi harian ke luar server, plus **uji restore pertama**.
- [ ] Security log aktif dan terlindungi; alert dasar menyala.
- [ ] `composer audit` / `npm audit` terjadwal.

### Prioritas 4: Pemeliharaan berkelanjutan
- [ ] Tulis prosedur respons insiden (Layer 8).
- [ ] Jalankan seluruh tabel verifikasi Layer 9 dan simpan hasilnya sebagai bukti.
- [ ] Jadwalkan: patch bulanan, uji restore bulanan, tinjau akun/role per kuartal, evaluasi plan per 6 bulan.
- [ ] Pertimbangkan pentest independen.

---

## RINGKASAN PERUBAHAN DARI V1

| Bagian | v1 | v2 |
|---|---|---|
| CVE-2024-4577 | Seolah berlaku umum | Dijelaskan hanya Windows/CGI; daftar versi patch dilengkapi |
| "Celah #2" | Tanpa rujukan | Dihapus; diganti defense-in-depth |
| `disable_functions` | Menyertakan `curl_multi_exec`, `eval`, `assert`; bentrok dengan backup | Daftar dikoreksi; FPM dipisah dari CLI |
| Pengaturan `session.*` | Di `php.ini` | Dipindah ke konfigurasi Laravel |
| SameSite | Strict | Lax (lebih praktis), tetap aman |
| Login throttle | `throttle:5,5` | Per IP + email, lockout 15 menit |
| Logout perangkat lain | Hanya `logoutOtherDevices` | Plus `AuthenticateSession` |
| CSP/HSTS | Langsung aktif | Report-Only dulu; preload ditunda |
| Server/OS | Tidak ada | Layer 2 baru |
| 2FA, deployment, respons insiden, pengujian | Tidak ada | Ditambahkan |
| Backup | Jadwal saja | Plus uji restore, 3-2-1, akun khusus |
| Log | Dicatat saja | Dilindungi, ada retensi dan alert |
