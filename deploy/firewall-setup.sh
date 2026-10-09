#!/bin/bash
# ==============================================================================
# PT Arsikon — Firewall & Anti-DDoS Setup (UFW + Fail2Ban)
# ==============================================================================
# Jalankan sebagai root: sudo bash firewall-setup.sh
# ==============================================================================

set -euo pipefail

echo "╔══════════════════════════════════════════════════════════════╗"
echo "║  PT Arsikon — Firewall & Anti-DDoS Setup                    ║"
echo "╚══════════════════════════════════════════════════════════════╝"

# ==========================================================================
# 1. UFW (Uncomplicated Firewall)
# ==========================================================================
echo ""
echo "▸ [1/4] Mengkonfigurasi UFW Firewall..."

apt install -y ufw

# Reset rules
ufw --force reset

# Default policy: block semua incoming, izinkan outgoing
ufw default deny incoming
ufw default allow outgoing

# Izinkan SSH (PENTING: jangan sampai terkunci!)
ufw allow 22/tcp comment 'SSH'

# Izinkan HTTP & HTTPS
ufw allow 80/tcp comment 'HTTP'
ufw allow 443/tcp comment 'HTTPS'

# (Opsional) Izinkan MySQL hanya dari localhost
# ufw allow from 127.0.0.1 to any port 3306 comment 'MySQL Local'

# Aktifkan UFW
ufw --force enable
ufw status verbose

echo "  ✅ UFW Firewall aktif."

# ==========================================================================
# 2. Fail2Ban (Auto-ban IP yang mencurigakan)
# ==========================================================================
echo ""
echo "▸ [2/4] Menginstall & mengkonfigurasi Fail2Ban..."

apt install -y fail2ban

# Buat konfigurasi lokal (agar tidak tertimpa update)
cat > /etc/fail2ban/jail.local << 'FAIL2BAN_CONFIG'
[DEFAULT]
# Ban selama 1 jam
bantime = 3600
# Window waktu pengecekan: 10 menit
findtime = 600
# Maksimal 5 percobaan gagal
maxretry = 5
# Kirim notifikasi (opsional, butuh sendmail)
# destemail = admin@arsikon.co.id
# action = %(action_mwl)s

# =============================================
# SSH Protection
# =============================================
[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3
bantime = 7200

# =============================================
# Nginx Protection — HTTP Auth
# =============================================
[nginx-http-auth]
enabled = true
filter = nginx-http-auth
logpath = /var/log/nginx/arsikon-error.log
maxretry = 5
bantime = 3600

# =============================================
# Nginx Protection — Rate Limit (429 errors)
# =============================================
[nginx-limit-req]
enabled = true
filter = nginx-limit-req
logpath = /var/log/nginx/arsikon-error.log
maxretry = 10
findtime = 120
bantime = 7200

# =============================================
# Nginx Protection — Bot/Scanner Detection
# =============================================
[nginx-botsearch]
enabled = true
filter = nginx-botsearch
logpath = /var/log/nginx/arsikon-access.log
maxretry = 5
findtime = 120
bantime = 86400

# =============================================
# Laravel Login Brute-Force (custom filter)
# =============================================
[arsikon-login]
enabled = true
filter = arsikon-login
logpath = /var/log/nginx/arsikon-access.log
maxretry = 5
findtime = 300
bantime = 3600
FAIL2BAN_CONFIG

# Buat custom filter untuk Laravel login attempts
cat > /etc/fail2ban/filter.d/arsikon-login.conf << 'FILTER_CONFIG'
[Definition]
failregex = ^<HOST> -.*"POST /login.*" (401|422|429)
ignoreregex =
FILTER_CONFIG

# Restart Fail2Ban
systemctl enable fail2ban
systemctl restart fail2ban

echo "  ✅ Fail2Ban aktif dengan proteksi SSH, Nginx, dan Laravel login."

# ==========================================================================
# 3. Kernel-Level DDoS Protection (sysctl)
# ==========================================================================
echo ""
echo "▸ [3/4] Mengkonfigurasi kernel-level network protection..."

cat > /etc/sysctl.d/99-arsikon-ddos.conf << 'SYSCTL_CONFIG'
# ==============================================================================
# PT Arsikon — Kernel Network Hardening & Anti-DDoS
# ==============================================================================

# --- SYN Flood Protection ---
net.ipv4.tcp_syncookies = 1
net.ipv4.tcp_max_syn_backlog = 4096
net.ipv4.tcp_synack_retries = 2
net.ipv4.tcp_syn_retries = 2

# --- Spoofing Protection ---
net.ipv4.conf.all.rp_filter = 1
net.ipv4.conf.default.rp_filter = 1

# --- ICMP Protection (anti ping flood) ---
net.ipv4.icmp_echo_ignore_broadcasts = 1
net.ipv4.icmp_ignore_bogus_error_responses = 1
# Batasi ICMP rate
net.ipv4.icmp_ratelimit = 100

# --- Disable Source Routing ---
net.ipv4.conf.all.accept_source_route = 0
net.ipv4.conf.default.accept_source_route = 0

# --- Disable Redirect Acceptance ---
net.ipv4.conf.all.accept_redirects = 0
net.ipv4.conf.default.accept_redirects = 0
net.ipv4.conf.all.send_redirects = 0

# --- Connection Tracking Limits ---
net.netfilter.nf_conntrack_max = 200000
net.netfilter.nf_conntrack_tcp_timeout_established = 600

# --- TCP Optimization ---
net.ipv4.tcp_fin_timeout = 15
net.ipv4.tcp_keepalive_time = 300
net.ipv4.tcp_keepalive_probes = 3
net.ipv4.tcp_keepalive_intvl = 15
net.core.somaxconn = 4096
net.ipv4.tcp_max_tw_buckets = 1440000
SYSCTL_CONFIG

sysctl --system > /dev/null 2>&1

echo "  ✅ Kernel network hardening aktif."

# ==========================================================================
# 4. IPTables Rate Limiting (lapisan tambahan)
# ==========================================================================
echo ""
echo "▸ [4/4] Menambahkan iptables rate limiting rules..."

# Limit new TCP connections per IP (60/menit)
iptables -A INPUT -p tcp --dport 80 -m conntrack --ctstate NEW -m limit --limit 60/minute --limit-burst 20 -j ACCEPT
iptables -A INPUT -p tcp --dport 443 -m conntrack --ctstate NEW -m limit --limit 60/minute --limit-burst 20 -j ACCEPT

# Limit ICMP (ping) to 1/second
iptables -A INPUT -p icmp --icmp-type echo-request -m limit --limit 1/s --limit-burst 4 -j ACCEPT
iptables -A INPUT -p icmp --icmp-type echo-request -j DROP

# Drop invalid packets
iptables -A INPUT -m conntrack --ctstate INVALID -j DROP

# Drop XMAS and NULL packets (port scanning)
iptables -A INPUT -p tcp --tcp-flags ALL ALL -j DROP
iptables -A INPUT -p tcp --tcp-flags ALL NONE -j DROP

# Simpan rules agar persist setelah reboot
apt install -y iptables-persistent
netfilter-persistent save

echo "  ✅ IPTables rate limiting aktif."

echo ""
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║  ✅ Firewall & Anti-DDoS setup selesai!                     ║"
echo "╠══════════════════════════════════════════════════════════════╣"
echo "║  Cek status:                                                ║"
echo "║    ufw status                                               ║"
echo "║    fail2ban-client status                                   ║"
echo "║    iptables -L -n                                           ║"
echo "╚══════════════════════════════════════════════════════════════╝"
