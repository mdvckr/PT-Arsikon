<x-app-layout>
    <x-slot name="title">Profil Pengguna & Keamanan</x-slot>

    @push('styles')
    <style>
        .profile-page-wrapper {
            max-width: 1240px;
            margin: 0 auto;
            padding-bottom: 30px;
        }

        .profile-grid-layout {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 22px;
            align-items: start;
        }

        @media (max-width: 992px) {
            .profile-grid-layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 576px) {
            .profile-page-wrapper {
                padding-bottom: 20px;
            }
            .profile-card-body {
                padding: 14px !important;
            }
            .profile-card-header {
                padding: 12px 14px !important;
            }
            .identity-body {
                padding: 14px 14px 16px 14px !important;
            }
            .user-identity-card, .profile-card {
                border-radius: 10px !important;
            }
            .identity-name {
                font-size: 16px !important;
            }
            .identity-avatar {
                width: 76px !important;
                height: 76px !important;
                font-size: 26px !important;
            }
        }

        /* Identity Sidebar Card */
        .user-identity-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }

        .identity-card-header-bg {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            height: 90px;
            position: relative;
        }

        .identity-avatar-container {
            margin-top: -46px;
            display: flex;
            justify-content: center;
            position: relative;
        }

        .identity-avatar {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
            border: 4px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .identity-status-dot {
            position: absolute;
            bottom: 4px;
            right: calc(50% - 38px);
            width: 15px;
            height: 15px;
            background: #10b981;
            border: 2.5px solid #ffffff;
            border-radius: 50%;
        }

        .identity-body {
            padding: 14px 20px 20px 20px;
            text-align: center;
        }

        .identity-name {
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 4px 0;
        }

        .identity-email {
            font-size: 12.5px;
            color: #64748b;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .identity-roles-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
            margin-bottom: 18px;
        }

        .identity-meta-list {
            border-top: 1px solid #f1f5f9;
            padding-top: 14px;
            text-align: left;
        }

        .meta-list-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 0;
            font-size: 12px;
        }

        .meta-list-row:not(:last-child) {
            border-bottom: 1px dashed #f1f5f9;
        }

        .meta-list-label {
            color: #64748b;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .meta-list-val {
            color: #1e293b;
            font-weight: 600;
        }

        /* Warehouse list card */
        .wh-badge-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 8px;
            font-size: 12px;
            transition: all 0.2s ease;
        }

        .wh-badge-item:hover {
            border-color: #cbd5e1;
            background: #f1f5f9;
        }

        .wh-icon-box {
            width: 28px;
            height: 28px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .wh-icon-central {
            background: #eff6ff;
            color: #2563eb;
        }

        .wh-icon-project {
            background: #fff7ed;
            color: #ea580c;
        }

        .wh-type-badge {
            font-size: 9.5px;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .wh-type-central {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }

        .wh-type-project {
            background: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        /* Main Form Card */
        .profile-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 2px 6px -1px rgba(0, 0, 0, 0.04);
            margin-bottom: 20px;
            overflow: hidden;
            transition: box-shadow 0.2s ease;
        }

        .profile-card:hover {
            box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.06);
        }

        .profile-card-header {
            padding: 14px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .profile-card-title {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .profile-card-desc {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
            font-weight: 400;
        }

        .profile-card-body {
            padding: 20px;
        }

        /* Form Controls Styling */
        .input-icon-group {
            position: relative;
        }

        .input-icon-group i.input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 13px;
            pointer-events: none;
        }

        .input-icon-group .form-control {
            padding-left: 36px !important;
            padding-right: 38px !important;
            height: 38px;
            font-size: 13px;
            border-radius: 7px;
            border: 1px solid #cbd5e1;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .input-icon-group .form-control:focus {
            border-color: #ea580c;
            box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.12);
            outline: none;
        }

        .toggle-password-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px 6px;
            font-size: 13px;
            transition: color 0.15s;
        }

        .toggle-password-btn:hover {
            color: #475569;
        }

        .form-label-custom {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 5px;
        }

        .field-error-msg {
            color: #dc2626;
            font-size: 11.5px;
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* Custom Modal */
        .custom-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(3px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .custom-modal-overlay.open {
            display: flex;
        }

        .custom-modal-box {
            background: #ffffff;
            border-radius: 12px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: modalPopIn 0.2s ease-out;
        }

        @keyframes modalPopIn {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
    @endpush

    <div class="profile-page-wrapper">

        {{-- Page Breadcrumb & Header --}}
        <div class="flex items-center justify-between mb-4" style="flex-wrap:wrap;gap:12px;">
            <div>
                <div class="flex items-center gap-2 text-muted" style="font-size:12px;margin-bottom:4px;">
                    <a href="{{ route('dashboard') }}" style="color:#64748b;text-decoration:none;">Beranda</a>
                    <i class="fas fa-chevron-right" style="font-size:9px;"></i>
                    <span style="color:#ea580c;font-weight:600;">Pengaturan Profil</span>
                </div>
                <h1 style="font-size:22px;font-weight:800;color:#0f172a;margin:0;">
                    Profil Pengguna & Keamanan
                </h1>
                <p class="text-muted" style="font-size:12.5px;margin:2px 0 0;">
                    Kelola data identitas, preferensi login, serta kredensial keamanan akun Anda
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span class="badge" style="background:#ecfdf5;color:#047857;border:1px solid #a7f3d0;font-size:11.5px;padding:6px 12px;border-radius:6px;font-weight:600;">
                    <i class="fas fa-shield-check me-1"></i> Akun Terverifikasi
                </span>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if (session('status') === 'profile-updated')
        <div class="alert alert-success mb-3 flex items-center justify-between" style="border-radius:8px;padding:12px 16px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;font-size:12.5px;">
            <div class="flex items-center gap-2">
                <i class="fas fa-circle-check" style="font-size:16px;color:#10b981;"></i>
                <span>Informasi profil Anda berhasil diperbarui.</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#047857;cursor:pointer;">&times;</button>
        </div>
        @endif

        @if (session('status') === 'password-updated')
        <div class="alert alert-success mb-3 flex items-center justify-between" style="border-radius:8px;padding:12px 16px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;font-size:12.5px;">
            <div class="flex items-center gap-2">
                <i class="fas fa-key" style="font-size:16px;color:#10b981;"></i>
                <span>Kata sandi Anda berhasil diperbarui. Gunakan kata sandi baru untuk login berikutnya.</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;color:#047857;cursor:pointer;">&times;</button>
        </div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger mb-3" style="border-radius:8px;padding:12px 16px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;font-size:12.5px;">
            <div class="flex items-center gap-2 mb-1" style="font-weight:700;">
                <i class="fas fa-triangle-exclamation"></i>
                <span>Terdapat kesalahan pada input Anda:</span>
            </div>
            <ul style="margin:0;padding-left:20px;font-size:12px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="profile-grid-layout">

            {{-- ======================================================== --}}
            {{-- KOLOM KIRI: KARTU IDENTITAS USER & INFORMASI HAK AKSES     --}}
            {{-- ======================================================== --}}
            <div>
                {{-- Kartu Profil Utama --}}
                <div class="user-identity-card mb-3">
                    <div class="identity-card-header-bg"></div>

                    <div class="identity-avatar-container">
                        <div class="identity-avatar">
                            @php
                                $words = explode(' ', trim($user->name));
                                $initials = count($words) >= 2 
                                    ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                                    : strtoupper(substr($words[0], 0, 2));
                            @endphp
                            {{ $initials }}
                        </div>
                        <span class="identity-status-dot" title="Akun Aktif"></span>
                    </div>

                    <div class="identity-body">
                        <h2 class="identity-name">{{ $user->name }}</h2>
                        <div class="identity-email">
                            <i class="fas fa-envelope" style="font-size:11px;"></i>
                            <span>{{ $user->email }}</span>
                        </div>

                        {{-- Roles Badges --}}
                        <div class="identity-roles-wrap">
                            @forelse($user->roles as $role)
                                @if($role->name === 'Owner')
                                    <span class="badge" style="background:#fef3c7;color:#92400e;border:1px solid #fed7aa;font-size:11px;padding:3px 9px;border-radius:6px;font-weight:600;">
                                        <i class="fas fa-crown me-1"></i> Owner
                                    </span>
                                @elseif($role->name === 'Admin Pusat')
                                    <span class="badge" style="background:#dbeafe;color:#1e40af;border:1px solid #bfdbfe;font-size:11px;padding:3px 9px;border-radius:6px;font-weight:600;">
                                        <i class="fas fa-user-shield me-1"></i> Admin Pusat
                                    </span>
                                @elseif($role->name === 'Admin Gudang Pusat')
                                    <span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-size:11px;padding:3px 9px;border-radius:6px;font-weight:600;">
                                        <i class="fas fa-building me-1"></i> Admin Gudang Pusat
                                    </span>
                                @elseif($role->name === 'Admin Gudang Proyek')
                                    <span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-size:11px;padding:3px 9px;border-radius:6px;font-weight:600;">
                                        <i class="fas fa-helmet-safety me-1"></i> Admin Gudang Proyek
                                    </span>
                                @elseif($role->name === 'Admin PO')
                                    <span class="badge" style="background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;font-size:11px;padding:3px 9px;border-radius:6px;font-weight:600;">
                                        <i class="fas fa-truck-ramp-box me-1"></i> Admin PO
                                    </span>
                                @else
                                    <span class="badge" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;font-size:11px;padding:3px 9px;border-radius:6px;font-weight:600;">
                                        <i class="fas fa-id-badge me-1"></i> {{ $role->name }}
                                    </span>
                                @endif
                            @empty
                                <span class="badge" style="background:#f1f5f9;color:#64748b;font-size:11px;padding:3px 8px;border-radius:6px;">Pengguna Terdaftar</span>
                            @endforelse
                        </div>

                        {{-- Metadata List --}}
                        <div class="identity-meta-list">
                            <div class="meta-list-row">
                                <span class="meta-list-label">
                                    <i class="fas fa-id-badge text-primary" style="font-size:11px;"></i> ID Akun
                                </span>
                                <span class="meta-list-val">#USR-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </div>

                            <div class="meta-list-row">
                                <span class="meta-list-label">
                                    <i class="fas fa-calendar-check text-success" style="font-size:11px;"></i> Terdaftar Sejak
                                </span>
                                <span class="meta-list-val">{{ $user->created_at ? $user->created_at->format('d M Y') : '—' }}</span>
                            </div>

                            <div class="meta-list-row">
                                <span class="meta-list-label">
                                    <i class="fas fa-clock-rotate-left text-warning" style="font-size:11px;"></i> Terakhir Diubah
                                </span>
                                <span class="meta-list-val">{{ $user->updated_at ? $user->updated_at->diffForHumans() : '—' }}</span>
                            </div>

                            <div class="meta-list-row">
                                <span class="meta-list-label">
                                    <i class="fas fa-shield text-info" style="font-size:11px;"></i> Status Keamanan
                                </span>
                                <span class="badge" style="background:#ecfdf5;color:#059669;font-size:10.5px;padding:2px 6px;border-radius:4px;font-weight:600;">Normal</span>
                            </div>
                        </div>

                        <div style="margin-top:16px;padding-top:14px;border-top:1px solid #f1f5f9;">
                            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                                @csrf
                                <button type="submit" class="btn btn-danger w-full" onclick="return confirm('Apakah Anda yakin ingin keluar dari sesi akun ini?')" style="width:100%;justify-content:center;padding:9px;font-size:12.5px;font-weight:600;border-radius:8px;background:#ef4444;color:#ffffff;border:none;display:flex;align-items:center;gap:8px;cursor:pointer;box-shadow:0 2px 4px rgba(239,68,68,0.25);">
                                    <i class="fas fa-right-from-bracket fa-flip-horizontal"></i>
                                    <span>Keluar dari Akun (Logout)</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Kartu Wewenang & Penugasan Gudang --}}
                <div class="profile-card">
                    <div class="profile-card-header">
                        <div class="profile-card-title">
                            <i class="fas fa-warehouse text-primary" style="font-size:13px;"></i>
                            <span>Wewenang Gudang</span>
                        </div>
                        <span class="badge" style="background:#eff6ff;color:#2563eb;font-size:10.5px;font-weight:700;border-radius:10px;padding:2px 7px;">
                            {{ $user->hasAnyRole(['Owner', 'Super Admin']) ? 'Full' : $user->warehouses->count() }} Lokasi
                        </span>
                    </div>

                    <div class="profile-card-body" style="padding:14px 16px;">
                        @if($user->hasAnyRole(['Owner', 'Super Admin']))
                            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px;font-size:11.5px;color:#1e40af;line-height:1.45;">
                                <div class="flex items-center gap-2 mb-1" style="font-weight:700;">
                                    <i class="fas fa-crown text-warning"></i>
                                    <span>Hak Akses Menyeluruh (Global)</span>
                                </div>
                                Akun Anda memiliki izin administratif penuh untuk memantau dan mengelola seluruh Gudang Pusat serta Gudang Proyek PT Arsikon.
                            </div>
                        @else
                            @forelse($user->warehouses as $wh)
                            <div class="wh-badge-item">
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div class="wh-icon-box {{ $wh->is_central ? 'wh-icon-central' : 'wh-icon-project' }}">
                                        <i class="fas {{ $wh->is_central ? 'fa-warehouse' : 'fa-building-shield' }}"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight:600;color:#0f172a;font-size:12px;">{{ $wh->name }}</div>
                                        <div style="font-size:10.5px;color:#64748b;">Kode: {{ $wh->code }}</div>
                                    </div>
                                </div>
                                <span class="badge wh-type-badge {{ $wh->is_central ? 'wh-type-central' : 'wh-type-project' }}">
                                    {{ $wh->is_central ? 'Pusat' : 'Proyek' }}
                                </span>
                            </div>
                            @empty
                            <div class="text-center text-muted" style="padding:16px 0;font-size:12px;">
                                <i class="fas fa-circle-info mb-1" style="font-size:18px;color:#cbd5e1;display:block;"></i>
                                Belum ada penugasan gudang spesifik untuk akun ini.
                            </div>
                            @endforelse

                            <div style="margin-top:10px;font-size:11px;color:#94a3b8;line-height:1.35;">
                                <i class="fas fa-lock me-1"></i> Penugasan gudang & hak akses akun dikelola oleh <strong>Owner / Super Admin</strong>.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ======================================================== --}}
            {{-- KOLOM KANAN: FORM UPDATE PROFIL, PASSWORD, & DANGER ZONE  --}}
            {{-- ======================================================== --}}
            <div>
                {{-- Form 1: Update Profile Information --}}
                <div class="profile-card">
                    <div class="profile-card-header">
                        <div>
                            <div class="profile-card-title">
                                <i class="fas fa-user-pen text-primary" style="font-size:13px;"></i>
                                <span>Informasi Pribadi & Jabatan</span>
                            </div>
                            <div class="profile-card-desc">Informasi akun pengguna, peran / jabatan resmi di PT Arsikon, dan kontak utama</div>
                        </div>
                    </div>

                    <div class="profile-card-body">
                        <form method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            @method('patch')

                            <div class="row g-3 mb-3">
                                {{-- Nama --}}
                                <div class="col-12 col-md-6">
                                    <label class="form-label-custom" for="profile_name">
                                        Nama Lengkap <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-icon-group">
                                        <i class="fas fa-user input-icon"></i>
                                        <input type="text" id="profile_name" name="name" class="form-control" 
                                            value="{{ old('name', $user->name) }}" required autocomplete="name"
                                            placeholder="Masukkan nama lengkap...">
                                    </div>
                                    @if ($errors->get('name'))
                                        <div class="field-error-msg">
                                            <i class="fas fa-circle-exclamation"></i>
                                            <span>{{ $errors->first('name') }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Email --}}
                                <div class="col-12 col-md-6">
                                    <label class="form-label-custom" for="profile_email">
                                        Alamat Email <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-icon-group">
                                        <i class="fas fa-envelope input-icon"></i>
                                        <input type="email" id="profile_email" name="email" class="form-control" 
                                            value="{{ old('email', $user->email) }}" required autocomplete="username"
                                            placeholder="nama@arsikon.co.id">
                                    </div>
                                    @if ($errors->get('email'))
                                        <div class="field-error-msg">
                                            <i class="fas fa-circle-exclamation"></i>
                                            <span>{{ $errors->first('email') }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Jabatan / Role --}}
                                <div class="col-12">
                                    <label class="form-label-custom">
                                        Jabatan / Peran Pengguna di Sistem
                                    </label>
                                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px 14px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                                        <div class="flex items-center gap-2" style="flex-wrap:wrap;">
                                            @forelse($user->roles as $role)
                                                @if($role->name === 'Owner')
                                                    <span class="badge" style="background:#fef3c7;color:#92400e;font-size:12px;font-weight:700;padding:5px 12px;border-radius:6px;border:1px solid #fde68a;">
                                                        <i class="fas fa-crown me-1"></i> Owner / Direksi (Super Admin)
                                                    </span>
                                                @elseif($role->name === 'Admin Pusat')
                                                    <span class="badge" style="background:#dbeafe;color:#1e40af;font-size:12px;font-weight:700;padding:5px 12px;border-radius:6px;border:1px solid #bfdbfe;">
                                                        <i class="fas fa-user-shield me-1"></i> Admin Pusat (Kantor Pusat - Full Akses)
                                                    </span>
                                                @elseif($role->name === 'Admin Gudang Pusat')
                                                    <span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-size:12px;font-weight:700;padding:5px 12px;border-radius:6px;">
                                                        <i class="fas fa-building me-1"></i> Admin Gudang Pusat (Sentral Logistik)
                                                    </span>
                                                @elseif($role->name === 'Admin Gudang Proyek')
                                                    <span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;font-size:12px;font-weight:700;padding:5px 12px;border-radius:6px;">
                                                        <i class="fas fa-helmet-safety me-1"></i> Admin Gudang Proyek (Logistik Lapangan)
                                                    </span>
                                                @elseif($role->name === 'Admin PO')
                                                    <span class="badge" style="background:#ecfdf5;color:#065f46;font-size:12px;font-weight:700;padding:5px 12px;border-radius:6px;border:1px solid #a7f3d0;">
                                                        <i class="fas fa-truck-ramp-box me-1"></i> Admin Pengadaan (PO & Pembelian)
                                                    </span>
                                                @elseif($role->name === 'Karyawan')
                                                    <span class="badge" style="background:#f8fafc;color:#475569;border:1px solid #e2e8f0;font-size:12px;font-weight:700;padding:5px 12px;border-radius:6px;">
                                                        <i class="fas fa-user me-1"></i> Karyawan Lapangan / Petugas
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary" style="font-size:12px;font-weight:700;padding:5px 12px;border-radius:6px;">
                                                        <i class="fas fa-id-badge me-1"></i> {{ ucfirst($role->name) }}
                                                    </span>
                                                @endif
                                            @empty
                                                <span class="text-muted" style="font-size:12px;">Tidak ada jabatan khusus yang ditugaskan.</span>
                                            @endforelse
                                        </div>
                                        <span style="font-size:11px;color:#94a3b8;display:inline-flex;align-items:center;gap:4px;">
                                            <i class="fas fa-lock"></i> Dikelola oleh Admin Pusat
                                        </span>
                                    </div>
                                    <div style="font-size:11.5px;color:#64748b;margin-top:5px;">
                                        <i class="fas fa-info-circle me-1" style="font-size:11px;"></i> Jabatan Anda menentukan tingkat wewenang dan modul yang dapat Anda akses di dalam sistem PT Arsikon.
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2" style="border-top:1px solid #f1f5f9;">
                                <span style="font-size:11.5px;color:#94a3b8;">
                                    <i class="fas fa-shield-halved me-1"></i> Perubahan data akan langsung diterapkan ke sistem
                                </span>
                                <button type="submit" class="btn btn-primary" style="padding:7px 18px;font-size:12.5px;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
                                    <i class="fas fa-floppy-disk"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Form 2: Update Password --}}
                <div class="profile-card">
                    <div class="profile-card-header">
                        <div>
                            <div class="profile-card-title">
                                <i class="fas fa-key text-warning" style="font-size:13px;"></i>
                                <span>Ganti Kata Sandi</span>
                            </div>
                            <div class="profile-card-desc">Gunakan kombinasi kata sandi yang kuat dan unik demi keamanan data akun</div>
                        </div>
                    </div>

                    <div class="profile-card-body">
                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            @method('put')

                            {{-- Password Saat Ini --}}
                            <div class="mb-3">
                                <label class="form-label-custom" for="update_password_current_password">
                                    Kata Sandi Saat Ini <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-group">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" id="update_password_current_password" name="current_password" 
                                        class="form-control" autocomplete="current-password" placeholder="Ketik kata sandi saat ini...">
                                    <button type="button" class="toggle-password-btn" onclick="togglePassVisibility('update_password_current_password', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                @if ($errors->updatePassword->get('current_password'))
                                    <div class="field-error-msg">
                                        <i class="fas fa-circle-exclamation"></i>
                                        <span>{{ $errors->updatePassword->first('current_password') }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="row g-3 mb-3">
                                {{-- Password Baru --}}
                                <div class="col-12 col-md-6">
                                    <label class="form-label-custom" for="update_password_password">
                                        Kata Sandi Baru <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-icon-group">
                                        <i class="fas fa-shield-keyhole input-icon"></i>
                                        <input type="password" id="update_password_password" name="password" 
                                            class="form-control" autocomplete="new-password" placeholder="Minimal 8 karakter...">
                                        <button type="button" class="toggle-password-btn" onclick="togglePassVisibility('update_password_password', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @if ($errors->updatePassword->get('password'))
                                        <div class="field-error-msg">
                                            <i class="fas fa-circle-exclamation"></i>
                                            <span>{{ $errors->updatePassword->first('password') }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Konfirmasi Password Baru --}}
                                <div class="col-12 col-md-6">
                                    <label class="form-label-custom" for="update_password_password_confirmation">
                                        Ulangi Kata Sandi Baru <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-icon-group">
                                        <i class="fas fa-check-double input-icon"></i>
                                        <input type="password" id="update_password_password_confirmation" name="password_confirmation" 
                                            class="form-control" autocomplete="new-password" placeholder="Ketik ulang kata sandi baru...">
                                        <button type="button" class="toggle-password-btn" onclick="togglePassVisibility('update_password_password_confirmation', this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @if ($errors->updatePassword->get('password_confirmation'))
                                        <div class="field-error-msg">
                                            <i class="fas fa-circle-exclamation"></i>
                                            <span>{{ $errors->updatePassword->first('password_confirmation') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-2" style="border-top:1px solid #f1f5f9;">
                                <div style="font-size:11.5px;color:#64748b;display:flex;align-items:center;gap:6px;">
                                    <i class="fas fa-info-circle text-primary"></i>
                                    <span>Gunakan minimal 8 karakter kombinasi huruf dan angka.</span>
                                </div>
                                <button type="submit" class="btn btn-primary" style="padding:7px 18px;font-size:12.5px;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
                                    <i class="fas fa-shield-halved"></i> Perbarui Kata Sandi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Form 3: Danger Zone / Delete Account --}}
                <div class="profile-card" style="border-color:#fecaca;">
                    <div class="profile-card-header" style="background:#fff1f2;border-bottom-color:#fecaca;">
                        <div>
                            <div class="profile-card-title" style="color:#991b1b;">
                                <i class="fas fa-triangle-exclamation text-danger" style="font-size:13px;"></i>
                                <span>Zona Bahaya: Hapus Akun</span>
                            </div>
                            <div class="profile-card-desc" style="color:#b91c1c;">
                                Tindakan ini bersifat permanen dan tidak dapat dibatalkan setelah dikonfirmasi
                            </div>
                        </div>
                    </div>

                    <div class="profile-card-body" style="background:#ffffff;">
                        @if($user->hasRole('Owner'))
                            <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:12px;font-size:12px;color:#991b1b;display:flex;align-items:center;gap:10px;">
                                <i class="fas fa-shield-alt" style="font-size:20px;color:#dc2626;flex-shrink:0;"></i>
                                <div>
                                    <strong>Akun Utama Dilindungi:</strong> Akun berstatus <strong>Owner / Pemilik Sistem</strong> tidak dapat dihapus melalui formulir mandiri demi menjaga stabilitas data dan riwayat audit perusahaan.
                                </div>
                            </div>
                        @else
                            <div class="flex items-center justify-between" style="flex-wrap:wrap;gap:12px;">
                                <div style="max-width:480px;font-size:12px;color:#64748b;line-height:1.45;">
                                    Setelah akun Anda dihapus, semua data sesi login dan wewenang akses akan dicabut secara permanen. Harap pastikan kembali sebelum melanjutkan.
                                </div>
                                <button type="button" onclick="openDeleteAccountModal()" class="btn btn-danger" style="padding:7px 16px;font-size:12px;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
                                    <i class="fas fa-trash-can"></i> Hapus Akun Saya
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

        </div>

    </div>

    {{-- MODAL KONFIRMASI HAPUS AKUN --}}
    @if(!$user->hasRole('Owner'))
    <div id="deleteAccountModal" class="custom-modal-overlay" data-show-on-load="{{ $errors->userDeletion->isNotEmpty() ? '1' : '0' }}">
        <div class="custom-modal-box">
            <div style="padding:16px 20px;background:#fef2f2;border-bottom:1px solid #fee2e2;display:flex;align-items:center;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="width:32px;height:32px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;color:#dc2626;">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h3 style="font-size:14px;font-weight:700;color:#991b1b;margin:0;">Konfirmasi Penghapusan Akun</h3>
                        <p style="font-size:11px;color:#b91c1c;margin:0;">Tindakan ini tidak dapat dikembalikan</p>
                    </div>
                </div>
                <button type="button" onclick="closeDeleteAccountModal()" style="background:none;border:none;font-size:18px;color:#991b1b;cursor:pointer;">&times;</button>
            </div>

            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <div style="padding:20px;">
                    <p style="font-size:12.5px;color:#475569;margin-bottom:14px;line-height:1.45;">
                        Apakah Anda yakin ingin menghapus akun Anda secara permanen? Masukkan kata sandi saat ini untuk memvalidasi identitas kepemilikan akun.
                    </p>

                    <div class="mb-3">
                        <label class="form-label-custom" for="delete_modal_password">
                            Kata Sandi Anda <span class="text-danger">*</span>
                        </label>
                        <div class="input-icon-group">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="delete_modal_password" name="password" 
                                class="form-control" required placeholder="Masukkan kata sandi untuk konfirmasi...">
                            <button type="button" class="toggle-password-btn" onclick="togglePassVisibility('delete_modal_password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @if ($errors->userDeletion->get('password'))
                            <div class="field-error-msg">
                                <i class="fas fa-circle-exclamation"></i>
                                <span>{{ $errors->userDeletion->first('password') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div style="padding:14px 20px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:8px;">
                    <button type="button" onclick="closeDeleteAccountModal()" class="btn btn-secondary" style="padding:7px 14px;font-size:12px;font-weight:600;border-radius:6px;">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-danger" style="padding:7px 16px;font-size:12px;font-weight:600;border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
                        <i class="fas fa-trash-can"></i> Ya, Hapus Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        function togglePassVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function openDeleteAccountModal() {
            const modal = document.getElementById('deleteAccountModal');
            if (modal) modal.classList.add('open');
        }

        function closeDeleteAccountModal() {
            const modal = document.getElementById('deleteAccountModal');
            if (modal) modal.classList.remove('open');
        }

        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('deleteAccountModal');
            if (modal && modal.getAttribute('data-show-on-load') === '1') {
                openDeleteAccountModal();
            }
        });
    </script>
    @endpush
</x-app-layout>
