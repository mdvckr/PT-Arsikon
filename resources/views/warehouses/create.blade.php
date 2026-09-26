<x-app-layout>
    <x-slot name="title">Tambah Gudang Baru</x-slot>

    <div class="warehouse-edit-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb mb-3">
            <a href="{{ route('warehouses.index') }}"><i class="fas fa-warehouse" style="font-size:12px;"></i> Data Gudang</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right" style="font-size:10px;"></i></span>
            <span style="color:#1e293b;font-weight:600;">Tambah Baru</span>
        </div>

        <!-- Form Card -->
        <div class="card shadow-sm warehouse-form-card">
            <div class="card-header warehouse-form-header">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <div class="icon-circle icon-circle-primary">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div>
                            <span class="card-title" style="font-size:16px;">Form Tambah Gudang Baru</span>
                            <p class="text-muted" style="font-size:12px;margin:2px 0 0 0;">Daftarkan lokasi pergudangan sentral atau gudang logistik proyek site.</p>
                        </div>
                    </div>
                    <a href="{{ route('warehouses.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
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

                <form method="POST" action="{{ route('warehouses.store') }}">
                    @csrf

                    <!-- SECTION 1: IDENTITAS GUDANG -->
                    <div class="form-section mb-4">
                        <div class="section-title-wrap">
                            <i class="fas fa-warehouse text-primary"></i>
                            <h3 class="section-title">Identitas Gudang</h3>
                        </div>

                        <div class="grid grid-2 responsive-grid">
                            <div class="mb-3">
                                <label class="form-label" for="whCode">
                                    Kode Gudang <span class="text-muted" style="font-size:12px;font-weight:normal;">(Otomatis jika kosong)</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-barcode input-icon"></i>
                                    <input type="text" id="whCode" name="code" value="{{ old('code') }}" class="form-control with-icon" placeholder="mis. W-PRJ-005">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="whName">
                                    Nama Gudang <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-building input-icon"></i>
                                    <input type="text" id="whName" name="name" value="{{ old('name') }}" class="form-control with-icon" required placeholder="mis. Gudang Site BSD City">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-divider"></div>

                    <!-- SECTION 2: TIPE & RELASI PROYEK -->
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
                                        <option value="project" {{ old('type', 'project') == 'project' ? 'selected' : '' }}>🏗️ Gudang Proyek (Project Site)</option>
                                        <option value="central" {{ old('type') == 'central' ? 'selected' : '' }}>🏢 Gudang Pusat (Central)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3" id="projectWrapper" style="display:{{ old('type', 'project') == 'project' ? 'block' : 'none' }};">
                                <label class="form-label" for="projectId">
                                    Terkait Proyek <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <i class="fas fa-folder input-icon"></i>
                                    <select name="project_id" class="form-control with-icon" id="projectId">
                                        <option value="">Pilih Proyek Terkait</option>
                                        @foreach(\App\Models\Project::where('status', '!=', 'completed')->orderBy('name')->get() as $proj)
                                        <option value="{{ $proj->id }}" {{ old('project_id') == $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                                        @endforeach
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
                                <textarea id="whAddress" name="address" class="form-control with-icon" rows="3" placeholder="Alamat lengkap lokasi gudang atau instruksi akses pengiriman...">{{ old('address', old('location')) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- FORM FOOTER ACTIONS -->
                    <div class="form-actions-bar">
                        <div class="flex items-center gap-2 flex-wrap form-buttons-wrapper">
                            <button type="submit" class="btn btn-primary btn-submit-form">
                                <i class="fas fa-plus-circle"></i> Simpan Gudang Baru
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
        .icon-circle-primary {
            background: rgba(37, 99, 235, 0.12);
            color: #2563eb;
        }

        .warehouse-form-body {
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
            .warehouse-edit-container {
                max-width: 100%;
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
