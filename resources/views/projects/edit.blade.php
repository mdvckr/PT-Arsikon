<x-app-layout>
    <x-slot name="title">Edit Proyek - {{ $project->name }}</x-slot>

    <div class="project-edit-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb mb-3">
            <a href="{{ route('projects.index') }}"><i class="fas fa-folder" style="font-size:12px;"></i> Data Proyek</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
            <span style="color:#1e293b;font-weight:600;">Edit Proyek</span>
        </div>

        <!-- Project Banner Summary -->
        <div class="card mb-4 project-banner-card">
            <div class="card-body" style="padding: 20px 24px;">
                <div class="project-banner-flex">
                    <div class="project-icon-badge">
                        <i class="fas fa-building-circle-check"></i>
                    </div>
                    <div class="project-banner-info">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h2 class="project-banner-title">{{ $project->name }}</h2>
                            @php
                                $badgeClass = match($project->status) {
                                    'active', 'ongoing' => 'badge-success',
                                    'completed'         => 'badge-primary',
                                    'planning'          => 'badge-purple',
                                    'on_hold'           => 'badge-warning',
                                    default             => 'badge-secondary',
                                };
                                $statusLabel = match($project->status) {
                                    'active', 'ongoing' => 'Sedang Berjalan',
                                    'completed'         => 'Selesai',
                                    'planning'          => 'Perencanaan',
                                    'on_hold'           => 'Ditangguhkan',
                                    default             => ucfirst($project->status ?? 'Aktif'),
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">
                                <i class="fas fa-circle-dot" style="font-size:9px;"></i> {{ $statusLabel }}
                            </span>
                        </div>
                        <div class="project-banner-meta">
                            <span><i class="fas fa-barcode text-muted"></i> {{ $project->code ?: 'KODE OTOMATIS' }}</span>
                            <span class="meta-dot">&bull;</span>
                            <span><i class="fas fa-calendar-days text-muted"></i> Mulai: {{ $project->start_date ? (is_string($project->start_date) ? substr($project->start_date, 0, 10) : $project->start_date->format('d M Y')) : '-' }}</span>
                            @if($project->warehouses_count ?? false || $project->warehouses()->count() > 0)
                                <span class="meta-dot">&bull;</span>
                                <span class="text-primary fw-600"><i class="fas fa-warehouse"></i> {{ $project->warehouses()->count() }} Gudang Site Terhubung</span>
                            @endif
                        </div>
                    </div>
                    <div class="project-banner-action">
                        <a href="{{ route('projects.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="card shadow-sm project-form-card">
            <div class="card-header project-form-header">
                <div class="flex items-center gap-2">
                    <div class="icon-circle icon-circle-warning">
                        <i class="fas fa-pen-to-square"></i>
                    </div>
                    <div>
                        <span class="card-title" style="font-size:16px;">Formulir Perubahan Proyek</span>
                        <p class="text-muted" style="font-size:12px;margin:2px 0 0 0;">Perbarui informasi master data proyek konstruksi dan estimasi target pengerjaan.</p>
                    </div>
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

                <form method="POST" action="{{ route('projects.update', $project) }}" id="editProjectForm">
                    @csrf 
                    @method('PUT')

                    <!-- SECTION 1: IDENTITAS PROYEK -->
                    <div class="form-section mb-4">
                        <div class="section-title-wrap">
                            <i class="fas fa-file-contract text-primary"></i>
                            <h3 class="section-title">Informasi Utama Proyek</h3>
                        </div>

                        <div class="grid grid-2 responsive-grid">
                            <div class="mb-3">
                                <label class="form-label" for="projectCode">
                                    Kode Proyek <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-barcode input-icon"></i>
                                    <input type="text" id="projectCode" name="code" value="{{ old('code', $project->code) }}" class="form-control with-icon" placeholder="Contoh: PRJ-004" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="projectName">
                                    Nama Proyek <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-building input-icon"></i>
                                    <input type="text" id="projectName" name="name" value="{{ old('name', $project->name) }}" class="form-control with-icon" placeholder="Nama proyek pembangunan..." required>
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

                        @php
                            $formattedStart = $project->start_date instanceof \DateTimeInterface 
                                ? $project->start_date->format('Y-m-d') 
                                : ($project->start_date ? substr((string)$project->start_date, 0, 10) : '');
                            $formattedEnd = $project->end_date instanceof \DateTimeInterface 
                                ? $project->end_date->format('Y-m-d') 
                                : ($project->end_date ? substr((string)$project->end_date, 0, 10) : '');
                            $curStatus = old('status', $project->status);
                        @endphp

                        <div class="grid grid-2 responsive-grid">
                            <div class="mb-3">
                                <label class="form-label" for="startDate">
                                    Tanggal Mulai <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-calendar-day input-icon"></i>
                                    <input type="date" id="startDate" name="start_date" value="{{ old('start_date', $formattedStart) }}" class="form-control with-icon" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="endDate">
                                    Estimasi Tanggal Selesai
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-flag-checkered input-icon"></i>
                                    <input type="date" id="endDate" name="end_date" value="{{ old('end_date', $formattedEnd) }}" class="form-control with-icon">
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
                                    <option value="planning" {{ $curStatus == 'planning' ? 'selected' : '' }}>📋 Perencanaan (Planning)</option>
                                    <option value="active" {{ in_array($curStatus, ['active', 'ongoing']) ? 'selected' : '' }}>⚡ Sedang Berjalan (Ongoing / Active)</option>
                                    <option value="on_hold" {{ in_array($curStatus, ['on_hold', 'suspended']) ? 'selected' : '' }}>⏸️ Ditangguhkan (On Hold)</option>
                                    <option value="completed" {{ $curStatus == 'completed' ? 'selected' : '' }}>✅ Selesai (Completed)</option>
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
                                <textarea id="projectLocation" name="location" class="form-control with-icon" rows="2" placeholder="Alamat atau lokasi proyek site (kabupaten, kota, provinsi)...">{{ old('location', $project->location) }}</textarea>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="projectDesc">
                                Deskripsi & Catatan Proyek
                            </label>
                            <div class="input-icon-wrap" style="align-items:flex-start;">
                                <i class="fas fa-align-left input-icon" style="top:12px;"></i>
                                <textarea id="projectDesc" name="description" class="form-control with-icon" rows="3" placeholder="Informasi ringkas mengenai spesifikasi pengerjaan proyek...">{{ old('description', $project->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- FORM FOOTER ACTIONS -->
                    <div class="form-actions-bar">
                        <div class="flex items-center gap-2 flex-wrap form-buttons-wrapper">
                            <button type="submit" class="btn btn-primary btn-submit-form">
                                <i class="fas fa-floppy-disk"></i> Simpan Perubahan
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

        /* Project Banner Card */
        .project-banner-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }

        .project-banner-flex {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .project-icon-badge {
            width: 58px;
            height: 58px;
            border-radius: 14px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            flex-shrink: 0;
        }

        .project-banner-info {
            flex: 1;
            min-width: 220px;
        }

        .project-banner-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            line-height: 1.2;
        }

        .project-banner-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 13px;
            color: #64748b;
            margin-top: 4px;
        }

        .meta-dot {
            color: #cbd5e1;
        }

        .project-banner-action {
            flex-shrink: 0;
        }

        /* Form Card */
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
        .icon-circle-warning {
            background: rgba(245, 158, 11, 0.12);
            color: #d97706;
        }

        .project-form-body {
            padding: 24px;
        }

        /* Section Titles */
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

        /* Input With Icon */
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

        /* Form Actions Bar */
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

        /* RESPONSIVE MOBILE STYLES */
        @media (max-width: 768px) {
            .project-edit-container {
                max-width: 100%;
            }

            .project-banner-flex {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .project-banner-action {
                width: 100%;
            }

            .project-banner-action .btn {
                width: 100%;
                justify-content: center;
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
