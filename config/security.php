<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Anti-DDoS & HTTP Flood Protection Settings
    |--------------------------------------------------------------------------
    |
    | Konfigurasi proteksi Anti-DDoS tingkat aplikasi untuk mendeteksi lonjakan
    | trafik tidak wajar (HTTP flood), pemindaian bot berbahaya, dan karantina IP.
    |
    */
    'anti_ddos' => [
        // Mengaktifkan atau menonaktifkan proteksi DDoS di level aplikasi
        'enabled' => env('SECURITY_DDOS_ENABLED', true),

        // Lewati proteksi saat berjalan di environment testing
        'bypass_in_testing' => env('SECURITY_BYPASS_IN_TESTING', true),

        // Deteksi lonjakan mendadak (burst flood): maks request dalam rentang detik
        'burst_limit' => env('SECURITY_BURST_LIMIT', 30),
        'burst_window_seconds' => env('SECURITY_BURST_WINDOW', 10),

        // Batas umum berkelanjutan (sustained rate): maks request per menit
        'sustained_limit' => env('SECURITY_SUSTAINED_LIMIT', 300),
        'sustained_window_seconds' => env('SECURITY_SUSTAINED_WINDOW', 60),

        // Durasi blokir / karantina sementara (dalam detik) jika IP melanggar ambang batas
        'block_duration_seconds' => env('SECURITY_BLOCK_DURATION', 300), // 5 menit

        // Daftar IP yang dikecualikan dari proteksi DDoS (whitelist)
        'ip_whitelist' => array_filter(explode(',', env('SECURITY_IP_WHITELIST', '127.0.0.1,::1'))),

        // Daftar signature User-Agent bot scanner berbahaya yang langsung diblokir
        'bad_user_agents' => [
            'sqlmap',
            'nikto',
            'dirbuster',
            'nmap',
            'masscan',
            'wpscan',
            'acunetix',
            'netsparker',
            'havij',
            'zgrab',
            'gobuster',
            'hydra',
            'medusa',
        ],

        // Blokir jika request metode mutasi (POST, PUT, DELETE, PATCH) tidak memiliki User-Agent
        'block_empty_user_agent_on_post' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Defaults
    |--------------------------------------------------------------------------
    |
    | Pengaturan default batasan request per menit untuk berbagai jenis rute.
    |
    */
    'rate_limits' => [
        // Percobaan login per kombinasi IP + Email
        'login_per_minute' => env('RATE_LIMIT_LOGIN', 5),

        // Percobaan login per IP address (mencegah distributed credential stuffing)
        'login_ip_per_minute' => env('RATE_LIMIT_LOGIN_IP', 15),

        // API untuk user yang sudah login (terotentikasi)
        'api_authenticated' => env('RATE_LIMIT_API_AUTH', 120),

        // API publik atau guest
        'api_guest' => env('RATE_LIMIT_API_GUEST', 30),

        // Aksi sensitif / beban tinggi (reset password, export data, sync Supabase)
        'strict_actions' => env('RATE_LIMIT_STRICT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Security Headers
    |--------------------------------------------------------------------------
    |
    | Konfigurasi header keamanan HTTP untuk memitigasi serangan XSS, Clickjacking,
    | MIME-sniffing, dan injeksi konten berbahaya.
    |
    */
    'headers' => [
        'remove_x_powered_by' => true,
        'hsts_enabled' => env('SECURITY_HSTS_ENABLED', true),
        'hsts_max_age' => 31536000, // 1 tahun
        'hsts_include_subdomains' => true,
        'hsts_preload' => true,

        'csp' => [
            'enabled' => env('SECURITY_CSP_ENABLED', false), // dapat diaktifkan jika policy CSP sudah disesuaikan
            'report_only' => false,
            'policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src 'self' data: https:; connect-src 'self' https:;",
        ],
    ],
];
