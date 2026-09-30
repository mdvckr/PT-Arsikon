<x-app-layout>
    <x-slot name="title">Tambah Proyek Baru</x-slot>

    <div class="project-edit-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb mb-3">
            <a href="{{ route('projects.index') }}"><i class="fas fa-folder" style="font-size:12px;"></i> Data Proyek</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
            <span style="color:#1e293b;font-weight:600;">Tambah Baru</span>
        </div>

        <!-- Form Card -->
        <div class="card shadow-sm project-form-card">
            <div class="card-header project-form-header">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <div class="icon-circle icon-circle-primary">
                            <i class="fas fa-folder-plus"></i>
                        </div>
                        <div>
                            <span class="card-title" style="font-size:16px;">Form Tambah Proyek Baru</span>
                            <p class="text-muted" style="font-size:12px;margin:2px 0 0 0;">Daftarkan proyek baru beserta jadwal dan lokasi pengerjaan site.</p>
                        </div>
                    </div>
                    <a href="{{ route('projects.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="card-body project-form-body">
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

                <form method="POST" action="{{ route('projects.store') }}">
                    @csrf

                    <!-- SECTION 1: IDENTITAS PROYEK -->
                    <div class="form-section mb-4">
                        <div class="section-title-wrap">
                            <i class="fas fa-file-contract text-primary"></i>
                            <h3 class="section-title">Informasi Utama Proyek</h3>
                        </div>

                        <div class="grid grid-2 responsive-grid">
                            <div class="mb-3">
                                <label class="form-label" for="projectCode">
                                    Kode Proyek <span class="text-muted" style="font-size:12px;font-weight:normal;">(Otomatis dibuat jika kosong)</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-barcode input-icon"></i>
                                    <input type="text" id="projectCode" name="code" value="{{ old('code') }}" class="form-control with-icon" placeholder="Contoh: PRJ-005">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="projectName">
                                    Nama Proyek <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-building input-icon"></i>
                                    <input type="text" id="projectName" name="name" value="{{ old('name') }}" class="form-control with-icon" placeholder="Nama proyek pembangunan..." required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-divider"></div>

                    <!-- SECTION 2: JADWAL & STATUS -->
                    <div class="form-section mb-4">
                        <div class="section-title-wrap">
                            <i class="fas fa-calendar-check text-warning"></i>
                            <h3 class="section-title">Jadwal & Status Pelaksanaan</h3>
                        </div>

                        <div class="grid grid-2 responsive-grid">
                            <div class="mb-3">
                                <label class="form-label" for="startDate">
                                    Tanggal Mulai <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-calendar-day input-icon"></i>
                                    <input type="date" id="startDate" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" class="form-control with-icon" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="endDate">
                                    Estimasi Tanggal Selesai
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-flag-checkered input-icon"></i>
                                    <input type="date" id="endDate" name="end_date" value="{{ old('end_date') }}" class="form-control with-icon">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" style="max-width: 420px;">
                            <label class="form-label" for="projectStatus">
                                Status Pengerjaan Proyek <span class="text-danger">*</span>
                            </label>
                            <div class="input-icon-wrap">
                                <i class="fas fa-bars-progress input-icon"></i>
                                <select id="projectStatus" name="status" class="form-control with-icon" required>
                                    <option value="planning" {{ old('status') == 'planning' ? 'selected' : '' }}>📋 Perencanaan (Planning)</option>
                                    <option value="ongoing" {{ old('status', 'ongoing') == 'ongoing' ? 'selected' : '' }}>⚡ Sedang Berjalan (Ongoing / Active)</option>
                                    <option value="on_hold" {{ old('status') == 'on_hold' ? 'selected' : '' }}>⏸️ Ditangguhkan (On Hold)</option>
                                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>✅ Selesai (Completed)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-divider"></div>

                    <!-- SECTION 3: LOKASI & DETAIL -->
                    <div class="form-section mb-4">
                        <div class="section-title-wrap">
                            <i class="fas fa-map-location-dot text-danger"></i>
                            <h3 class="section-title">Lokasi & Keterangan Tambahan</h3>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="projectLocation">
                                Lokasi Proyek
                            </label>
                            <div class="input-icon-wrap" style="align-items:flex-start;">
                                <i class="fas fa-location-dot input-icon" style="top:12px;"></i>
                                <textarea id="projectLocation" name="location" class="form-control with-icon" rows="2" placeholder="Alamat atau lokasi proyek site (kabupaten, kota, provinsi)...">{{ old('location') }}</textarea>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="projectDesc">
                                Deskripsi & Catatan Proyek
                            </label>
                            <div class="input-icon-wrap" style="align-items:flex-start;">
                                <i class="fas fa-align-left input-icon" style="top:12px;"></i>
                                <textarea id="projectDesc" name="description" class="form-control with-icon" rows="3" placeholder="Informasi ringkas mengenai spesifikasi pengerjaan proyek...">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    <div class="form-divider"></div>

                    <!-- SECTION 4: GUDANG SITE LOGISTIK TERPADU -->
                    <div class="form-section mb-4" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:18px;">
                        <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
                            <div class="flex items-center gap-2">
                                <div style="width:34px;height:34px;border-radius:8px;background:rgba(234,88,12,0.12);color:#ea580c;display:flex;align-items:center;justify-content:center;font-size:16px;">
                                    <i class="fas fa-warehouse"></i>
                                </div>
                                <div>
                                    <h3 class="section-title" style="margin:0;font-size:15px;color:#0f172a;">Gudang Site Logistik Terpadu</h3>
                                    <p class="text-muted" style="font-size:12px;margin:2px 0 0 0;">Otomatis daftarkan tempat penyimpanan material & alat di site proyek ini sekaligus.</p>
                                </div>
                            </div>
                            <label class="flex items-center gap-2" style="cursor:pointer;background:#fff;padding:6px 14px;border:1px solid #cbd5e1;border-radius:8px;font-weight:600;font-size:13px;color:#1e293b;">
                                <input type="checkbox" id="autoCreateWarehouse" name="auto_create_warehouse" value="1" {{ old('auto_create_warehouse', '1') == '1' ? 'checked' : '' }} onchange="toggleWhBox()" style="width:16px;height:16px;accent-color:#ea580c;">
                                <span>Buat Gudang Site Sekaligus</span>
                            </label>
                        </div>

                        <div id="whFieldsContainer" style="display:{{ old('auto_create_warehouse', '1') == '1' ? 'block' : 'none' }};margin-top:14px;padding-top:14px;border-top:1px dashed #cbd5e1;">
                            <div class="grid grid-2 responsive-grid">
                                <div class="mb-3">
                                    <label class="form-label" for="whName">
                                        Nama Gudang Site
                                    </label>
                                    <div class="input-icon-wrap">
                                        <i class="fas fa-warehouse input-icon"></i>
                                        <input type="text" id="whName" name="warehouse_name" value="{{ old('warehouse_name') }}" class="form-control with-icon" placeholder="Otomatis: Gudang Site [Nama Proyek]">
                                    </div>
                                    <span class="text-muted" style="font-size:11.5px;">Otomatis terisi mengikuti nama proyek bila dikosongkan.</span>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="whCode">
                                        Kode Gudang Site
                                    </label>
                                    <div class="input-icon-wrap">
                                        <i class="fas fa-barcode input-icon"></i>
                                        <input type="text" id="whCode" name="warehouse_code" value="{{ old('warehouse_code') }}" class="form-control with-icon" placeholder="Otomatis (contoh: W-PRJ-005)">
                                    </div>
                                    <span class="text-muted" style="font-size:11.5px;">Otomatis dibuat dari kode proyek bila dikosongkan.</span>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label" for="whAddress">
                                    Alamat / Titik Gudang Site
                                </label>
                                <div class="input-icon-wrap" style="align-items:flex-start;">
                                    <i class="fas fa-location-arrow input-icon" style="top:12px;"></i>
                                    <textarea id="whAddress" name="warehouse_address" class="form-control with-icon" rows="2" placeholder="Otomatis mengambil dari lokasi proyek bila dikosongkan...">{{ old('warehouse_address') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FORM FOOTER ACTIONS -->
                    <div class="form-actions-bar">
                        <div class="flex items-center gap-2 flex-wrap form-buttons-wrapper">
                            <button type="submit" class="btn btn-primary btn-submit-form">
                                <i class="fas fa-plus-circle"></i> <span id="btnSubmitLabel">Simpan Proyek & Gudang Sekaligus</span>
                            </button>
                            <a href="{{ route('projects.index') }}" class="btn btn-secondary btn-cancel-form">
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
        .project-edit-container {
            width: 100%;
        }

        .project-form-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 4px 16px rgba(0,0,0,0.04);
            overflow: hidden;
        }

        .project-form-header {
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

        .project-form-body {
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
            .project-edit-container {
                max-width: 100%;
            }

            .project-form-body {
                padding: 16px;
            }

            .responsive-grid {
                grid-template-columns: 1fr !important;
                gap: 0;
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

    @push('scripts')
    <script>
        function toggleWhBox() {
            const chk = document.getElementById('autoCreateWarehouse');
            const container = document.getElementById('whFieldsContainer');
            const btnLabel = document.getElementById('btnSubmitLabel');
            if (chk && container) {
                container.style.display = chk.checked ? 'block' : 'none';
            }
            if (btnLabel && chk) {
                btnLabel.textContent = chk.checked ? 'Simpan Proyek & Gudang Sekaligus' : 'Simpan Proyek Baru';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const projName = document.getElementById('projectName');
            const whName = document.getElementById('whName');
            const projLoc = document.getElementById('projectLocation');
            const whAddress = document.getElementById('whAddress');
            const projCode = document.getElementById('projectCode');
            const whCode = document.getElementById('whCode');

            let whNameEdited = false;
            let whCodeEdited = false;
            let whAddrEdited = false;

            if (whName) {
                whName.addEventListener('input', () => { whNameEdited = true; });
            }
            if (whCode) {
                whCode.addEventListener('input', () => { whCodeEdited = true; });
            }
            if (whAddress) {
                whAddress.addEventListener('input', () => { whAddrEdited = true; });
            }

            if (projName && whName) {
                projName.addEventListener('input', function() {
                    if (!whNameEdited) {
                        whName.value = this.value.trim() ? ('Gudang Site ' + this.value.trim()) : '';
                    }
                });
            }

            if (projCode && whCode) {
                projCode.addEventListener('input', function() {
                    if (!whCodeEdited) {
                        whCode.value = this.value.trim() ? ('W-' + this.value.trim()) : '';
                    }
                });
            }

            if (projLoc && whAddress) {
                projLoc.addEventListener('input', function() {
                    if (!whAddrEdited) {
                        whAddress.value = this.value;
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
