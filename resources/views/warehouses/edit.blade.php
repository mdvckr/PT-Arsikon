<x-app-layout>
    <x-slot name="title">Edit Gudang - {{ $warehouse->name }}</x-slot>

    <div class="warehouse-edit-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb mb-3">
            <a href="{{ route('warehouses.index') }}"><i class="fas fa-warehouse" style="font-size:12px;"></i> Data Gudang</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
            <span style="color:#1e293b;font-weight:600;">Edit Gudang</span>
        </div>

        @php
            $isCentral = $warehouse->is_central || in_array($warehouse->type, ['central', 'main', 'pusat']);
            $currentType = old('type', $isCentral ? 'central' : 'project');
        @endphp

        <!-- Header Warehouse Banner -->
        <div class="card mb-4 warehouse-banner-card">
            <div class="card-body" style="padding: 20px 24px;">
                <div class="warehouse-banner-flex">
                    <div class="warehouse-icon-badge {{ $isCentral ? 'central' : 'project' }}">
                        <i class="fas {{ $isCentral ? 'fa-building' : 'fa-helmet-safety' }}"></i>
                    </div>
                    <div class="warehouse-banner-info">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h2 class="warehouse-banner-title">{{ $warehouse->name }}</h2>
                            @if($isCentral)
                                <span class="badge badge-primary"><i class="fas fa-building"></i> Gudang Pusat</span>
                            @else
                                <span class="badge badge-info"><i class="fas fa-person-digging"></i> Gudang Proyek</span>
                            @endif
                            @if($warehouse->is_active)
                                <span class="badge badge-success"><i class="fas fa-circle-check" style="font-size:10px;"></i> Aktif</span>
                            @else
                                <span class="badge badge-danger"><i class="fas fa-circle-xmark" style="font-size:10px;"></i> Nonaktif</span>
                            @endif
                        </div>
                        <div class="warehouse-banner-meta">
                            <span><i class="fas fa-barcode text-muted"></i> {{ $warehouse->code ?: '-' }}</span>
                            @if($warehouse->project)
                                <span class="meta-dot">&bull;</span>
                                <span><i class="fas fa-folder-open text-warning"></i> Proyek: <strong>{{ $warehouse->project->name }}</strong></span>
                            @endif
                            <span class="meta-dot">&bull;</span>
                            <span><i class="fas fa-box text-muted"></i> {{ $warehouse->inventories()->count() }} Item Material</span>
                        </div>
                    </div>
                    <div class="warehouse-banner-action">
                        <a href="{{ route('warehouses.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="card shadow-sm warehouse-form-card">
            <div class="card-header warehouse-form-header">
                <div class="flex items-center gap-2">
                    <div class="icon-circle icon-circle-warning">
                        <i class="fas fa-pen-to-square"></i>
                    </div>
                    <div>
                        <span class="card-title" style="font-size:16px;">Formulir Perubahan Data Gudang</span>
                        <p class="text-muted" style="font-size:12px;margin:2px 0 0 0;">Ubah identitas lokasi, penugasan proyek, dan status operasional pergudangan.</p>
                    </div>
                </div>
            </div>

            <div class="card-body warehouse-form-body">
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

                <form method="POST" action="{{ route('warehouses.update', $warehouse) }}" id="editWarehouseForm">
                    @csrf 
                    @method('PUT')

                    <!-- SECTION 1: IDENTITAS GUDANG -->
                    <div class="form-section mb-4">
                        <div class="section-title-wrap">
                            <i class="fas fa-warehouse text-primary"></i>
                            <h3 class="section-title">Identitas Gudang</h3>
                        </div>

                        <div class="grid grid-2 responsive-grid">
                            <div class="mb-3">
                                <label class="form-label" for="whCode">
                                    Kode Gudang <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-barcode input-icon"></i>
                                    <input type="text" id="whCode" name="code" value="{{ old('code', $warehouse->code) }}" class="form-control with-icon" required placeholder="mis. W-CENTRAL">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="whName">
                                    Nama Gudang <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-building input-icon"></i>
                                    <input type="text" id="whName" name="name" value="{{ old('name', $warehouse->name) }}" class="form-control with-icon" required placeholder="mis. Gudang Sentral Jakarta">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-divider"></div>

                    <!-- SECTION 2: TIPE & PENUGASAN PROYEK -->
                    <div class="form-section mb-4">
                        <div class="section-title-wrap">
                            <i class="fas fa-layer-group text-warning"></i>
                            <h3 class="section-title">Tipe Gudang & Relasi Proyek</h3>
                        </div>

                        <div class="grid grid-2 responsive-grid">
                            <div class="mb-3">
                                <label class="form-label" for="warehouseType">
                                    Tipe Gudang <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-tag input-icon"></i>
                                    <select name="type" class="form-control with-icon" id="warehouseType" required onchange="toggleProject()">
                                        <option value="central" {{ $currentType == 'central' ? 'selected' : '' }}>🏢 Gudang Pusat (Central)</option>
                                        <option value="project" {{ $currentType == 'project' ? 'selected' : '' }}>🏗️ Gudang Proyek (Project Site)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3" id="projectWrapper" style="display:{{ $currentType == 'project' ? 'block' : 'none' }};">
                                <label class="form-label" for="projectId">
                                    Terkait Proyek <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-folder input-icon"></i>
                                    <select name="project_id" class="form-control with-icon" id="projectId">
                                        <option value="">Pilih Proyek Terkait</option>
                                        @foreach(\App\Models\Project::orderBy('name')->get() as $proj)
                                        <option value="{{ $proj->id }}" {{ old('project_id', $warehouse->project_id) == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="whActive">
                                    Status Operasional
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-toggle-on input-icon"></i>
                                    <select name="is_active" id="whActive" class="form-control with-icon">
                                        <option value="1" {{ old('is_active', $warehouse->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>🟢 Aktif (Siap Operasional)</option>
                                        <option value="0" {{ old('is_active', $warehouse->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>🔴 Nonaktif (Operasional Ditutup)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-divider"></div>

                    <!-- SECTION 3: LOKASI & ALAMAT -->
                    <div class="form-section mb-4">
                        <div class="section-title-wrap">
                            <i class="fas fa-location-dot text-danger"></i>
                            <h3 class="section-title">Lokasi & Alamat Gudang</h3>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="whAddress">
                                Alamat Lengkap
                            </label>
                            <div class="input-icon-wrap" style="align-items:flex-start;">
                                <i class="fas fa-map-pin input-icon" style="top:12px;"></i>
                                <textarea id="whAddress" name="address" class="form-control with-icon" rows="3" placeholder="Alamat lengkap lokasi gudang atau instruksi akses pengiriman...">{{ old('address', $warehouse->address ?? $warehouse->location) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- FORM FOOTER ACTIONS -->
                    <div class="form-actions-bar">
                        <div class="flex items-center gap-2 flex-wrap form-buttons-wrapper">
                            <button type="submit" class="btn btn-primary btn-submit-form">
                                <i class="fas fa-floppy-disk"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('warehouses.index') }}" class="btn btn-secondary btn-cancel-form">
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
        .warehouse-edit-container {
            width: 100%;
        }

        /* Banner Card */
        .warehouse-banner-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }

        .warehouse-banner-flex {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .warehouse-icon-badge {
            width: 58px;
            height: 58px;
            border-radius: 14px;
            color: #ffffff;
            font-size: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .warehouse-icon-badge.central {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }
        .warehouse-icon-badge.project {
            background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
        }

        .warehouse-banner-info {
            flex: 1;
            min-width: 220px;
        }

        .warehouse-banner-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            line-height: 1.2;
        }

        .warehouse-banner-meta {
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

        .warehouse-banner-action {
            flex-shrink: 0;
        }

        /* Form Card */
        .warehouse-form-card {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 4px 16px rgba(0,0,0,0.04);
            overflow: hidden;
        }

        .warehouse-form-header {
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

        .warehouse-form-body {
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
            .warehouse-edit-container {
                max-width: 100%;
            }

            .warehouse-banner-flex {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .warehouse-banner-action {
                width: 100%;
            }

            .warehouse-banner-action .btn {
                width: 100%;
                justify-content: center;
            }

            .warehouse-form-body {
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
        function toggleProject() {
            const type = document.getElementById('warehouseType').value;
            const projectWrapper = document.getElementById('projectWrapper');
            const projectId = document.getElementById('projectId');
            if (type === 'project') {
                projectWrapper.style.display = 'block';
                projectId.required = true;
            } else {
                projectWrapper.style.display = 'none';
                projectId.required = false;
                projectId.value = '';
            }
        }
        document.addEventListener('DOMContentLoaded', toggleProject);
    </script>
    @endpush
</x-app-layout>
