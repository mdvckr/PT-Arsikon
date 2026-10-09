# Panduan Lengkap Proteksi & Penanganan Serangan DDoS — PT Arsikon

Dokumen ini menjelaskan strategi pertahanan berlapis (**Defense-in-Depth**) untuk melindungi aplikasi PT Arsikon dari serangan DDoS (*Distributed Denial of Service*) dan cara menangani serangan jika sedang terjadi.

---

## 1. Arsitektur Pertahanan Berlapis (5 Lapis)

Sebuah server VPS biasa tidak akan sanggup menahan DDoS skala besar (puluhan Gbps) jika diserang langsung ke IP VPS. Karena itu, pertahanan harus disusun bertingkat:

```
[ INTERNET / TRAFFIC ]
         │
         ▼
[ Lapis 1: Cloudflare Edge ] ────► Menyerap Volumetric DDoS (SYN Flood, UDP, Gbps+)
         │                         WAF, Bot Fight Mode, Under Attack Mode
         ▼
[ Lapis 2: Firewall & Kernel ] ──► UFW + IPTables (drop packet scanner, limit TCP NEW)
         │                         Linux Sysctl (SYN cookies, anti-spoofing)
         ▼
[ Lapis 3: Nginx Web Server ] ───► Rate Limiting (10 req/s general, 5 req/min login)
         │                          Anti-Slowloris timeouts, Block bad User-Agents
         ▼
[ Lapis 4: Fail2Ban ] ───────────► Auto-ban IP mencurigakan selama 1-24 jam di firewall
         │
         ▼
[ Lapis 5: Laravel App ] ────────► TrustProxies (Real IP) + DdosProtection Middleware
```

---

## 2. Rincian Konfigurasi yang Sudah Disiapkan di Project

