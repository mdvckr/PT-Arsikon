<x-app-layout>
    <x-slot name="title">Edit Gudang - {{ $warehouse->name }}</x-slot>

    @push('styles')
        @include('warehouses.partials.styles')
    @endpush

    @php
        $isCentral = $warehouse->isCentral();
        $adminActive = $warehouse->isAdministrativelyActive();
        $oldType = old('type');
        $currentType = $oldType !== null
            ? (in_array($oldType, ['central', 'main', 'pusat'], true) ? 'central' : 'project')
            : ($isCentral ? 'central' : 'project');
        $isActive = (string) old('is_active', $adminActive ? '1' : '0') === '1';
    @endphp

    <div class="wh-form-shell">
        <nav class="wh-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('warehouses.index') }}">
                <x-lucide-warehouse /> Data Gudang
            </a>
            <x-lucide-chevron-right />
            <span>Edit Gudang</span>
        </nav>

        <div class="wh-banner">
            <div class="wh-banner-icon {{ $isCentral ? 'is-central' : 'is-project' }}">
                @if($isCentral)
                    <x-lucide-building-2 />
                @else
                    <x-lucide-hard-hat />
                @endif
            </div>

            <div class="wh-banner-info">
                <div class="wh-banner-title-row">
                    <h1 class="wh-banner-title">{{ $warehouse->name }}</h1>
                    <x-warehouse-type-badge :central="$isCentral" />
                    <x-warehouse-status-badge :active="$adminActive" />
                </div>
                <div class="wh-banner-meta">
                    <span><x-lucide-barcode /> {{ $warehouse->code ?: 'Tanpa kode' }}</span>
                    @if($warehouse->project)
                        <span><x-lucide-folder-open /> {{ $warehouse->project->name }}</span>
                    @endif
                    <span><x-lucide-package /> {{ $warehouse->inventories_count }} jenis inventori</span>
                    <span><x-lucide-users /> {{ $warehouse->users_count }} user</span>
                </div>
            </div>

            <div class="wh-banner-actions">
                <a href="{{ route('warehouses.index') }}" class="btn btn-secondary btn-sm">
                    <x-lucide-arrow-left /> Kembali
                </a>
            </div>
        </div>

        <div class="wh-form-card">
            <div class="wh-form-head">
                <div class="wh-form-head-main">
                    <div class="wh-form-head-icon is-amber">
                        <x-lucide-square-pen />
                    </div>
                    <div>
                        <h2 class="wh-form-title">Formulir Perubahan Data Gudang</h2>
                        <p class="wh-form-sub">Ubah identitas lokasi, penugasan proyek, dan status operasional pergudangan.</p>
                    </div>
                </div>
            </div>

            <div class="wh-form-body">
                @if($errors->any())
                <div class="wh-alert" role="alert">
                    <x-lucide-circle-alert />
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

                <form method="POST" action="{{ route('warehouses.update', $warehouse) }}">
                    @csrf
                    @method('PUT')

                    <div class="wh-section">
                        <div class="wh-section-head">
                            <x-lucide-warehouse class="text-primary" />
                            <h2 class="wh-section-title">Identitas Gudang</h2>
                        </div>

                        <div class="wh-grid-2">
                            <div class="wh-field-block">
                                <label class="form-label" for="whCode">
                                    Kode Gudang <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       id="whCode"
                                       name="code"
                                       value="{{ old('code', $warehouse->code) }}"
                                       maxlength="30"
                                       class="form-control @error('code') is-invalid @enderror"
                                       placeholder="mis. W-CENTRAL"
                                       required
                                       @error('code') aria-invalid="true" aria-describedby="codeError" @enderror>
                                @error('code')
                                    <span class="wh-error" id="codeError"><x-lucide-circle-alert /> {{ $message }}</span>
                                @enderror
                            </div>

                            <div class="wh-field-block">
                                <label class="form-label" for="whName">
                                    Nama Gudang <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       id="whName"
                                       name="name"
                                       value="{{ old('name', $warehouse->name) }}"
                                       maxlength="255"
                                       class="form-control @error('name') is-invalid @enderror"
                                       placeholder="mis. Gudang Sentral Jakarta"
                                       required
                                       @error('name') aria-invalid="true" aria-describedby="nameError" @enderror>
                                @error('name')
                                    <span class="wh-error" id="nameError"><x-lucide-circle-alert /> {{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="wh-form-divider"></div>

                    <div class="wh-section">
                        <div class="wh-section-head">
                            <x-lucide-layers class="text-warning" />
                            <h2 class="wh-section-title">Tipe & Relasi Proyek</h2>
                        </div>

                        <div class="wh-field-block">
                            <span class="form-label">Tipe Gudang <span class="text-danger">*</span></span>
                            <div class="wh-type-options" role="radiogroup" aria-label="Tipe gudang">
                                <label class="wh-type-option">
                                    <input type="radio" name="type" value="central" @checked($currentType === 'central')>
                                    <span class="wh-type-option-top"><x-lucide-building-2 /> Gudang Pusat</span>
                                    <span class="wh-type-option-desc">Lokasi inventori utama dan operasional kantor pusat.</span>
                                </label>
                                <label class="wh-type-option">
                                    <input type="radio" name="type" value="project" @checked($currentType === 'project')>
                                    <span class="wh-type-option-top"><x-lucide-hard-hat /> Gudang Proyek</span>
                                    <span class="wh-type-option-desc">Lokasi material khusus untuk kebutuhan site proyek.</span>
                                </label>
                            </div>
                            @error('type')
                                <span class="wh-error"><x-lucide-circle-alert /> {{ $message }}</span>
                            @enderror
                        </div>

                        <div class="wh-field-block mt-3 {{ $currentType === 'project' ? '' : 'wh-is-hidden' }}" id="projectWrapper">
                            <label class="form-label" for="projectNameInput">
                                Terkait Proyek <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="projectNameInput"
                                   name="project_name"
                                   list="projectSuggestions"
                                   value="{{ old('project_name', $warehouse->project->name ?? '') }}"
                                   maxlength="255"
                                   class="form-control @error('project_name') is-invalid @enderror"
                                   placeholder="Pilih atau ketik nama proyek..."
                                   @error('project_name') aria-invalid="true" aria-describedby="projectNameError" @enderror>
                            <datalist id="projectSuggestions">
                                @foreach($projects as $project)
                                    <option value="{{ $project->name }}"></option>
                                @endforeach
                            </datalist>
                            <span class="wh-help">
                                @if($warehouse->project)
                                    Saat ini terhubung ke {{ $warehouse->project->name }}. Ubah nama untuk memindahkan gudang ke proyek lain.
                                @else
                                    Pilih proyek yang sudah ada atau ketik nama baru untuk dibuat otomatis.
                                @endif
                            </span>
                            @error('project_name')
                                <span class="wh-error" id="projectNameError"><x-lucide-circle-alert /> {{ $message }}</span>
                            @enderror
                        </div>

                        <div class="wh-field-block mt-3">
                            <span class="form-label">Status Operasional</span>
                            <label class="wh-switch" for="whActive">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" id="whActive" name="is_active" value="1" @checked($isActive)>
                                <span>Aktif dan siap digunakan</span>
                            </label>
                            <span class="wh-help">Gudang nonaktif tetap tersimpan, tetapi tidak dipakai untuk operasi baru.</span>
                        </div>
                    </div>

                    <div class="wh-form-divider"></div>

                    <div class="wh-section">
                        <div class="wh-section-head">
                            <x-lucide-map-pin class="text-danger" />
                            <h2 class="wh-section-title">Lokasi & Alamat</h2>
                        </div>

                        <div class="wh-field-block">
                            <div class="flex items-center justify-between">
                                <label class="form-label" for="whAddress">Alamat Lengkap</label>
                                <span class="wh-help" id="addressCount">0/500</span>
                            </div>
                            <textarea id="whAddress"
                                      name="address"
                                      rows="3"
                                      maxlength="500"
                                      class="form-control @error('address') is-invalid @enderror"
                                      placeholder="Alamat lengkap lokasi gudang atau instruksi akses pengiriman..."
                                      @error('address') aria-invalid="true" aria-describedby="addressError" @enderror>{{ old('address', $warehouse->address) }}</textarea>
                            @error('address')
                                <span class="wh-error" id="addressError"><x-lucide-circle-alert /> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="wh-form-actions">
                        <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">
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

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const typeInputs = document.querySelectorAll('input[name="type"]');
            const projectWrapper = document.getElementById('projectWrapper');
            const projectInput = document.getElementById('projectNameInput');
            const addressInput = document.getElementById('whAddress');
            const addressCount = document.getElementById('addressCount');

            function syncProjectField() {
                const selected = document.querySelector('input[name="type"]:checked');
                const isProject = selected !== null && selected.value === 'project';

                if (projectWrapper) {
                    projectWrapper.classList.toggle('wh-is-hidden', !isProject);
                }

                if (projectInput) {
                    projectInput.required = isProject;
                }
            }

            typeInputs.forEach(function (input) {
                input.addEventListener('change', syncProjectField);
            });

            if (addressInput && addressCount) {
                const updateCount = function () {
                    addressCount.textContent = addressInput.value.length + '/500';
                };

                addressInput.addEventListener('input', updateCount);
                updateCount();
            }

            syncProjectField();
        });
    </script>
    @endpush
</x-app-layout>
