<x-app-layout>
    <x-slot name="title">Tambah Proyek Baru</x-slot>

    @push('styles')
        @include('projects.partials.styles')
    @endpush

    @php
        $warehouseChecked = old('auto_create_warehouse', $errors->any() ? null : '1') === '1';
    @endphp

    <div class="proj-form-shell" data-animate="panel">
        <nav class="proj-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('projects.index') }}"><x-lucide-folder-kanban /> Data Proyek</a>
            <x-lucide-chevron-right />
            <span style="color:#1e293b;font-weight:600;">Tambah Baru</span>
        </nav>

        <div class="proj-form-card">
            <div class="proj-form-head">
                <div class="proj-form-head-main">
                    <span class="proj-form-head-icon is-blue"><x-lucide-folder-plus /></span>
                    <div>
                        <h1 class="proj-form-title">Form Tambah Proyek Baru</h1>
                        <p class="proj-form-sub">Daftarkan proyek baru beserta jadwal, lokasi, dan gudang site-nya.</p>
                    </div>
                </div>
                <a href="{{ route('projects.index') }}" class="btn btn-secondary btn-sm">
                    <x-lucide-arrow-left /> Kembali
                </a>
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

                <form method="POST" action="{{ route('projects.store') }}">
                    @csrf

                    <div class="proj-section">
                        <div class="proj-section-head">
                            <x-lucide-file-text style="color:#2563eb;" />
                            <h2 class="proj-section-title">Informasi Utama Proyek</h2>
                        </div>

                        <div class="proj-grid-2">
                            <div class="proj-field-block">
                                <label class="form-label" for="projectCode">
                                    Kode Proyek <span class="text-muted" style="font-weight:500;font-size:11.5px;">(otomatis jika kosong)</span>
                                </label>
                                <div class="input-icon-wrap">
                                    <x-lucide-barcode class="input-icon" />
                                    <input type="text"
                                           id="projectCode"
                                           name="code"
                                           value="{{ old('code') }}"
                                           class="form-control with-icon @error('code') is-invalid @enderror"
                                           placeholder="Contoh: PRJ-005"
                                           maxlength="30">
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
                                           value="{{ old('name') }}"
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
                                           value="{{ old('start_date', date('Y-m-d')) }}"
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
                                           value="{{ old('end_date') }}"
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
                                           @checked(old('status', 'active') === 'planning')>
                                    <span class="proj-status-option-top"><x-lucide-clock /> Perencanaan</span>
                                    <span class="proj-status-option-desc">Belum mulai dikerjakan</span>
                                </label>

                                <label class="proj-status-option" for="statusActive">
                                    <input type="radio" id="statusActive" name="status" value="active"
                                           @checked(old('status', 'active') === 'active')>
                                    <span class="proj-status-option-top"><x-lucide-activity /> Berjalan</span>
                                    <span class="proj-status-option-desc">Sedang dalam pelaksanaan</span>
                                </label>

                                <label class="proj-status-option" for="statusOnHold">
                                    <input type="radio" id="statusOnHold" name="status" value="on_hold"
                                           @checked(old('status') === 'on_hold')>
                                    <span class="proj-status-option-top"><x-lucide-pause /> Ditangguhkan</span>
                                    <span class="proj-status-option-desc">Sementara dihentikan</span>
                                </label>

                                <label class="proj-status-option" for="statusCompleted">
                                    <input type="radio" id="statusCompleted" name="status" value="completed"
                                           @checked(old('status') === 'completed')>
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
                                          placeholder="Alamat atau lokasi site proyek (kabupaten, kota, provinsi)...">{{ old('location') }}</textarea>
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
                                          placeholder="Informasi ringkas mengenai lingkup dan spesifikasi pekerjaan...">{{ old('description') }}</textarea>
                            </div>
                            <span class="proj-help">Maksimal 2.000 karakter. Akan tampil sebagai ringkasan pada tabel proyek.</span>
                            @error('description')
                                <span class="proj-error"><x-lucide-circle-alert /> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="proj-form-divider"></div>

                    <div class="proj-warehouse-card">
                        <div class="proj-warehouse-head">
                            <div class="proj-warehouse-title-wrap">
                                <span class="proj-warehouse-icon"><x-lucide-warehouse /></span>
                                <div>
                                    <h2 class="proj-warehouse-title">Gudang Site Logistik Terpadu</h2>
                                    <p class="proj-warehouse-sub">Daftarkan tempat penyimpanan material dan alat di site proyek ini sekaligus.</p>
                                </div>
                            </div>
                            <label class="proj-switch" for="autoCreateWarehouse">
                                <input type="checkbox"
                                       id="autoCreateWarehouse"
                                       name="auto_create_warehouse"
                                       value="1"
                                       @checked($warehouseChecked)
                                       onchange="toggleWhBox()">
                                <span>Buat Gudang Site Sekaligus</span>
                            </label>
                        </div>

                        <div id="whFieldsContainer" class="proj-warehouse-fields" style="display:{{ $warehouseChecked ? 'block' : 'none' }};">
                            <div class="proj-grid-2">
                                <div class="proj-field-block">
                                    <label class="form-label" for="whName">Nama Gudang Site</label>
                                    <div class="input-icon-wrap">
                                        <x-lucide-warehouse class="input-icon" />
                                        <input type="text"
                                               id="whName"
                                               name="warehouse_name"
                                               value="{{ old('warehouse_name') }}"
                                               class="form-control with-icon @error('warehouse_name') is-invalid @enderror"
                                               maxlength="255"
                                               placeholder="Otomatis: Gudang Site [Nama Proyek]">
                                    </div>
                                    <span class="proj-help">Terisi otomatis mengikuti nama proyek bila dikosongkan.</span>
                                    @error('warehouse_name')
                                        <span class="proj-error"><x-lucide-circle-alert /> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="proj-field-block">
                                    <label class="form-label" for="whCode">Kode Gudang Site</label>
                                    <div class="input-icon-wrap">
                                        <x-lucide-barcode class="input-icon" />
                                        <input type="text"
                                               id="whCode"
                                               name="warehouse_code"
                                               value="{{ old('warehouse_code') }}"
                                               class="form-control with-icon @error('warehouse_code') is-invalid @enderror"
                                               maxlength="30"
                                               placeholder="Otomatis (contoh: W-PRJ-005)">
                                    </div>
                                    <span class="proj-help">Dibuat otomatis dari kode proyek bila dikosongkan.</span>
                                    @error('warehouse_code')
                                        <span class="proj-error"><x-lucide-circle-alert /> {{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="proj-field-block" style="margin-top:14px;">
                                <label class="form-label" for="whAddress">Alamat / Titik Gudang Site</label>
                                <div class="input-icon-wrap" style="align-items:flex-start;">
                                    <x-lucide-navigation class="input-icon" style="top:12px;" />
                                    <textarea id="whAddress"
                                              name="warehouse_address"
                                              class="form-control with-icon @error('warehouse_address') is-invalid @enderror"
                                              rows="2"
                                              placeholder="Otomatis mengambil dari lokasi proyek bila dikosongkan...">{{ old('warehouse_address') }}</textarea>
                                </div>
                                @error('warehouse_address')
                                    <span class="proj-error"><x-lucide-circle-alert /> {{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="proj-form-actions">
                        <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                            <x-lucide-x /> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <x-lucide-circle-plus /> <span id="btnSubmitLabel">{{ $warehouseChecked ? 'Simpan Proyek & Gudang Sekaligus' : 'Simpan Proyek Baru' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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

        document.addEventListener('DOMContentLoaded', function () {
            const projName = document.getElementById('projectName');
            const whName = document.getElementById('whName');
            const projLoc = document.getElementById('projectLocation');
            const whAddress = document.getElementById('whAddress');
            const projCode = document.getElementById('projectCode');
            const whCode = document.getElementById('whCode');

            let whNameEdited = false;
            let whCodeEdited = false;
            let whAddrEdited = false;

            if (whName) whName.addEventListener('input', () => { whNameEdited = true; });
            if (whCode) whCode.addEventListener('input', () => { whCodeEdited = true; });
            if (whAddress) whAddress.addEventListener('input', () => { whAddrEdited = true; });

            if (projName && whName) {
                projName.addEventListener('input', function () {
                    if (!whNameEdited) {
                        whName.value = this.value.trim() ? ('Gudang Site ' + this.value.trim()) : '';
                    }
                });
            }

            if (projCode && whCode) {
                projCode.addEventListener('input', function () {
                    if (!whCodeEdited) {
                        whCode.value = this.value.trim() ? ('W-' + this.value.trim()) : '';
                    }
                });
            }

            if (projLoc && whAddress) {
                projLoc.addEventListener('input', function () {
                    if (!whAddrEdited) {
                        whAddress.value = this.value;
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