Semua file konfigurasi telah dibuat di folder [deploy/](file:///d:/XAMPP/htdocs/PT-Arsikon/deploy):

| Komponen | File / Lokasi | Fungsi Utama |
|---|---|---|
| **Firewall & Kernel** | [firewall-setup.sh](file:///d:/XAMPP/htdocs/PT-Arsikon/deploy/firewall-setup.sh) | UFW allow port 22/80/443, IPTables limit connection rate, Sysctl SYN cookies |
| **Nginx Rate Limits** | [arsikon-ddos-protection.conf](file:///d:/XAMPP/htdocs/PT-Arsikon/deploy/arsikon-ddos-protection.conf) | Zone `general` (10r/s), `login` (5r/m), `api` (30r/s), `limit_conn` (20/IP) |
| **Nginx Hardening** | [arsikon-nginx.conf](file:///d:/XAMPP/htdocs/PT-Arsikon/deploy/arsikon-nginx.conf) | Anti-Slowloris timeouts, blokir bot/scanner (sqlmap, nikto, scrapers), block path rahasia |
| **Fail2Ban Jails** | Dikelola di `firewall-setup.sh` | Auto-ban IP yang memicu HTTP 429 atau gagal login 5x |
| **Laravel Proxy Trust** | [TrustProxies.php](file:///d:/XAMPP/htdocs/PT-Arsikon/app/Http/Middleware/TrustProxies.php) | Membaca IP asli dari Cloudflare header (`CF-Connecting-IP` / `X-Forwarded-For`) |
| **Laravel Middleware** | [DdosProtection.php](file:///d:/XAMPP/htdocs/PT-Arsikon/app/Http/Middleware/DdosProtection.php) | Batas 120 req/menit per IP di level aplikasi + auto ban cooldown 15 menit |

---

## 3. Langkah Kunci: Setup Cloudflare (Perisai Utama Terpenting)

**Gratis dan wajib dipasang** agar IP asli VPS tidak diketahui penyerang:

1. Buat akun di [cloudflare.com](https://dash.cloudflare.com/) (Free Plan).
2. Tambahkan domain `arsikon.co.id`.
3. Ganti NameServer domain di registrar (Niagahoster/Rumahweb/IDCloudHost) ke NameServer Cloudflare.
4. Di menu **DNS**:
   - `A` `arsikon.co.id` ➔ `<IP_VPS>` (Status: **Proxied / Orange Cloud ☁️**)
   - `CNAME` `www` ➔ `arsikon.co.id` (Status: **Proxied / Orange Cloud ☁️**)
5. Di menu **Security > WAF**:
   - Aktifkan **Bot Fight Mode**.
   - (Opsional) Buat Firewall Rule: Jika traffic berasal dari luar Indonesia dan ingin dibatasi, beri tantangan **Managed Challenge (Captcha)**.
6. **PENTING: Sembunyikan IP Asli Server**:
   - Jangan pernah gunakan IP server langsung di record DNS publik (misal: mail server atau sub-domain tanpa proxy).
   - Pastikan port SSH (22) hanya diakses via SSH Key dan port 80/443 diproxy.

---

## 4. SOP Darurat: Apa yang Harus Dilakukan Saat Diserang DDoS?

Jika website mendadak sangat lambat atau error 502/504 Bad Gateway:

### Langkah 1: Aktifkan "Under Attack Mode" di Cloudflare (Solusi Tercepat: 10 Detik)
1. Buka dashboard Cloudflare: `arsikon.co.id`.
2. Pada menu **Overview** atau **Security**, aktifkan toggle **"Under Attack Mode"**.
3. **Efeknya**: Setiap pengunjung akan diberikan tantangan Javascript (5 detik pengecekan) sebelum diteruskan ke server Anda. Jutaan request bot/DDoS akan langsung terblokir di server Cloudflare, dan beban server Anda akan langsung turun drastis ke 0%.

### Langkah 2: Cek Kondisi Server via SSH
Jalankan perintah ini di VPS:
```bash
# 1. Cek beban CPU dan RAM
htop
# atau: uptime

# 2. Cek koneksi aktif terbanyak per IP
netstat -ntu | awk '{print $5}' | cut -d: -f1 | sort | uniq -c | sort -n

# 3. Cek status Nginx dan PHP-FPM
systemctl status nginx
systemctl status php8.1-fpm
```

### Langkah 3: Periksa Log Nginx & Fail2Ban
```bash
# Pantau log request Nginx secara real-time
tail -f /var/log/nginx/arsikon-access.log

# Cek IP yang paling banyak mengirim request
awk '{print $1}' /var/log/nginx/arsikon-access.log | sort | uniq -c | sort -nr | head -n 20

# Cek status IP yang sudah di-ban Fail2Ban
fail2ban-client status nginx-limit-req
fail2ban-client status arsikon-login
```

### Langkah 4: Blokir IP Penyerang Secara Manual (Jika diperlukan)
Jika ada IP tertentu yang lolos dan membombardir server:
```bash
# Blokir via UFW:
sudo ufw insert 1 deny from 203.0.113.5 to any

# Atau unban jika salah blokir teman/kantor:
sudo ufw delete deny from 203.0.113.5
sudo fail2ban-client set nginx-limit-req unbanip 203.0.113.5
```

---

## 5. Cara Eksekusi Script Proteksi di VPS Baru

Saat setup server baru, cukup jalankan urutan ini:

```bash
# 1. Setup server dasar (PHP, Nginx, MySQL, Redis, Supervisor)
sudo bash /var/www/arsikon/deploy/server-setup.sh

# 2. Setup Firewall, Fail2Ban, Sysctl, IPTables
sudo bash /var/www/arsikon/deploy/firewall-setup.sh

# 3. Copy konfigurasi DDoS Nginx
sudo cp /var/www/arsikon/deploy/arsikon-ddos-protection.conf /etc/nginx/conf.d/
sudo cp /var/www/arsikon/deploy/arsikon-nginx.conf /etc/nginx/sites-available/arsikon
sudo ln -sf /etc/nginx/sites-available/arsikon /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl restart nginx
```
