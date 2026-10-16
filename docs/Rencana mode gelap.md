# Rencana Implementasi — Mode Gelap & Terang (PT-Arsikon Warehouse / CWMS)

## 1. Keputusan arsitektur
- **Strategi**: class-based dark mode via Tailwind (`darkMode: 'class'` sudah ada di `tailwind.config.js`).
- **Penyimpanan preferensi**: `localStorage` dengan kunci `arsikon_theme` (`dark` / `light`).
- **Fallback**: bila pengguna belum pernah memilih, ikuti `prefers-color-scheme` OS.
- **Anti-flash**: script kecil di `<head>` menjalankan sebelum render, mencegah flash saat load.
- **Default awal**: mode terang (light).
- **Scope**: seluruh UI (layout utama, guest/login, komponen Blade, seluruh view) — bukan hanya layout utama saja.
- **Toggle**: tombol pemilih mode ditampilkan di topbar, untuk semua user login.
- **Mode cetak**: tetap terang (tidak dirubah).

## 2. File yang ditangani (fase implementasi)

### Fase 0 — Fondasi token tema
- `resources/css/app.css`: definisikan CSS variables di `:root` (terang) dan `.dark` (gelap) — `--theme-bg`, `--theme-surface`, `--theme-text`, `--theme-text-muted`, `--theme-border`, `--theme-primary`, `--theme-primary-dark`, `--theme-sidebar-bg`, `--theme-sidebar-text`, `--theme-topbar-bg`.
- `resources/views/layouts/app.blade.php`: blok `<style>` inline diubah agar komponen membaca variabel (`var(--theme-…)`) bukan warna hardcoded; sisakan fallback minimal.
- `resources/views/layouts/guest.blade.php`: pakai variabel yang sama untuk `.login-card`.

### Fase 1 — Dark mode di seluruh UI
- `resources/views/layouts/app.blade.php` (2.150 baris): audit seluruh selector hardcoded (body, sidebar, topbar, cards, stat-cards, tabel, tombol, badge, form-control, alert, tab, modal, notif-toast) lalu terjemahkan ke variabel/class `.dark`. Varian `.dark` untuk `.glass`/`.ios26-*` sudah ada, cukup dipelihara.
- `resources/views/layouts/guest.blade.php`: dark mode lengkap untuk halaman login/register.
- Komponen Blade (`resources/views/components/`): audit; komponen yang sudah support `dark:` Tailwind cukup dipelihara, tambahkan yang masih terang-only.
- Seluruh view di `resources/views/**`: banyak view menyimpan `<style>` inline sendiri (contoh: `dashboard.blade.php`); pakai variabel fase 0.
- Mode cetak tetap terang.

### Fase 2 — Toggle antarmuka
- `resources/js/app.js`: state tema + tombol sun/moon di topbar; class `dark` di `<html>`.
- `resources/views/layouts/app.blade.php`: tombol toggle di topbar-actions (`id="themeToggle"`, ikon `fa-moon`/`fa-sun`), skrip anti-flash di `<head>`, fungsi `toggleTheme()` yang toggle class `dark`, ganti ikon, dan simpan ke `localStorage`.
- `navigation.blade.php` (tidak terpakai) tidak disentuh.

### Fase 3 — Test, verifikasi & dokumentasi
- TDD: tulis test terlebih dahulu (RED) — tombol toggle ada, `dark` diterapkan ke `<html>`, preferensi tersimpan di `localStorage`, halaman guest tercover; lalu hijau (GREEN).
- Verifikasi visual: `npm run build` sukses; review screenshot via Playwright (sudah terpasang untuk e2e).
- Dokumentasi: catat rencana dan implementasi di `docs/` — isi placeholder `docs/Rencana mode gelap.md` serta entri baru di `docs/PERUBAHAN.md`.

## 3. Risiko & mitigasi
- Kontras/terbaca buruk jika palette tidak konsisten → pakai skema slate/zinc, hindari putih murni; periksa screenshot semua halaman.
- Preferensi disimpan hanya nilai class (`dark` / `light`), bukan data pribadi pengguna.
- Estimasi file terdampak: `app.css`, `app.blade.php`, `guest.blade.php`, `app.js`, plus banyak view — total sedang.
