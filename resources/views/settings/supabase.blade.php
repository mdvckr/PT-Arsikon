<x-app-layout>
    <x-slot name="title">Backup & Sinkronisasi Supabase</x-slot>

    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Backup & Sinkronisasi Supabase Cloud</h2>
            <p class="text-muted" style="font-size:13px;margin-top:2px;">
                Pencadangan dan pemulihan dua arah (Two-Way Sync) katalog Material & Alat ke database PostgreSQL Supabase
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button id="btnTestConn" type="button" class="btn btn-light border" style="font-size:13px;font-weight:600;color:#334155;height:38px;padding:0 14px;border-radius:8px;">
                <i class="fas fa-plug-circle-bolt me-1 text-primary"></i> Uji Koneksi
            </button>
        </div>
    </div>

    {{-- Status Banner --}}
    <div class="card mb-4" style="border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;background:#ffffff;">
        <div class="card-body" style="padding:20px 24px;">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <div style="width:52px;height:52px;border-radius:12px;background:{{ $stats['connection']['connected'] ? '#ecfdf5' : '#fef2f2' }};display:flex;align-items:center;justify-content:center;font-size:24px;color:{{ $stats['connection']['connected'] ? '#059669' : '#dc2626' }};">
                        <i class="fas {{ $stats['connection']['connected'] ? 'fa-cloud-circle-check' : 'fa-cloud-slash' }}"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 style="font-size:16px;font-weight:700;color:#0f172a;margin:0;">Status Server Supabase:</h4>
                            @if($stats['connection']['connected'])
                                <span class="badge" style="background:#10b981;color:#ffffff;font-size:12px;padding:4px 10px;border-radius:20px;font-weight:600;">
                                    <i class="fas fa-circle-check me-1"></i> Terhubung
                                </span>
                            @else
                                <span class="badge" style="background:#ef4444;color:#ffffff;font-size:12px;padding:4px 10px;border-radius:20px;font-weight:600;">
                                    <i class="fas fa-circle-xmark me-1"></i> {{ $config['is_configured'] ? 'Gagal Terhubung' : 'Belum Dikonfigurasi' }}
                                </span>
                            @endif
                        </div>
                        <p style="font-size:13px;color:#64748b;margin:4px 0 0 0;" id="connMessage">
                            {{ $stats['connection']['message'] }}
                            @if($stats['connection']['connected'])
                                <span class="ms-2" style="font-weight:600;color:#059669;">({{ $stats['connection']['latency_ms'] }} ms)</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-right" style="font-size:12px;color:#64748b;">
                        <div>Sinkronisasi Terakhir:</div>
                        <div style="font-weight:700;color:#1e293b;font-size:13px;" id="lastSyncedText">
                            {{ $stats['cloud']['last_synced_at'] ? \Carbon\Carbon::parse($stats['cloud']['last_synced_at'])->translatedFormat('d M Y, H:i') . ' WIB' : 'Belum pernah disinkronkan' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Komparasi Data (Local vs Cloud) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        {{-- Card Database Lokal (MySQL) --}}
        <div class="card" style="border-radius:12px;border:1px solid #e2e8f0;background:#ffffff;">
            <div class="card-body" style="padding:20px;">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div style="width:34px;height:34px;border-radius:8px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:15px;">
                            <i class="fas fa-database"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:14px;color:#0f172a;">Database Lokal (MySQL)</div>
                            <div style="font-size:11px;color:#64748b;">Operasional Harian Komputer</div>
                        </div>
                    </div>
                    <span class="badge" style="background:#e0f2fe;color:#0369a1;font-size:11px;padding:3px 8px;border-radius:6px;font-weight:600;">Aktif</span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 pt-2">
                    <div style="background:#f8fafc;padding:10px 12px;border-radius:8px;border:1px solid #f1f5f9;">
                        <div style="font-size:10px;color:#64748b;font-weight:700;text-transform:uppercase;">Kategori</div>
                        <div style="font-size:18px;font-weight:800;color:#0f172a;margin-top:2px;">
                            {{ number_format($stats['local']['categories_count']) }}
                        </div>
                        <div style="font-size:10px;color:#94a3b8;">Arsitek, MEP...</div>
                    </div>
                    <div style="background:#f8fafc;padding:10px 12px;border-radius:8px;border:1px solid #f1f5f9;">
                        <div style="font-size:10px;color:#64748b;font-weight:700;text-transform:uppercase;">Satuan</div>
                        <div style="font-size:18px;font-weight:800;color:#0f172a;margin-top:2px;">
                            {{ number_format($stats['local']['units_count']) }}
                        </div>
                        <div style="font-size:10px;color:#94a3b8;">kg, sak, btg...</div>
                    </div>
                    <div style="background:#f8fafc;padding:10px 12px;border-radius:8px;border:1px solid #f1f5f9;">
                        <div style="font-size:10px;color:#64748b;font-weight:700;text-transform:uppercase;">Material (Katalog / Stok)</div>
                        <div style="font-size:18px;font-weight:800;color:#0f172a;margin-top:2px;" id="localMatCount">
                            {{ number_format($stats['local']['materials_count']) }} <span style="font-size:12px;color:#64748b;font-weight:600;">/ {{ number_format($stats['local']['inventories_count'] ?? 0) }} rec</span>
                        </div>
                        <div style="font-size:10px;color:#94a3b8;">Item & Stok Per Gudang</div>
                    </div>
                    <div style="background:#f8fafc;padding:10px 12px;border-radius:8px;border:1px solid #f1f5f9;">
                        <div style="font-size:10px;color:#64748b;font-weight:700;text-transform:uppercase;">Alat (Katalog / Stok)</div>
                        <div style="font-size:18px;font-weight:800;color:#0f172a;margin-top:2px;" id="localToolCount">
                            {{ number_format($stats['local']['tools_count']) }} <span style="font-size:12px;color:#64748b;font-weight:600;">/ {{ number_format($stats['local']['tool_inventories_count'] ?? 0) }} rec</span>
                        </div>
                        <div style="font-size:10px;color:#94a3b8;">Unit & Stok Per Gudang</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card Database Cloud (Supabase) --}}
        <div class="card" style="border-radius:12px;border:1px solid #e2e8f0;background:#ffffff;">
            <div class="card-body" style="padding:20px;">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div style="width:34px;height:34px;border-radius:8px;background:#ecfdf5;color:#059669;display:flex;align-items:center;justify-content:center;font-size:15px;">
                            <i class="fas fa-cloud"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:14px;color:#0f172a;">Supabase Cloud (PostgreSQL)</div>
                            <div style="font-size:11px;color:#64748b;">Brankas Cadangan Online</div>
                        </div>
                    </div>
                    <span class="badge" style="background:#dcfce7;color:#15803d;font-size:11px;padding:3px 8px;border-radius:6px;font-weight:600;">Cloud Backup</span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 pt-2">
                    <div style="background:#f8fafc;padding:10px 12px;border-radius:8px;border:1px solid #f1f5f9;">
                        <div style="font-size:10px;color:#64748b;font-weight:700;text-transform:uppercase;">Kategori Cloud</div>
                        <div style="font-size:18px;font-weight:800;color:#0f172a;margin-top:2px;">
                            {{ $stats['cloud']['categories_count'] !== null ? number_format($stats['cloud']['categories_count']) : '-' }}
                        </div>
                        <div style="font-size:10px;color:#94a3b8;">Tersimpan</div>
                    </div>
                    <div style="background:#f8fafc;padding:10px 12px;border-radius:8px;border:1px solid #f1f5f9;">
                        <div style="font-size:10px;color:#64748b;font-weight:700;text-transform:uppercase;">Satuan Cloud</div>
                        <div style="font-size:18px;font-weight:800;color:#0f172a;margin-top:2px;">
                            {{ $stats['cloud']['units_count'] !== null ? number_format($stats['cloud']['units_count']) : '-' }}
                        </div>
                        <div style="font-size:10px;color:#94a3b8;">Tersimpan</div>
                    </div>
                    <div style="background:#f8fafc;padding:10px 12px;border-radius:8px;border:1px solid #f1f5f9;">
                        <div style="font-size:10px;color:#64748b;font-weight:700;text-transform:uppercase;">Material Cloud (Stok)</div>
                        <div style="font-size:18px;font-weight:800;color:#0f172a;margin-top:2px;" id="cloudMatCount">
                            {{ $stats['cloud']['materials_count'] !== null ? number_format($stats['cloud']['materials_count']) : '-' }} <span style="font-size:12px;color:#64748b;font-weight:600;">/ {{ $stats['cloud']['inventories_count'] !== null ? number_format($stats['cloud']['inventories_count']) : '-' }} rec</span>
                        </div>
                        <div style="font-size:10px;color:#94a3b8;">Katalog & Stok Gudang</div>
                    </div>
                    <div style="background:#f8fafc;padding:10px 12px;border-radius:8px;border:1px solid #f1f5f9;">
                        <div style="font-size:10px;color:#64748b;font-weight:700;text-transform:uppercase;">Alat Cloud (Stok)</div>
                        <div style="font-size:18px;font-weight:800;color:#0f172a;margin-top:2px;" id="cloudToolCount">
                            {{ $stats['cloud']['tools_count'] !== null ? number_format($stats['cloud']['tools_count']) : '-' }} <span style="font-size:12px;color:#64748b;font-weight:600;">/ {{ $stats['cloud']['tool_inventories_count'] !== null ? number_format($stats['cloud']['tool_inventories_count']) : '-' }} rec</span>
                        </div>
                        <div style="font-size:10px;color:#94a3b8;">Unit & Stok Gudang</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tombol Tindakan Sinkronisasi (Action Cards) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        {{-- Card PUSH --}}
        <div class="card" style="border-radius:12px;border:1px solid #e2e8f0;background:#ffffff;">
            <div class="card-body" style="padding:22px;">
                <div class="flex items-start gap-3 mb-3">
                    <div style="width:40px;height:40px;border-radius:10px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
                        <i class="fas fa-cloud-arrow-up"></i>
                    </div>
                    <div>
                        <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin:0;">Cadangkan ke Supabase (PUSH)</h3>
                        <p style="font-size:12.5px;color:#64748b;margin:4px 0 0 0;line-height:1.4;">
                            Menyalin dan memperbarui seluruh data Material dan Alat dari komputer lokal ke database Supabase Cloud.
                        </p>
                    </div>
                </div>

                <div style="background:#f8fafc;padding:10px 14px;border-radius:8px;font-size:12px;color:#475569;margin-bottom:18px;">
                    <i class="fas fa-shield-halved text-success me-1"></i> Data di cloud akan di-upsert (data baru ditambahkan, data yang ada diperbarui tanpa duplikasi).
                </div>

                <button id="btnPush" type="button" class="btn btn-primary w-100" style="height:42px;font-weight:600;font-size:13.5px;border-radius:8px;">
                    <i class="fas fa-cloud-arrow-up me-2"></i> Jalankan PUSH (Cadangkan Sekarang)
                </button>
            </div>
        </div>

        {{-- Card PULL --}}
        <div class="card" style="border-radius:12px;border:1px solid #e2e8f0;background:#ffffff;">
            <div class="card-body" style="padding:22px;">
                <div class="flex items-start gap-3 mb-3">
                    <div style="width:40px;height:40px;border-radius:10px;background:#f0fdf4;color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
                        <i class="fas fa-cloud-arrow-down"></i>
                    </div>
                    <div>
                        <h3 style="font-size:16px;font-weight:700;color:#0f172a;margin:0;">Tarik ke Database Lokal (PULL / RESTORE)</h3>
                        <p style="font-size:12.5px;color:#64748b;margin:4px 0 0 0;line-height:1.4;">
                            Menarik kembali seluruh katalog Material dan Alat dari Supabase Cloud untuk dimasukkan ke database lokal komputer Anda.
                        </p>
                    </div>
                </div>

                <div style="background:#fefce8;padding:10px 14px;border-radius:8px;font-size:12px;color:#854d0e;margin-bottom:18px;border:1px solid #fef08a;">
                    <i class="fas fa-lightbulb text-warning me-1"></i> Sangat cocok dijalankan setelah Anda melakukan <code>php artisan migrate:fresh</code> agar data manual kembali.
                </div>

                <button id="btnPull" type="button" class="btn btn-success w-100" style="height:42px;font-weight:600;font-size:13.5px;border-radius:8px;background:#16a34a;border-color:#16a34a;color:#ffffff;">
                    <i class="fas fa-cloud-arrow-down me-2"></i> Jalankan PULL (Tarik Data ke Lokal)
                </button>
            </div>
        </div>
    </div>

    {{-- Panduan Konfigurasi Supabase (.env) --}}
    <div class="card" style="border-radius:12px;border:1px solid #e2e8f0;background:#ffffff;">
        <div class="card-body" style="padding:20px 24px;">
            <div class="flex items-center justify-between cursor-pointer" onclick="toggleGuide()">
                <div class="flex items-center gap-2">
                    <i class="fas fa-circle-info text-primary"></i>
                    <span style="font-weight:700;font-size:14px;color:#0f172a;">Panduan Menghubungkan Supabase (.env)</span>
                </div>
                <i class="fas fa-chevron-down text-muted" id="guideArrow"></i>
            </div>

            <div id="guideContent" style="display:none;margin-top:16px;border-top:1px solid #f1f5f9;padding-top:14px;">
                <p style="font-size:13px;color:#475569;line-height:1.5;">
                    Untuk menghubungkan aplikasi dengan project Supabase Anda, buka file <code>.env</code> di root folder proyek dan sesuaikan baris berikut:
                </p>
                <pre style="background:#0f172a;color:#f8fafc;padding:14px 18px;border-radius:8px;font-size:12.5px;overflow-x:auto;margin:12px 0;"><code># Supabase Cloud Backup Connection
