<x-app-layout>
    <x-slot name="title">Edit Proyek - {{ $project->name }}</x-slot>

    @push('styles')
        @include('projects.partials.styles')
    @endpush

    @php
        $formattedStart = $project->start_date?->format('Y-m-d');
        $formattedEnd = $project->end_date?->format('Y-m-d');
        $curStatus = old('status', $project->status);
        $curStatus = match ($curStatus) {
            'ongoing' => 'active',
            'suspended' => 'on_hold',
            default => $curStatus,
        };
        $warehouseTotal = $project->warehouses()->count();
    @endphp

    <div class="proj-form-shell" data-animate="panel">
        <nav class="proj-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('projects.index') }}"><x-lucide-folder-kanban /> Data Proyek</a>
            <x-lucide-chevron-right />
            <span style="color:#1e293b;font-weight:600;">Edit Proyek</span>
        </nav>

        <div class="proj-banner">
            <span class="proj-banner-icon"><x-lucide-building-2 /></span>
            <div class="proj-banner-info">
                <div class="proj-banner-title-row">
                    <h1 class="proj-banner-title">{{ $project->name }}</h1>
                    <x-project-status-badge :status="$project->status" />
                </div>
                <div class="proj-banner-meta">
                    <span><x-lucide-barcode /> {{ $project->code ?: 'Kode otomatis' }}</span>
                    <span><x-lucide-calendar-days /> Mulai: {{ $project->start_date?->format('d M Y') ?? 'Belum dijadwalkan' }}</span>
                    <span><x-lucide-warehouse /> {{ $warehouseTotal }} gudang site terhubung</span>
                </div>
            </div>
            <div class="proj-banner-actions">
                <a href="{{ route('projects.index') }}" class="btn btn-secondary btn-sm">
                    <x-lucide-arrow-left /> Kembali
                </a>
            </div>
        </div>

        <div class="proj-form-card">
            <div class="proj-form-head">
                <div class="proj-form-head-main">
                    <span class="proj-form-head-icon is-amber"><x-lucide-square-pen /></span>
                    <div>
                        <h2 class="proj-form-title">Formulir Perubahan Proyek</h2>
                        <p class="proj-form-sub">Perbarui informasi master data proyek dan estimasi target pengerjaan.</p>
                    </div>
                </div>
            </div>

            <div class="proj-form-body">
                @if($errors->any())
                <div class="proj-alert" role="alert">
                    <x-lucide-triangle-alert />
                    <div>
                        <strong>Terdapat {{ $errors->count() }} kesalahan pada pengisian form:</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <form method="POST" action="{{ route('projects.update', $project) }}" id="editProjectForm">
                    @csrf
                    @method('PUT')

                    <div class="proj-section">
                        <div class="proj-section-head">
                            <x-lucide-file-text style="color:#2563eb;" />
                            <h2 class="proj-section-title">Informasi Utama Proyek</h2>
                        </div>

                        <div class="proj-grid-2">
                            <div class="proj-field-block">
                                <label class="form-label" for="projectCode">
                                    Kode Proyek <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <x-lucide-barcode class="input-icon" />
                                    <input type="text"
                                           id="projectCode"
                                           name="code"
                                           value="{{ old('code', $project->code) }}"
                                           class="form-control with-icon @error('code') is-invalid @enderror"
                                           placeholder="Contoh: PRJ-004"
                                           maxlength="30"
                                           required>
                                </div>
                                @error('code')
                                    <span class="proj-error"><x-lucide-circle-alert /> {{ $message }}</span>
                                @enderror
                            </div>

                            <div class="proj-field-block">
                                <label class="form-label" for="projectName">
                                    Nama Proyek <span class="text-danger">*</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <x-lucide-building-2 class="input-icon" />
                                    <input type="text"
                                           id="projectName"
                                           name="name"
                                           value="{{ old('name', $project->name) }}"
                                           class="form-control with-icon @error('name') is-invalid @enderror"
                                           placeholder="Nama proyek pembangunan..."
                                           maxlength="255"
                                           required>
                                </div>
                                @error('name')
                                    <span class="proj-error"><x-lucide-circle-alert /> {{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="proj-form-divider"></div>

                    <div class="proj-section">
                        <div class="proj-section-head">
                            <x-lucide-calendar-check style="color:#d97706;" />
                            <h2 class="proj-section-title">Jadwal & Status Pelaksanaan</h2>
                        </div>

                        <div class="proj-grid-2" style="margin-bottom:16px;">
                            <div class="proj-field-block">
                                <label class="form-label" for="startDate">Tanggal Mulai</label>
                                <div class="input-icon-wrap">
                                    <x-lucide-calendar-days class="input-icon" />
                                    <input type="date"
                                           id="startDate"
                                           name="start_date"
                                           value="{{ old('start_date', $formattedStart) }}"
                                           class="form-control with-icon @error('start_date') is-invalid @enderror">
                                </div>
                                <span class="proj-help">Boleh dikosongkan bila jadwal belum ditetapkan.</span>
                                @error('start_date')
                                    <span class="proj-error"><x-lucide-circle-alert /> {{ $message }}</span>
                                @enderror
                            </div>

                            <div class="proj-field-block">
                                <label class="form-label" for="endDate">Estimasi Tanggal Selesai</label>
                                <div class="input-icon-wrap">
                                    <x-lucide-flag class="input-icon" />
                                    <input type="date"
                                           id="endDate"
                                           name="end_date"
                                           value="{{ old('end_date', $formattedEnd) }}"
                                           class="form-control with-icon @error('end_date') is-invalid @enderror">
                                </div>
                                <span class="proj-help">Tidak boleh lebih awal dari tanggal mulai.</span>
                                @error('end_date')
                                    <span class="proj-error"><x-lucide-circle-alert /> {{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="proj-field-block">
                            <label class="form-label">Status Pengerjaan Proyek <span class="text-danger">*</span></label>
                            <div class="proj-status-options">
                                <label class="proj-status-option" for="statusPlanning">
                                    <input type="radio" id="statusPlanning" name="status" value="planning"
                                           @checked($curStatus === 'planning')>
                                    <span class="proj-status-option-top"><x-lucide-clock /> Perencanaan</span>
                                    <span class="proj-status-option-desc">Belum mulai dikerjakan</span>
                                </label>

                                <label class="proj-status-option" for="statusActive">
                                    <input type="radio" id="statusActive" name="status" value="active"
                                           @checked($curStatus === 'active')>
                                    <span class="proj-status-option-top"><x-lucide-activity /> Berjalan</span>
                                    <span class="proj-status-option-desc">Sedang dalam pelaksanaan</span>
                                </label>

                                <label class="proj-status-option" for="statusOnHold">
                                    <input type="radio" id="statusOnHold" name="status" value="on_hold"
                                           @checked($curStatus === 'on_hold')>
                                    <span class="proj-status-option-top"><x-lucide-pause /> Ditangguhkan</span>
                                    <span class="proj-status-option-desc">Sementara dihentikan</span>
                                </label>

                                <label class="proj-status-option" for="statusCompleted">
                                    <input type="radio" id="statusCompleted" name="status" value="completed"
                                           @checked($curStatus === 'completed')>
                                    <span class="proj-status-option-top"><x-lucide-circle-check /> Selesai</span>
                                    <span class="proj-status-option-desc">Pekerjaan telah rampung</span>
                                </label>
                            </div>
                            @error('status')
                                <span class="proj-error"><x-lucide-circle-alert /> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="proj-form-divider"></div>

                    <div class="proj-section">
                        <div class="proj-section-head">
                            <x-lucide-map-pin style="color:#dc2626;" />
                            <h2 class="proj-section-title">Lokasi & Keterangan Tambahan</h2>
                        </div>

                        <div class="proj-field-block" style="margin-bottom:16px;">
                            <label class="form-label" for="projectLocation">Lokasi Proyek</label>
                            <div class="input-icon-wrap" style="align-items:flex-start;">
                                <x-lucide-map-pin class="input-icon" style="top:12px;" />
                                <textarea id="projectLocation"
                                          name="location"
                                          class="form-control with-icon @error('location') is-invalid @enderror"
                                          rows="2"
                                          maxlength="255"
                                          placeholder="Alamat atau lokasi site proyek (kabupaten, kota, provinsi)...">{{ old('location', $project->location) }}</textarea>
                            </div>
                            @error('location')
                                <span class="proj-error"><x-lucide-circle-alert /> {{ $message }}</span>
                            @enderror
                        </div>

                        <div class="proj-field-block">
                            <label class="form-label" for="projectDesc">Deskripsi & Catatan Proyek</label>
                            <div class="input-icon-wrap" style="align-items:flex-start;">
                                <x-lucide-align-left class="input-icon" style="top:12px;" />
                                <textarea id="projectDesc"
                                          name="description"
                                          class="form-control with-icon @error('description') is-invalid @enderror"
                                          rows="3"
                                          maxlength="2000"
                                          placeholder="Informasi ringkas mengenai lingkup dan spesifikasi pekerjaan...">{{ old('description', $project->description) }}</textarea>
                            </div>
                            <span class="proj-help">Maksimal 2.000 karakter. Akan tampil sebagai ringkasan pada tabel proyek.</span>
                            @error('description')
                                <span class="proj-error"><x-lucide-circle-alert /> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="proj-form-actions">
                        <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                            <x-lucide-x /> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <x-lucide-save /> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
