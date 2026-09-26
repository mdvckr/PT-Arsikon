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
                    </div>

                    <!-- FORM FOOTER ACTIONS -->
                    <div class="form-actions-bar">
                        <div class="flex items-center gap-2 flex-wrap form-buttons-wrapper">
                            <button type="submit" class="btn btn-primary btn-submit-form">
                                <i class="fas fa-plus-circle"></i> Simpan Proyek Baru
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
</x-app-layout>
