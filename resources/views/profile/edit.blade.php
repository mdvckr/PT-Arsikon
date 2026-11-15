<x-app-layout>
    <x-slot name="title">Pengaturan Profil Pegawai - Arsikon</x-slot>

    @push('styles')
    <style>
        :root {
            --arsikon-amber: #ea580c;
            --arsikon-amber-hover: #c2410c;
            --arsikon-amber-soft: #fff7ed;
            --arsikon-navy: #1e3a8a;
            --arsikon-navy-soft: #eff6ff;
            --arsikon-emerald: #059669;
            --arsikon-emerald-soft: #ecfdf5;
            --arsikon-slate-dark: #0f172a;
            --arsikon-slate-muted: #64748b;
            --arsikon-border: rgba(226, 232, 240, 0.95);
        }

        /* ===== FULL-WIDTH FLUID LAYOUT (RATA KANAN-KIRI 100%) ===== */
        .profile-fluid-container {
            width: 100%;
            margin: 0;
            padding: 0 0 32px 0;
        }

        /* ===== TOP PROFILE HERO CARD ===== */
        .profile-hero-card {
            width: 100%;
            background: #ffffff;
            border: 1px solid var(--arsikon-border);
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.04);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        .profile-hero-identity {
            display: flex;
            align-items: center;
            gap: 18px;
            min-width: 280px;
        }

        .profile-avatar-squircle {
            width: 68px;
            height: 68px;
            border-radius: 18px;
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            border: 3px solid #ffffff;
            box-shadow: 0 4px 14px rgba(234, 88, 12, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 0.5px;
            position: relative;
            flex-shrink: 0;
        }

        .profile-online-badge {
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 16px;
            height: 16px;
            background: #10b981;
            border: 2.5px solid #ffffff;
            border-radius: 50%;
        }

        .profile-hero-name {
            font-size: 19px;
            font-weight: 800;
            color: var(--arsikon-slate-dark);
            margin: 0 0 4px 0;
            letter-spacing: -0.02em;
        }

        .profile-hero-email-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--arsikon-slate-muted);
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .profile-copy-btn {
            background: none;
            border: none;
            padding: 0;
            color: var(--arsikon-slate-muted);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            transition: color 0.15s ease;
        }

        .profile-copy-btn:hover {
            color: var(--arsikon-amber);
        }

        .profile-hero-stats {
            display: flex;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
        }

        .profile-stat-box {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .profile-stat-lbl {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: var(--arsikon-slate-muted);
        }

        .profile-stat-val {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--arsikon-slate-dark);
            font-variant-numeric: tabular-nums;
        }

        .profile-hero-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .profile-logout-ghost-btn {
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 10px;
            background: #fff;
            border: 1px solid #fecaca;
            color: #dc2626;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .profile-logout-ghost-btn:hover {
            background: #fef2f2;
            border-color: #f87171;
            color: #b91c1c;
        }

        /* ===== CLEAN CARDS ===== */
        .clean-profile-card {
            background: #ffffff;
            border: 1px solid var(--arsikon-border);
            border-radius: 16px;
            box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.04);
            overflow: hidden;
            margin-bottom: 20px;
            transition: border-color 0.2s ease;
        }

        .clean-profile-card:hover {
            border-color: rgba(203, 213, 225, 0.95);
        }

        .clean-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fafbfc;
        }

        .clean-card-title {
            font-size: 14px;
            font-weight: 800;
            color: var(--arsikon-slate-dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: -0.01em;
        }

        .clean-card-body {
            padding: 20px;
        }

        /* ===== FORM CONTROLS & INPUT LENS ===== */
        .field-label-clean {
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 6px;
            display: block;
        }

        .input-lens-box {
            position: relative;
            display: flex;
            align-items: stretch;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            background: #ffffff;
            transition: all 0.2s ease;
            overflow: hidden;
        }

        .input-lens-box:focus-within {
            border-color: var(--arsikon-amber);
            box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.12);
        }

        .input-icon-lead {
            width: 38px;
            background: #f8fafc;
            border-right: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            flex-shrink: 0;
            transition: color 0.15s ease;
        }

        .input-lens-box:focus-within .input-icon-lead {
            color: var(--arsikon-amber);
            background: var(--arsikon-amber-soft);
        }

        .input-clean-field {
            width: 100%;
            border: none;
            padding: 10px 14px;
            font-size: 13px;
            color: var(--arsikon-slate-dark);
            outline: none;
            background: transparent;
            font-family: inherit;
        }

        .input-clean-field::placeholder {
            color: #94a3b8;
            font-size: 12.5px;
        }

        .input-eye-toggle {
            background: none;
            border: none;
            padding: 0 12px;
            color: #94a3b8;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s ease;
        }

        .input-eye-toggle:hover {
            color: var(--arsikon-slate-dark);
        }

        .field-error-feedback {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: #dc2626;
            font-size: 11.5px;
            font-weight: 600;
            margin-top: 5px;
        }

        /* ===== ACTION BUTTONS ===== */
        .btn-clean-amber {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            color: #ffffff;
            border: none;
            border-radius: 9px;
            padding: 9px 20px;
            font-size: 12.5px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(234, 88, 12, 0.25);
            transition: all 0.15s ease;
        }

        .btn-clean-amber:hover {
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.35);
            transform: translateY(-1px);
        }

        .btn-clean-navy {
            background: #0f172a;
            color: #ffffff;
            border: none;
            border-radius: 9px;
            padding: 9px 20px;
            font-size: 12.5px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.2);
            transition: all 0.15s ease;
        }

        .btn-clean-navy:hover {
            background: #1e293b;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.3);
            transform: translateY(-1px);
        }

        /* ===== ROLE MEMO & BADGES ===== */
        .role-authority-clean-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px 16px;
        }

        .role-pill-clean {
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .role-pill-clean.amber {
            background: #fff7ed;
            color: #ea580c;
            border: 1px solid #fed7aa;
        }

        .role-pill-clean.blue {
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }

        .role-pill-clean.green {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .role-pill-clean.gold {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .role-pill-clean.slate {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }

        /* ===== ASSIGNED LOCATIONS ITEM ===== */
        .wh-item-clean {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: 8px;
            transition: all 0.15s ease;
        }

        .wh-item-clean:hover {
            background: #ffffff;
            border-color: #cbd5e1;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
        }

        /* ===== LIVE PASSWORD STRENGTH BARS ===== */
        .pwd-strength-wrap {
            margin-top: 8px;
            padding: 8px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }

        .pwd-strength-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 11px;
            font-weight: 600;
            color: var(--arsikon-slate-muted);
        }

        .pwd-bars-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 4px;
            height: 4px;
        }

        .pwd-seg {
            background: #e2e8f0;
            border-radius: 2px;
            transition: background 0.2s ease;
        }

        /* ===== MODAL ===== */
        .custom-modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(4px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .custom-modal-backdrop.open {
            display: flex;
        }

        .clean-modal-card {
            background: #ffffff;
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.97) translateY(8px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
    </style>
    @endpush

    <div class="profile-fluid-container">

        {{-- Breadcrumb Navigation --}}
        <div class="flex items-center gap-1.5 mb-3 text-xs text-slate-500 font-medium">
            <a href="{{ route('dashboard') }}" class="text-primary hover:text-orange-600 transition flex items-center gap-1">
                <x-lucide-home class="w-3.5 h-3.5" /> Beranda
            </a>
            <x-lucide-chevron-right class="w-3 h-3 text-slate-400" />
            <span class="text-slate-800 font-semibold">Pengaturan Profil & Keamanan</span>
        </div>

        {{-- Flash Alerts --}}
        @if (session('status') === 'profile-updated')
        <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between">
            <span class="flex items-center gap-2">
                <x-lucide-circle-check class="w-4 h-4 text-emerald-600" />
                Informasi profil berhasil diperbarui dan disinkronkan ke seluruh sistem.
            </span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900">&times;</button>
        </div>
        @endif

        @if (session('status') === 'password-updated')
        <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center justify-between">
            <span class="flex items-center gap-2">
                <x-lucide-shield-check class="w-4 h-4 text-emerald-600" />
                Kata sandi berhasil diperbarui. Silakan gunakan kata sandi baru untuk login sesi berikutnya.
            </span>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900">&times;</button>
        </div>
        @endif

        @if ($errors->any())
        <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
            <div class="flex items-center gap-2 font-bold mb-1">
                <x-lucide-circle-alert class="w-4 h-4 text-rose-600" />
                Terdapat kesalahan pada formulir yang dikirimkan:
            </div>
            <ul class="list-disc pl-5 mt-1 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- TOP FULL-WIDTH HERO BANNER --}}
        <div class="profile-hero-card">
            <div class="profile-hero-identity">
                <div class="profile-avatar-squircle">
                    @php
                        $words = explode(' ', trim($user->name));
                        $initials = count($words) >= 2 
                            ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                            : strtoupper(substr($words[0], 0, 2));
                    @endphp
                    <span>{{ $initials }}</span>
                    <span class="profile-online-badge" title="Akun Aktif"></span>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h1 class="profile-hero-name">{{ $user->name }}</h1>
                        <x-lucide-badge-check class="w-5 h-5 text-emerald-500 shrink-0" />
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <div class="profile-hero-email-tag">
                            <x-lucide-mail class="w-3.5 h-3.5 text-slate-400" />
                            <span>{{ $user->email }}</span>
                            <button type="button" class="profile-copy-btn" title="Salin Email" onclick="copyText('{{ $user->email }}', 'Email disalin!')">
                                <x-lucide-copy class="w-3 h-3" />
                            </button>
                        </div>
                        {{-- Primary Role Badge --}}
                        @php
                            $primaryRole = $user->roles->first()?->name ?? 'Staf Terdaftar';
                        @endphp
                        @if($primaryRole === 'Owner')
                            <span class="role-pill-clean gold"><x-lucide-crown class="w-3.5 h-3.5" /> Owner / Direksi</span>
                        @elseif($primaryRole === 'Admin Pusat')
                            <span class="role-pill-clean blue"><x-lucide-shield-check class="w-3.5 h-3.5" /> Admin Pusat</span>
                        @elseif(in_array($primaryRole, ['Admin PO', 'Admin Pengadaan']))
                            <span class="role-pill-clean amber"><x-lucide-shopping-cart class="w-3.5 h-3.5" /> Admin Pengadaan Pusat</span>
                        @elseif(in_array($primaryRole, ['Admin PO Proyek', 'Admin Pengadaan Proyek', 'admin-po-nota']))
                            <span class="role-pill-clean green"><x-lucide-receipt class="w-3.5 h-3.5" /> Admin PO Proyek</span>
                        @elseif(in_array($primaryRole, ['Site Engineer', 'site-engineer']))
                            <span class="role-pill-clean amber"><x-lucide-hard-hat class="w-3.5 h-3.5" /> Site Engineer Lapangan</span>
                        @else
                            <span class="role-pill-clean slate"><x-lucide-user class="w-3.5 h-3.5" /> {{ ucfirst($primaryRole) }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Stat Chips --}}
            <div class="profile-hero-stats">
                <div class="profile-stat-box">
                    <span class="profile-stat-lbl">ID Pengguna</span>
                    <span class="profile-stat-val font-mono">
                        #USR-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}
                        <button type="button" class="profile-copy-btn" title="Salin ID" onclick="copyText('#USR-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}', 'ID Pengguna disalin!')">
                            <x-lucide-copy class="w-3 h-3 ml-1" />
                        </button>
                    </span>
                </div>
                <div class="profile-stat-box">
                    <span class="profile-stat-lbl">Terdaftar Sejak</span>
                    <span class="profile-stat-val">{{ $user->created_at ? $user->created_at->format('d M Y') : '—' }}</span>
                </div>
            </div>

            {{-- Sign Out Action --}}
            <div class="profile-hero-actions">
                <form method="POST" action="{{ route('logout') }}" class="m-0" 
                      data-confirm="Apakah Anda yakin ingin mengakhiri sesi login saat ini?" 
                      data-confirm-title="Konfirmasi Keluar Akun" 
                      data-confirm-btn="Ya, Keluar" 
                      data-confirm-danger="true">
                    @csrf
                    <button type="submit" class="profile-logout-ghost-btn">
                        <x-lucide-log-out class="w-3.5 h-3.5 transform -scale-x-100" />
                        <span>Keluar Sesi</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- CARD 1: INFORMASI PRIBADI & KONTAK PEGAWAI --}}
        <div class="clean-profile-card">
            <div class="clean-card-header">
                <div class="clean-card-title">
                    <x-lucide-user-pen class="w-4 h-4 text-orange-600" />
                    <span>Informasi Pribadi & Kontak Pegawai</span>
                </div>
            </div>

            <div class="clean-card-body">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-4">
                        {{-- Nama --}}
                        <div>
                            <label class="field-label-clean" for="profile_name">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <div class="input-lens-box">
                                <div class="input-icon-lead">
                                    <x-lucide-user class="w-4 h-4" />
                                </div>
                                <input type="text" id="profile_name" name="name" class="input-clean-field" 
                                    value="{{ old('name', $user->name) }}" required autocomplete="name"
                                    placeholder="Masukkan nama lengkap...">
                            </div>
                            @if ($errors->get('name'))
                                <div class="field-error-feedback">
                                    <x-lucide-circle-alert class="w-3.5 h-3.5" />
                                    <span>{{ $errors->first('name') }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="field-label-clean" for="profile_email">
                                Alamat Email Resmi <span class="text-rose-500">*</span>
                            </label>
                            <div class="input-lens-box">
                                <div class="input-icon-lead">
                                    <x-lucide-mail class="w-4 h-4" />
                                </div>
                                <input type="email" id="profile_email" name="email" class="input-clean-field" 
                                    value="{{ old('email', $user->email) }}" required autocomplete="username"
                                    placeholder="nama@arsikon.co.id">
                            </div>
                            @if ($errors->get('email'))
                                <div class="field-error-feedback">
                                    <x-lucide-circle-alert class="w-3.5 h-3.5" />
                                    <span>{{ $errors->first('email') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-100 flex-wrap gap-3">
                        <span class="text-xs text-slate-500 flex items-center gap-1.5">
                            <x-lucide-shield-check class="w-4 h-4 text-emerald-600" />
                            Perubahan data profil langsung disinkronkan ke sesi kerja Anda.
                        </span>
                        <button type="submit" class="btn-clean-amber">
                            <x-lucide-save class="w-4 h-4" />
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- CARD 2: WEWENANG LOKASI & GUDANG --}}
        <div class="clean-profile-card">
            <div class="clean-card-header">
                <div class="clean-card-title">
                    <x-lucide-warehouse class="w-4 h-4 text-blue-700" />
                    <span>Wewenang Lokasi & Gudang Penugasan</span>
                </div>
                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                    {{ $user->hasAnyRole(['Owner', 'Admin Pusat']) ? 'Hak Akses Global' : $user->warehouses->count() . ' Lokasi Terdaftar' }}
                </span>
            </div>

            <div class="clean-card-body">
                @if($user->hasAnyRole(['Owner', 'Admin Pusat']))
                    <div class="p-4 rounded-xl bg-blue-50/70 border border-blue-200 text-blue-900 text-xs leading-relaxed flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 border border-blue-200 flex items-center justify-center text-blue-700 shrink-0 mt-0.5">
                            <x-lucide-crown class="w-4 h-4 text-amber-600" />
                        </div>
                        <div>
                            <div class="font-bold text-sm text-blue-950 mb-0.5">Hak Akses Menyeluruh (Global ERP System)</div>
                            Akun Anda memiliki izin administratif tertinggi untuk memantau, mengelola, serta memverifikasi seluruh operasional Gudang Pusat dan Gudang Proyek aktif PT Arsikon.
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @forelse($user->warehouses as $wh)
                        <div class="wh-item-clean">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $wh->is_central ? 'bg-blue-50 text-blue-800 border border-blue-200' : 'bg-orange-50 text-orange-700 border border-orange-200' }}">
                                    @if($wh->is_central)
                                        <x-lucide-building-2 class="w-4 h-4" />
                                    @else
                                        <x-lucide-hard-hat class="w-4 h-4" />
                                    @endif
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 text-xs">{{ $wh->name }}</div>
                                    <div class="text-slate-500 text-[11px] flex items-center gap-1.5 flex-wrap">
                                        <span>Kode: <strong class="font-mono text-slate-700">{{ $wh->code }}</strong></span>
                                        @if($wh->project)
                                            &bull; <span class="text-orange-600 font-semibold">{{ $wh->project->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold px-2.5 py-0.5 rounded {{ $wh->is_central ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800' }}">
                                {{ $wh->is_central ? 'Pusat' : 'Proyek' }}
                            </span>
                        </div>
                        @empty
                        <div class="col-span-full text-center py-6 text-slate-400 text-xs">
                            <x-lucide-info class="w-6 h-6 mx-auto mb-1.5 text-slate-300" />
                            Belum ada penugasan gudang spesifik untuk akun ini.
                        </div>
                        @endforelse
                    </div>

                    <div class="mt-3 text-[11px] text-slate-400 flex items-center gap-1.5">
                        <x-lucide-lock class="w-3.5 h-3.5 shrink-0 text-slate-400" />
                        <span>Penugasan gudang & proyek dikelola langsung oleh <strong>Owner / Admin Pusat</strong>.</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- CARD 3: KREDENSIAL & PENGGANTIAN KATA SANDI --}}
        <div class="clean-profile-card">
            <div class="clean-card-header">
                <div class="clean-card-title">
                    <x-lucide-key-round class="w-4 h-4 text-blue-800" />
                    <span>Kredensial & Penggantian Kata Sandi</span>
                </div>
            </div>

            <div class="clean-card-body">
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')

                    {{-- Password Saat Ini --}}
                    <div class="mb-4">
                        <label class="field-label-clean" for="update_password_current_password">
                            Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                        </label>
                        <div class="input-lens-box">
                            <div class="input-icon-lead">
                                <x-lucide-lock class="w-4 h-4" />
                            </div>
                            <input type="password" id="update_password_current_password" name="current_password" 
                                class="input-clean-field" autocomplete="current-password" placeholder="Ketik kata sandi saat ini...">
                            <button type="button" class="input-eye-toggle" onclick="togglePass('update_password_current_password', this)">
                                <span class="eye-open"><x-lucide-eye class="w-4 h-4" /></span>
                                <span class="eye-closed" style="display:none;"><x-lucide-eye-off class="w-4 h-4" /></span>
                            </button>
                        </div>
                        @if ($errors->updatePassword->get('current_password'))
                            <div class="field-error-feedback">
                                <x-lucide-circle-alert class="w-3.5 h-3.5" />
                                <span>{{ $errors->updatePassword->first('current_password') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-4">
                        {{-- Password Baru --}}
                        <div>
                            <label class="field-label-clean" for="update_password_password">
                                Kata Sandi Baru <span class="text-rose-500">*</span>
                            </label>
                            <div class="input-lens-box">
                                <div class="input-icon-lead">
                                    <x-lucide-shield-check class="w-4 h-4" />
                                </div>
                                <input type="password" id="update_password_password" name="password" 
                                    class="input-clean-field" autocomplete="new-password" placeholder="Minimal 8 karakter..."
                                    oninput="calcPasswordStrength(this.value)">
                                <button type="button" class="input-eye-toggle" onclick="togglePass('update_password_password', this)">
                                    <span class="eye-open"><x-lucide-eye class="w-4 h-4" /></span>
                                    <span class="eye-closed" style="display:none;"><x-lucide-eye-off class="w-4 h-4" /></span>
                                </button>
                            </div>
                            @if ($errors->updatePassword->get('password'))
                                <div class="field-error-feedback">
                                    <x-lucide-circle-alert class="w-3.5 h-3.5" />
                                    <span>{{ $errors->updatePassword->first('password') }}</span>
                                </div>
                            @endif

                            {{-- Live Strength Indicator --}}
                            <div class="pwd-strength-wrap">
                                <div class="pwd-strength-meta">
                                    <span>Kekuatan Sandi:</span>
                                    <strong id="strengthLabel" class="text-slate-500">Belum Diisi</strong>
                                </div>
                                <div class="pwd-bars-grid">
                                    <div id="pBar1" class="pwd-seg"></div>
                                    <div id="pBar2" class="pwd-seg"></div>
                                    <div id="pBar3" class="pwd-seg"></div>
                                    <div id="pBar4" class="pwd-seg"></div>
                                </div>
                            </div>
                        </div>

                        {{-- Konfirmasi Password Baru --}}
                        <div>
                            <label class="field-label-clean" for="update_password_password_confirmation">
                                Ulangi Kata Sandi Baru <span class="text-rose-500">*</span>
                            </label>
                            <div class="input-lens-box">
                                <div class="input-icon-lead">
                                    <x-lucide-check-check class="w-4 h-4" />
                                </div>
                                <input type="password" id="update_password_password_confirmation" name="password_confirmation" 
                                    class="input-clean-field" autocomplete="new-password" placeholder="Ketik ulang kata sandi baru...">
                                <button type="button" class="input-eye-toggle" onclick="togglePass('update_password_password_confirmation', this)">
                                    <span class="eye-open"><x-lucide-eye class="w-4 h-4" /></span>
                                    <span class="eye-closed" style="display:none;"><x-lucide-eye-off class="w-4 h-4" /></span>
                                </button>
                            </div>
                            @if ($errors->updatePassword->get('password_confirmation'))
                                <div class="field-error-feedback">
                                    <x-lucide-circle-alert class="w-3.5 h-3.5" />
                                    <span>{{ $errors->updatePassword->first('password_confirmation') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4 border-t border-slate-100 flex-wrap gap-3">
                        <span class="text-xs text-slate-500 flex items-center gap-1.5">
                            <x-lucide-info class="w-4 h-4 text-blue-700" />
                            Gunakan kombinasi minimal 8 karakter huruf dan angka.
                        </span>
                        <button type="submit" class="btn-clean-navy">
                            <x-lucide-shield class="w-4 h-4" />
                            <span>Perbarui Kata Sandi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- CARD 4: ZONA PROTEKSI AKUN --}}
        <div class="clean-profile-card border-rose-200">
            <div class="clean-card-header bg-rose-50/60 border-rose-100">
                <div class="clean-card-title text-rose-800">
                    <x-lucide-triangle-alert class="w-4 h-4 text-rose-600" />
                    <span>Zona Proteksi Akun</span>
                </div>
            </div>

            <div class="clean-card-body">
                @if($user->hasRole('Owner'))
                    <div class="p-3.5 rounded-xl bg-rose-50/70 border border-rose-200 text-rose-900 text-xs flex items-center gap-2.5">
                        <x-lucide-shield class="w-5 h-5 text-rose-600 flex-shrink-0" />
                        <div>
                            <strong>Akun Utama Dilindungi:</strong> Akun berstatus <strong>Owner / Pemilik Sistem</strong> diproteksi secara permanen dan tidak dapat dihapus melalui formulir mandiri demi integritas audit perusahaan.
                        </div>
                    </div>
                @else
                    <div class="flex items-center justify-between flex-wrap gap-3">
                        <div class="text-xs text-slate-500 leading-relaxed">
                            Menghapus akun akan mencabut seluruh akses, wewenang gudang, dan token sesi login Anda secara permanen.
                        </div>
                        <button type="button" onclick="openDeleteModal()" class="px-3.5 py-2 text-xs font-bold rounded-lg bg-white border border-rose-300 text-rose-600 hover:bg-rose-50 transition flex items-center gap-1.5 cursor-pointer">
                            <x-lucide-trash-2 class="w-3.5 h-3.5" />
                            <span>Hapus Akun Saya</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- MODAL KONFIRMASI HAPUS AKUN --}}
    @if(!$user->hasRole('Owner'))
    <div id="deleteAccountModal" class="custom-modal-backdrop" data-show-on-load="{{ $errors->userDeletion->isNotEmpty() ? '1' : '0' }}">
        <div class="clean-modal-card">
            <div class="p-4 bg-rose-50 border-b border-rose-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 flex items-center justify-center text-rose-600 flex-shrink-0">
                        <x-lucide-triangle-alert class="w-4 h-4" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-rose-900 m-0">Konfirmasi Penghapusan Akun</h3>
                        <p class="text-[11px] text-rose-700 m-0">Tindakan ini tidak dapat dibatalkan</p>
                    </div>
                </div>
                <button type="button" onclick="closeDeleteModal()" class="text-rose-700 hover:text-rose-900 text-lg cursor-pointer">&times;</button>
            </div>

            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <div class="p-5">
                    <p class="text-xs text-slate-600 mb-3 leading-relaxed">
                        Apakah Anda yakin ingin menghapus akun Anda secara permanen? Masukkan kata sandi saat ini untuk mengonfirmasi identitas kepemilikan akun.
                    </p>

                    <div class="mb-3">
                        <label class="field-label-clean" for="delete_modal_password">
                            Kata Sandi Anda <span class="text-rose-500">*</span>
                        </label>
                        <div class="input-lens-box">
                            <div class="input-icon-lead">
                                <x-lucide-lock class="w-4 h-4" />
                            </div>
                            <input type="password" id="delete_modal_password" name="password" 
                                class="input-clean-field" required placeholder="Masukkan kata sandi untuk konfirmasi...">
                            <button type="button" class="input-eye-toggle" onclick="togglePass('delete_modal_password', this)">
                                <span class="eye-open"><x-lucide-eye class="w-4 h-4" /></span>
                                <span class="eye-closed" style="display:none;"><x-lucide-eye-off class="w-4 h-4" /></span>
                            </button>
                        </div>
                        @if ($errors->userDeletion->get('password'))
                            <div class="field-error-feedback">
                                <x-lucide-circle-alert class="w-3.5 h-3.5" />
                                <span>{{ $errors->userDeletion->first('password') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-3.5 px-5 bg-slate-50 border-t border-slate-200 flex justify-end gap-2.5">
                    <button type="button" onclick="closeDeleteModal()" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-bold rounded-lg bg-rose-600 text-white hover:bg-rose-700 transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <x-lucide-trash-2 class="w-3.5 h-3.5" />
                        <span>Ya, Hapus Akun</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        function togglePass(inputId, btn) {
            const input = document.getElementById(inputId);
            const eyeOpen = btn.querySelector('.eye-open');
            const eyeClosed = btn.querySelector('.eye-closed');
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                if (eyeOpen) eyeOpen.style.display = 'none';
                if (eyeClosed) eyeClosed.style.display = 'inline-flex';
            } else {
                input.type = 'password';
                if (eyeOpen) eyeOpen.style.display = 'inline-flex';
                if (eyeClosed) eyeClosed.style.display = 'none';
            }
        }

        function calcPasswordStrength(val) {
            const lbl = document.getElementById('strengthLabel');
            const b1 = document.getElementById('pBar1');
            const b2 = document.getElementById('pBar2');
            const b3 = document.getElementById('pBar3');
            const b4 = document.getElementById('pBar4');
            if (!lbl || !b1) return;

            [b1, b2, b3, b4].forEach(b => b.style.background = '#e2e8f0');

            if (!val || val.length === 0) {
                lbl.innerText = 'Belum Diisi';
                lbl.className = 'text-slate-500 font-bold';
                return;
            }

            let score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val) || val.length >= 12) score++;

            if (score <= 1) {
                lbl.innerText = 'Lemah';
                lbl.className = 'text-rose-600 font-bold';
                b1.style.background = '#ef4444';
            } else if (score === 2) {
                lbl.innerText = 'Cukup';
                lbl.className = 'text-amber-600 font-bold';
                b1.style.background = '#f59e0b';
                b2.style.background = '#f59e0b';
            } else if (score === 3) {
                lbl.innerText = 'Bagus';
                lbl.className = 'text-sky-600 font-bold';
                b1.style.background = '#0284c7';
                b2.style.background = '#0284c7';
                b3.style.background = '#0284c7';
            } else {
                lbl.innerText = 'Sangat Kuat';
                lbl.className = 'text-emerald-600 font-bold';
                b1.style.background = '#10b981';
                b2.style.background = '#10b981';
                b3.style.background = '#10b981';
                b4.style.background = '#10b981';
            }
        }

        function copyText(text, msg) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    if (window.Swal) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: msg || 'Tersalin ke clipboard!',
                            showConfirmButton: false,
                            timer: 1800,
                            timerProgressBar: true
                        });
                    } else {
                        alert(msg || 'Tersalin!');
                    }
                });
            }
        }

        function openDeleteModal() {
            const modal = document.getElementById('deleteAccountModal');
            if (modal) modal.classList.add('open');
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteAccountModal');
            if (modal) modal.classList.remove('open');
        }

        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('deleteAccountModal');
            if (modal && modal.getAttribute('data-show-on-load') === '1') {
                openDeleteModal();
            }
        });
    </script>
    @endpush
</x-app-layout>