SUPABASE_DB_HOST=db.xxxxxxxxxxxxxxxxxxxx.supabase.co
SUPABASE_DB_PORT=5432
SUPABASE_DB_DATABASE=postgres
SUPABASE_DB_USERNAME=postgres
SUPABASE_DB_PASSWORD=password_database_anda
SUPABASE_DB_SSLMODE=prefer</code></pre>
                
                <div style="font-size:12.5px;color:#64748b;line-height:1.5;">
                    <span style="font-weight:700;color:#0f172a;">Di mana menemukan data di atas pada dashboard Supabase?</span><br>
                    Buka project Anda di <strong>supabase.com</strong> &gt; Klik menu <strong>Project Settings (ikon gir)</strong> &gt; Pilih <strong>Database</strong> &gt; Cari bagian <strong>Connection Parameters</strong>.
                </div>
            </div>
        </div>
    </div>

    {{-- Script Interaktif --}}
    <script>
        function toggleGuide() {
            const content = document.getElementById('guideContent');
            const arrow = document.getElementById('guideArrow');
            if (content.style.display === 'none') {
                content.style.display = 'block';
                arrow.classList.remove('fa-chevron-down');
                arrow.classList.add('fa-chevron-up');
            } else {
                content.style.display = 'none';
                arrow.classList.remove('fa-chevron-up');
                arrow.classList.add('fa-chevron-down');
            }
        }

        document.getElementById('btnTestConn').addEventListener('click', async function() {
            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menguji...';

            try {
                const res = await fetch("{{ route('supabase-sync.test') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();

                if (data.connected) {
                    if (window.showNotificationToast) {
                        window.showNotificationToast({
                            title: 'Koneksi Berhasil',
                            message: `${data.message} (${data.latency_ms} ms)`,
                            sound_type: 'success',
                            type: 'success'
                        });
                    }
                    if (window.playNotificationChime) window.playNotificationChime('success');
                } else {
                    if (window.showNotificationToast) {
                        window.showNotificationToast({
                            title: 'Koneksi Gagal',
                            message: data.message,
                            sound_type: 'warning',
                            type: 'warning'
                        });
                    }
                }
                setTimeout(() => window.location.reload(), 1500);
            } catch (err) {
                alert('Gagal menguji koneksi: ' + err.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });

        document.getElementById('btnPush').addEventListener('click', async function() {
            if (!confirm('Apakah Anda yakin ingin mencadangkan (PUSH) data Material & Alat lokal ke Supabase Cloud?')) {
                return;
            }

            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sedang mencadangkan...';

            try {
                const res = await fetch("{{ route('supabase-sync.push') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();

                if (data.success) {
                    if (window.showNotificationToast) {
                        window.showNotificationToast({
                            title: 'Pencadangan Berhasil!',
                            message: data.message,
                            sound_type: 'success',
                            type: 'success'
                        });
                    }
                    if (window.playNotificationChime) window.playNotificationChime('success');
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    alert('Gagal PUSH: ' + (data.message || 'Terjadi kesalahan'));
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan: ' + err.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });

        document.getElementById('btnPull').addEventListener('click', async function() {
            if (!confirm('Data katalog lokal akan disinkronkan dan diperbarui dari Supabase Cloud. Lanjutkan PULL?')) {
                return;
            }

            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Sedang menarik data dari cloud...';

            try {
                const res = await fetch("{{ route('supabase-sync.pull') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();

                if (data.success) {
                    if (window.showNotificationToast) {
                        window.showNotificationToast({
                            title: 'Pemulihan Berhasil!',
                            message: data.message,
                            sound_type: 'success',
                            type: 'success'
                        });
                    }
                    if (window.playNotificationChime) window.playNotificationChime('success');
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    alert('Gagal PULL: ' + (data.message || 'Terjadi kesalahan'));
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan: ' + err.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    </script>
</x-app-layout>
