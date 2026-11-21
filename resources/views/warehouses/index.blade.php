<x-app-layout>
    <x-slot name="title">Data Gudang & Lokasi</x-slot>

    @push('styles')
        @include('warehouses.partials.styles')
    @endpush

    @php
        $canManage = auth()->user()?->can('warehouses.manage')
            || auth()->user()?->hasAnyRole(['Owner', 'Admin Pusat']);
        $canOpenProjects = auth()->user()?->can('projects.manage')
            || auth()->user()?->hasAnyRole(['Owner', 'Admin Pusat']);
        $search = request('search');
        $typeFilter = request('type');
        $statusFilter = request('status');
        $hasFilters = filled($search) || filled($typeFilter) || filled($statusFilter);
    @endphp

    <div class="wh-page">
        <header class="wh-header">
            <div class="wh-header-main">
                <div class="wh-header-icon">
                    <x-lucide-warehouse />
                </div>
                <div>
                    <h1 class="wh-header-title">Data Gudang & Lokasi</h1>
                    <p class="wh-header-subtitle">Kelola gudang pusat dan gudang site proyek, termasuk status operasional dan isi inventori.</p>
                </div>
            </div>

            @if($canManage)
            <div class="wh-header-actions">
                <a href="{{ route('warehouses.create') }}" class="btn btn-primary wh-btn-primary">
                    <x-lucide-circle-plus /> Tambah Gudang
                </a>
                <a href="{{ route('projects.create') }}" class="btn btn-secondary wh-btn-secondary">
                    <x-lucide-folder-plus /> Tambah Proyek
                </a>
            </div>
            @endif
        </header>

        <nav class="wh-tabs" aria-label="Navigasi data master lokasi">
            <a href="{{ route('warehouses.index') }}" class="wh-tab is-active" aria-current="page">
                <x-lucide-warehouse />
                <span>Data Gudang</span>
                <span class="wh-tab-count">{{ number_format($warehouseCount, 0, ',', '.') }}</span>
            </a>
            <a href="{{ route('projects.index') }}" class="wh-tab">
                <x-lucide-building-2 />
                <span>Data Proyek</span>
                <span class="wh-tab-count">{{ number_format($projectCount, 0, ',', '.') }}</span>
            </a>
        </nav>

        <section class="card wh-filter-card" aria-label="Filter data gudang">
            <form method="GET" action="{{ route('warehouses.index') }}" class="wh-filter-form">
                <div class="wh-field wh-field-search">
                    <label class="form-label" for="warehouseSearch">Cari Gudang</label>
                    <div class="wh-input-wrap">
                        <x-lucide-search class="wh-input-icon" />
                        <input type="search"
                               id="warehouseSearch"
                               name="search"
                               value="{{ $search }}"
                               class="form-control with-icon"
                               placeholder="Nama, kode, alamat, atau proyek...">
                    </div>
                </div>

                <div class="wh-field">
                    <label class="form-label" for="warehouseTypeFilter">Tipe Gudang</label>
                    <div class="wh-input-wrap">
                        <x-lucide-layers class="wh-input-icon" />
                        <select id="warehouseTypeFilter" name="type" class="form-control with-icon">
                            <option value="">Semua Tipe</option>
                            <option value="central" @selected(in_array($typeFilter, ['central', 'main', 'pusat'], true))>Gudang Pusat</option>
                            <option value="project" @selected($typeFilter === 'project')>Gudang Proyek</option>
                        </select>
                    </div>
                </div>

                <div class="wh-field">
                    <label class="form-label" for="warehouseStatusFilter">Status</label>
                    <div class="wh-input-wrap">
                        <x-lucide-toggle-right class="wh-input-icon" />
                        <select id="warehouseStatusFilter" name="status" class="form-control with-icon">
                            <option value="">Semua Status</option>
                            <option value="active" @selected($statusFilter === 'active')>Aktif</option>
                            <option value="inactive" @selected($statusFilter === 'inactive')>Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="wh-filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <x-lucide-filter /> Terapkan
                    </button>
                    @if($hasFilters)
                    <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">
                        <x-lucide-rotate-ccw /> Reset
                    </a>
                    @endif
                </div>
            </form>
        </section>

        <section class="card wh-table-card" aria-label="Daftar gudang">
            <div class="wh-table-head">
                <div>
                    <h2 class="wh-table-title">Daftar Gudang</h2>
                    <p class="wh-table-sub">
                        @if($warehouses->total() > 0)
                            Menampilkan {{ number_format($warehouses->firstItem(), 0, ',', '.') }}–{{ number_format($warehouses->lastItem(), 0, ',', '.') }} dari {{ number_format($warehouses->total(), 0, ',', '.') }} gudang
                        @else
                            Tidak ada gudang untuk ditampilkan
                        @endif
                    </p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="data-table wh-table" aria-label="Daftar gudang dan lokasi">
                    <thead>
                        <tr>
                            <th>Gudang</th>
                            <th class="wh-col-type">Tipe</th>
                            <th class="wh-col-project">Proyek</th>
                            <th class="wh-col-address">Alamat</th>
                            <th class="wh-col-inventory">Inventori</th>
                            <th>Status</th>
                            <th class="wh-th-actions">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warehouses as $wh)
                        @php
                            $isCentral = $wh->isCentral();
                            $adminActive = $wh->isAdministrativelyActive();
                            $deleteBlocked = ($wh->active_stock_count ?? 0) > 0 || ($wh->users_count ?? 0) > 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="wh-identity">
                                    <span class="wh-identity-icon {{ $isCentral ? 'is-central' : 'is-project' }}">
                                        @if($isCentral)
                                            <x-lucide-building-2 />
                                        @else
                                            <x-lucide-hard-hat />
                                        @endif
                                    </span>
                                    <span class="wh-identity-body">
                                        <span class="wh-identity-name">{{ $wh->name }}</span>
                                        <span class="wh-identity-code">
                                            <x-lucide-barcode /> {{ $wh->code ?: 'Tanpa kode' }}
                                        </span>
                                        <span class="wh-identity-meta">
                                            <span><x-lucide-users /> {{ $wh->users_count }} user</span>
                                        </span>
                                        <span class="wh-mobile-meta">
                                            <x-warehouse-type-badge :central="$isCentral" />
                                            @if($wh->project)
                                                <span class="wh-mobile-item"><x-lucide-folder-open /> {{ $wh->project->name }}</span>
                                            @endif
                                            <span class="wh-mobile-item"><x-lucide-package /> {{ $wh->inventories_count }} jenis</span>
                                        </span>
                                    </span>
                                </div>
                            </td>
                            <td class="wh-col-type">
                                <x-warehouse-type-badge :central="$isCentral" />
                            </td>
                            <td class="wh-col-project">
                                @if($wh->project)
                                    @if($canOpenProjects)
                                    <a href="{{ route('projects.edit', $wh->project) }}" class="wh-project" title="Buka data proyek {{ $wh->project->name }}">
                                        <x-lucide-folder-open /> {{ $wh->project->name }}
                                    </a>
                                    @else
                                    <span class="wh-project"><x-lucide-folder-open /> {{ $wh->project->name }}</span>
                                    @endif
                                @elseif($isCentral)
                                    <span class="wh-muted">Kantor pusat</span>
                                @else
                                    <span class="wh-muted">Belum terhubung</span>
                                @endif
                            </td>
                            <td class="wh-col-address">
                                @if(filled($wh->address))
                                    <span class="wh-address">
                                        <x-lucide-map-pin />
                                        <span>{{ \Illuminate\Support\Str::limit($wh->address, 80) }}</span>
                                    </span>
                                @else
                                    <span class="wh-muted">Belum diisi</span>
                                @endif
                            </td>
                            <td class="wh-col-inventory">
                                <div class="wh-inventory">
                                    @if($wh->inventories_count > 0)
                                        <span class="wh-inventory-main"><x-lucide-package /> {{ $wh->inventories_count }} jenis</span>
                                    @else
                                        <span class="wh-muted">Belum ada stok</span>
                                    @endif
                                    @if(($wh->low_stock_count ?? 0) > 0)
                                        <span class="badge badge-warning wh-low-stock">
                                            <x-lucide-triangle-alert /> {{ $wh->low_stock_count }} menipis
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="wh-status">
                                    <x-warehouse-status-badge :active="$adminActive" />
                                    @if($isCentral)
                                        <span class="wh-status-sub">Operasional pusat</span>
                                    @elseif($wh->project)
                                        <span class="wh-status-sub">{{ $wh->project_phase_label }}</span>
                                    @else
                                        <span class="wh-status-sub">Belum terhubung ke proyek</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="wh-row-actions">
                                    @if($canManage)
                                    <a href="{{ route('warehouses.edit', $wh) }}"
                                       class="btn btn-sm btn-secondary btn-icon"
                                       title="Edit gudang"
                                       aria-label="Edit gudang {{ $wh->name }}">
                                        <x-lucide-square-pen />
                                    </a>

                                    @if($deleteBlocked)
                                    <button type="button"
                                            class="btn btn-sm btn-secondary btn-icon is-disabled"
                                            disabled
                                            title="Tidak dapat dihapus: {{ ($wh->active_stock_count ?? 0) > 0 ? $wh->active_stock_count.' item stok aktif' : $wh->users_count.' user ditugaskan' }}"
                                            aria-label="Gudang {{ $wh->name }} tidak dapat dihapus karena masih memiliki data terkait">
                                        <x-lucide-lock />
                                    </button>
                                    @else
                                    <form method="POST"
                                          action="{{ route('warehouses.destroy', $wh) }}"
                                          data-confirm="Hapus gudang {{ addslashes($wh->name) }}?"
                                          data-confirm-title="Konfirmasi Hapus Gudang"
                                          data-confirm-btn="Hapus Gudang"
                                          data-confirm-danger="true">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger btn-icon"
                                                title="Hapus gudang"
                                                aria-label="Hapus gudang {{ $wh->name }}">
                                            <x-lucide-trash-2 />
                                        </button>
                                    </form>
                                    @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="wh-empty">
                                    <span class="wh-empty-icon"><x-lucide-warehouse /></span>
                                    @if($hasFilters)
                                        <h3>Tidak ada gudang yang cocok</h3>
                                        <p>Kata kunci, tipe, atau status yang dipilih tidak menemukan gudang. Coba ubah filter atau bersihkan pencarian.</p>
                                        <a href="{{ route('warehouses.index') }}" class="btn btn-secondary">
                                            <x-lucide-rotate-ccw /> Bersihkan Filter
                                        </a>
                                    @else
                                        <h3>Belum ada data gudang</h3>
                                        <p>Tambahkan gudang pertama untuk mulai mengelola lokasi pusat dan site proyek.</p>
                                        @if($canManage)
                                        <a href="{{ route('warehouses.create') }}" class="btn btn-primary">
                                            <x-lucide-circle-plus /> Tambah Gudang Pertama
                                        </a>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($warehouses->hasPages())
            <div class="wh-pagination">
                {{ $warehouses->links() }}
            </div>
            @endif
        </section>
    </div>
</x-app-layout>
