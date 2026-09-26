<x-app-layout>
    <x-slot name="title">Tambah Pengguna Baru</x-slot>

    <div class="user-edit-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb mb-3">
            <a href="{{ route('users.index') }}"><i class="fas fa-users" style="font-size:12px;"></i> Data Pengguna</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
            <span style="color:#1e293b;font-weight:600;">Tambah Baru</span>
        </div>

        <!-- Form Card -->
        <div class="card shadow-sm user-form-card">
            <div class="card-header user-form-header">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <div class="icon-circle icon-circle-primary">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div>
                            <span class="card-title" style="font-size:16px;">Form Tambah Pengguna Baru</span>
                            <p class="text-muted" style="font-size:12px;margin:2px 0 0 0;">Lengkapi data diri, kredensial login, dan tentukan role hak akses akun.</p>
                        </div>
                    </div>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="card-body user-form-body">
                @if (isset($errors) && $errors->any())
                    <div class="alert alert-danger mb-4" style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:14px;border-radius:10px;">
                        <strong style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                            <i class="fas fa-triangle-exclamation"></i> Terdapat kesalahan pada pengisian form:
                        </strong>
                        <ul style="margin:0;padding-left:22px;font-size:13px;line-height:1.5;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('users.store') }}">
                    @csrf

                    <!-- SECTION 1: IDENTITAS UTAMA -->
                    <div class="form-section mb-4">
                        <div class="section-title-wrap">
                            <i class="fas fa-user-circle text-primary"></i>
                            <h3 class="section-title">Informasi Akun</h3>
                        </div>

                        <div class="grid grid-2 responsive-grid">
                            <div class="mb-3">
                                <label class="form-label" for="userName">
                                    Nama Lengkap <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" id="userName" name="name" value="{{ old('name') }}" class="form-control with-icon" placeholder="Contoh: Budi Santoso" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="userEmail">
                                    Alamat Email <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-envelope input-icon"></i>
                                    <input type="email" id="userEmail" name="email" value="{{ old('email') }}" class="form-control with-icon" placeholder="nama@arsikon.co.id" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-divider"></div>

                    <!-- SECTION 2: KEAMANAN & PASSWORD -->
                    <div class="form-section mb-4">
                        <div class="section-title-wrap">
                            <i class="fas fa-lock text-warning"></i>
                            <h3 class="section-title">Keamanan & Password Akun</h3>
                        </div>

                        <div class="grid grid-2 responsive-grid">
                            <div class="mb-3">
                                <label class="form-label" for="userPassword">
                                    Password Login <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-key input-icon"></i>
                                    <input type="password" id="userPassword" name="password" class="form-control with-icon" required minlength="8" placeholder="Minimal 8 karakter">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="userPasswordConfirm">
                                    Konfirmasi Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-check-double input-icon"></i>
                                    <input type="password" id="userPasswordConfirm" name="password_confirmation" class="form-control with-icon" required minlength="8" placeholder="Ulangi password di atas">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-divider"></div>

                    <!-- SECTION 3: ROLE & JABATAN -->
                    <div class="form-section mb-4">
                        <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
                            <div class="section-title-wrap mb-0">
                                <i class="fas fa-shield-halved text-purple"></i>
                                <h3 class="section-title">Role / Jabatan Pengguna <span class="text-danger">*</span></h3>
                            </div>
                            @if(auth()->user()->hasRole('Admin Pusat') || auth()->user()->hasRole('Owner') || auth()->user()->hasRole('Admin'))
                            <a href="{{ route('roles.index') }}" target="_blank" class="role-manage-link" title="Buka kelola jabatan di tab baru">
                                <i class="fas fa-sliders"></i> Kelola Jabatan
                            </a>
                            @endif
                        </div>
                        <p class="text-muted" style="font-size:12.5px;margin-bottom:12px;">Tentukan satu atau beberapa peran jabatan untuk akun ini.</p>

                        <div class="roles-grid">
                            @foreach($roles as $role)
                            @php
                                $roleConfig = match($role->name) {
                                    'Owner' => ['icon' => 'fa-crown', 'color' => '#7c3aed', 'bg' => 'rgba(124,58,237,0.1)', 'desc' => 'Pemilik Sistem (Akses penuh tanpa batas ke seluruh modul)'],
                                    'Admin Pusat' => ['icon' => 'fa-building-shield', 'color' => '#d97706', 'bg' => 'rgba(217,119,6,0.1)', 'desc' => 'Kantor Pusat (Full kontrol user, gudang, jabatan, master data & logistik)'],
                                    'Admin Gudang Pusat' => ['icon' => 'fa-warehouse', 'color' => '#2563eb', 'bg' => 'rgba(37,99,235,0.1)', 'desc' => 'Sentral Logistik Jakarta (Kelola material, alat, stok gudang pusat, distribusi)'],
                                    'Admin Gudang Proyek' => ['icon' => 'fa-helmet-safety', 'color' => '#0891b2', 'bg' => 'rgba(8,145,178,0.1)', 'desc' => 'Site Proyek (Penerimaan kiriman, stok site, peminjaman alat & pemakaian)'],
                                    'Admin PO' => ['icon' => 'fa-file-invoice-dollar', 'color' => '#059669', 'bg' => 'rgba(5,150,105,0.1)', 'desc' => 'Divisi Pengadaan & Purchase Order (Pemasok, harga, permohonan beli)'],
                                    'Karyawan' => ['icon' => 'fa-user-check', 'color' => '#475569', 'bg' => 'rgba(71,85,105,0.1)', 'desc' => 'Staff Operasional (Mengajukan peminjaman alat & permintaan material)'],
                                    default => ['icon' => 'fa-id-card', 'color' => '#64748b', 'bg' => 'rgba(100,116,139,0.1)', 'desc' => 'Hak akses jabatan khusus'],
                                };
                                $isChecked = in_array($role->name, old('roles', []));
                            @endphp
                            <label class="role-card-item {{ $isChecked ? 'active' : '' }}">
                                <div class="role-card-checkbox">
                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                                        {{ $isChecked ? 'checked' : '' }} onchange="this.closest('.role-card-item').classList.toggle('active', this.checked)">
                                </div>
                                <div class="role-card-icon" style="background:{{ $roleConfig['bg'] }};color:{{ $roleConfig['color'] }};">
                                    <i class="fas {{ $roleConfig['icon'] }}"></i>
                                </div>
                                <div class="role-card-details">
                                    <div class="role-card-name">{{ $role->name }}</div>
                                    <div class="role-card-desc">{{ $roleConfig['desc'] }}</div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-divider"></div>

                    <!-- SECTION 4: PENUGASAN GUDANG AKSES -->
                    <div class="form-section mb-4">
                        <div class="section-title-wrap">
                            <i class="fas fa-boxes-stacked text-info"></i>
                            <h3 class="section-title">Penugasan Akses Gudang</h3>
                        </div>
                        <p class="text-muted" style="font-size:12.5px;margin-bottom:12px;">Pilih lokasi gudang yang diizinkan untuk dikelola atau diakses oleh pengguna ini.</p>

                        <div class="warehouses-selection-grid">
                            @forelse($warehouses as $wh)
                            @php
                                $isWhChecked = in_array($wh->id, old('warehouse_ids', []));
                                $isCentral = $wh->is_central || in_array($wh->type, ['central', 'main', 'pusat']);
                            @endphp
                            <label class="wh-select-card {{ $isWhChecked ? 'active' : '' }}">
                                <input type="checkbox" name="warehouse_ids[]" value="{{ $wh->id }}"
                                    {{ $isWhChecked ? 'checked' : '' }} onchange="this.closest('.wh-select-card').classList.toggle('active', this.checked)">
                                <div class="wh-select-body">
                                    <div class="wh-select-header">
                                        <span class="wh-name">{{ $wh->name }}</span>
                                        @if($isCentral)
                                            <span class="badge badge-primary wh-badge"><i class="fas fa-building"></i> Pusat</span>
                                        @else
                                            <span class="badge badge-info wh-badge"><i class="fas fa-person-digging"></i> Proyek</span>
                                        @endif
                                    </div>
                                    <div class="wh-location text-muted">
                                        <i class="fas fa-location-dot" style="font-size:11px;"></i> {{ $wh->address ?: 'Lokasi Sentral Jakarta' }}
                                    </div>
                                </div>
                            </label>
                            @empty
                            <div class="p-3 text-muted" style="background:#f8fafc;border-radius:8px;font-size:13px;">
                                Belum ada data gudang yang terdaftar di sistem.
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- FORM FOOTER ACTIONS -->
                    <div class="form-actions-bar">
                        <div class="flex items-center gap-2 flex-wrap form-buttons-wrapper">
                            <button type="submit" class="btn btn-primary btn-submit-form">
                                <i class="fas fa-user-plus"></i> Simpan Pengguna Baru
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-secondary btn-cancel-form">
                                <i class="fas fa-xmark"></i> Batal
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .user-edit-container {
            width: 100%;
        }

        .user-form-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 4px 16px rgba(0,0,0,0.04);
            overflow: hidden;
        }

        .user-form-header {
            padding: 18px 24px;
            background: #ffffff;
            border-bottom: 1px solid #f1f5f9;
        }

        .icon-circle {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .icon-circle-primary {
            background: rgba(37, 99, 235, 0.12);
            color: #2563eb;
        }

        .user-form-body {
            padding: 24px;
        }

        .section-title-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
        }

        .section-title {
            font-size: 14.5px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .form-divider {
            height: 1px;
            background: #f1f5f9;
            margin: 24px 0;
        }

        .input-icon-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-wrap .input-icon {
            position: absolute;
            left: 12px;
            color: #94a3b8;
            font-size: 14px;
            pointer-events: none;
            transition: color 0.2s;
        }

        .input-icon-wrap .form-control.with-icon {
            padding-left: 36px;
        }

        .input-icon-wrap .form-control:focus + .input-icon,
        .input-icon-wrap:focus-within .input-icon {
            color: #2563eb;
        }

        .role-manage-link {
            font-size: 12px;
            font-weight: 600;
            color: #2563eb;
            text-decoration: none;
            background: rgba(37,99,235,0.08);
            padding: 4px 10px;
            border-radius: 6px;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .role-manage-link:hover {
            background: #2563eb;
            color: #ffffff;
        }

        .roles-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .role-card-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 14px;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            user-select: none;
        }

        .role-card-item:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
            transform: translateY(-1px);
        }

        .role-card-item.active {
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.03);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.08);
        }

        .role-card-checkbox input[type="checkbox"] {
            width: 17px;
            height: 17px;
            cursor: pointer;
            accent-color: #2563eb;
            border-radius: 4px;
            margin: 0;
            display: block;
        }

        .role-card-icon {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .role-card-details {
            flex: 1;
            min-width: 0;
        }

        .role-card-name {
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.25;
        }

        .role-card-desc {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
            line-height: 1.35;
        }

        .warehouses-selection-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .wh-select-card {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 12px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
        }

        .wh-select-card:hover {
            border-color: #cbd5e1;
            background: #ffffff;
        }

        .wh-select-card.active {
            border-color: #2563eb;
            background: rgba(37,99,235,0.03);
        }

        .wh-select-card input[type="checkbox"] {
            margin-top: 3px;
            width: 16px;
            height: 16px;
            accent-color: #2563eb;
            cursor: pointer;
            flex-shrink: 0;
        }

        .wh-select-body {
            flex: 1;
            min-width: 0;
        }

        .wh-select-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            margin-bottom: 2px;
        }

        .wh-name {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .wh-badge {
            font-size: 9.5px;
            padding: 1px 6px;
            flex-shrink: 0;
        }

        .wh-location {
            font-size: 11.5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .form-actions-bar {
            margin-top: 30px;
            padding-top: 18px;
            border-top: 1px solid #f1f5f9;
        }

        .btn-submit-form {
            padding: 10px 22px;
            font-size: 14px;
            font-weight: 700;
        }

        .btn-cancel-form {
            padding: 10px 18px;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .user-edit-container {
                max-width: 100%;
            }

            .user-form-body {
                padding: 16px;
            }

            .responsive-grid {
                grid-template-columns: 1fr !important;
                gap: 0;
            }

            .warehouses-selection-grid {
                grid-template-columns: 1fr !important;
            }

            .form-buttons-wrapper {
                flex-direction: column;
                width: 100%;
            }

            .btn-submit-form,
            .btn-cancel-form {
                width: 100%;
                justify-content: center;
                padding: 12px;
            }
        }
    </style>
    @endpush
</x-app-layout>
