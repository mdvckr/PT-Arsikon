<x-app-layout>
    <x-slot name="title">Data Proyek</x-slot>

    @push('styles')
        @include('projects.partials.styles')
    @endpush

    @php
        $canManageProjects = auth()->user()?->can('projects.manage')
            || auth()->user()?->hasAnyRole(['Owner', 'Admin Pusat']);
        $search = request('search');
        $statusFilter = request('status');
        $hasFilters = filled($search) || filled($statusFilter);
    @endphp

    <div class="proj-page">
        <header class="proj-header">
            <div class="proj-header-main">
                <div class="proj-header-icon">
                    <x-lucide-building-2 />
                </div>
                <div>
                    <h1 class="proj-header-title">Data Proyek Konstruksi</h1>
                    <p class="proj-header-subtitle">Kelola proyek, jadwal pelaksanaan, dan gudang site dalam satu tempat.</p>
                </div>
            </div>

            @if($canManageProjects)
            <div class="proj-header-actions">
                <a href="{{ route('projects.create') }}" class="btn btn-primary proj-btn-primary">
                    <x-lucide-circle-plus /> Tambah Proyek
                </a>
            </div>
            @endif
        </header>

        <nav class="proj-tabs" aria-label="Navigasi data master lokasi">
            <a href="{{ route('warehouses.index') }}" class="proj-tab">
                <x-lucide-warehouse />
                <span>Data Gudang</span>
                <span class="proj-tab-count">{{ number_format($warehouseCount, 0, ',', '.') }}</span>
            </a>
            <a href="{{ route('projects.index') }}" class="proj-tab is-active" aria-current="page">
                <x-lucide-building-2 />
                <span>Data Proyek</span>
                <span class="proj-tab-count">{{ number_format($stats['total'], 0, ',', '.') }}</span>
            </a>
        </nav>

        <section class="proj-kpi-grid" aria-label="Ringkasan proyek">
            <a href="{{ route('projects.index', array_filter(['search' => $search])) }}"
               class="proj-kpi {{ filled($statusFilter) ? '' : 'is-active' }}"
               data-animate="kpi">
                <span class="proj-kpi-icon is-total"><x-lucide-folder-kanban /></span>
                <span class="proj-kpi-body">
                    <span class="proj-kpi-label">Total Proyek</span>
                    <span class="proj-kpi-value" data-counter>{{ number_format($stats['total'], 0, ',', '.') }}</span>
                </span>
            </a>

            <a href="{{ route('projects.index', array_filter(['search' => $search, 'status' => 'active'])) }}"
               class="proj-kpi {{ $statusFilter === 'active' ? 'is-active' : '' }}"
               data-animate="kpi">
                <span class="proj-kpi-icon is-active"><x-lucide-activity /></span>
                <span class="proj-kpi-body">
                    <span class="proj-kpi-label">Berjalan</span>
                    <span class="proj-kpi-value" data-counter>{{ number_format($stats['active'], 0, ',', '.') }}</span>
                </span>
            </a>

            <a href="{{ route('projects.index', array_filter(['search' => $search, 'status' => 'planning'])) }}"
               class="proj-kpi {{ $statusFilter === 'planning' ? 'is-active' : '' }}"
               data-animate="kpi">
                <span class="proj-kpi-icon is-planning"><x-lucide-clock /></span>
                <span class="proj-kpi-body">
                    <span class="proj-kpi-label">Perencanaan</span>
                    <span class="proj-kpi-value" data-counter>{{ number_format($stats['planning'], 0, ',', '.') }}</span>
                </span>
            </a>

            <a href="{{ route('projects.index', array_filter(['search' => $search, 'status' => 'completed'])) }}"
               class="proj-kpi {{ $statusFilter === 'completed' ? 'is-active' : '' }}"
               data-animate="kpi">
                <span class="proj-kpi-icon is-completed"><x-lucide-circle-check /></span>
                <span class="proj-kpi-body">
                    <span class="proj-kpi-label">Selesai</span>
                    <span class="proj-kpi-value" data-counter>{{ number_format($stats['completed'], 0, ',', '.') }}</span>
                </span>
            </a>
        </section>

        <section class="card proj-filter-card" aria-label="Filter data proyek">
            <form method="GET" action="{{ route('projects.index') }}" class="proj-filter-form">
                <div class="proj-field proj-field-search">
                    <label class="form-label" for="projectSearch">Cari Proyek</label>
                    <div class="input-icon-wrap">
                        <x-lucide-search class="input-icon" />
                        <input type="search"
                               id="projectSearch"
                               name="search"
                               value="{{ $search }}"
                               class="form-control with-icon"
                               placeholder="Nama, kode, lokasi, atau deskripsi proyek...">
                    </div>
                </div>

                <div class="proj-field">
                    <label class="form-label" for="projectStatusFilter">Status</label>
                    <div class="input-icon-wrap">
                        <x-lucide-list-filter class="input-icon" />
                        <select id="projectStatusFilter" name="status" class="form-control with-icon">
                            <option value="">Semua Status</option>
                            <option value="planning" @selected($statusFilter === 'planning')>Perencanaan</option>
                            <option value="active" @selected($statusFilter === 'active')>Berjalan</option>
                            <option value="completed" @selected($statusFilter === 'completed')>Selesai</option>
                            <option value="on_hold" @selected($statusFilter === 'on_hold')>Ditangguhkan</option>
                        </select>
                    </div>
                </div>

                <div class="proj-filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <x-lucide-filter /> Terapkan
                    </button>
                    @if($hasFilters)
                    <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                        <x-lucide-rotate-ccw /> Reset
                    </a>
                    @endif
                </div>
            </form>
        </section>

        <section class="card proj-table-card" aria-label="Daftar proyek">
            <div class="table-wrap">
                <table class="data-table proj-table">
                    <thead>
                        <tr>
                            <th>Proyek</th>
                            <th class="proj-col-location">Lokasi</th>
                            <th class="proj-col-dates">Jadwal Pelaksanaan</th>
                            <th>Gudang</th>
                            <th>Status</th>
                            <th class="proj-th-actions">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $proj)
                        <tr>
                            <td>
                                <div class="proj-identity">
                                    <span class="proj-identity-icon"><x-lucide-building-2 /></span>
                                    <span class="proj-identity-body">
                                        <span class="proj-identity-name">{{ $proj->name }}</span>
                                        <span class="proj-identity-code">
                                            <x-lucide-barcode /> {{ $proj->code ?: 'Tanpa kode' }}
                                        </span>
                                        @if(filled($proj->description))
                                            <span class="proj-identity-desc">{{ \Illuminate\Support\Str::limit($proj->description, 90) }}</span>
                                        @else
                                            <span class="proj-identity-desc is-empty">Belum ada deskripsi proyek</span>
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="proj-col-location">
                                <span class="proj-location">
                                    <x-lucide-map-pin />
                                    <span>{{ $proj->location ?: 'Belum ditentukan' }}</span>
                                </span>
                            </td>
                            <td class="proj-col-dates">
                                <div class="proj-dates">
                                    <span><x-lucide-calendar-days /> {{ $proj->start_date?->format('d M Y') ?? 'Belum dijadwalkan' }}</span>
                                    <span class="proj-dates-sep">s.d.</span>
                                    <span><x-lucide-flag /> {{ $proj->end_date?->format('d M Y') ?? 'Belum ditentukan' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-info proj-wh-badge">
                                    <x-lucide-warehouse /> {{ $proj->warehouses_count }} Gudang
                                </span>
                            </td>
                            <td>
                                <x-project-status-badge :status="$proj->status" />
                            </td>
                            <td>
                                <div class="proj-row-actions">
                                    @if($canManageProjects)
                                    <a href="{{ route('projects.edit', $proj) }}"
                                       class="btn btn-sm btn-secondary btn-icon"
                                       title="Edit proyek"
                                       aria-label="Edit proyek {{ $proj->name }}">
                                        <x-lucide-square-pen />
                                    </a>
                                    <form method="POST"
                                          action="{{ route('projects.destroy', $proj) }}"
                                          data-confirm="Hapus proyek {{ addslashes($proj->name) }} dari sistem?"
                                          data-confirm-title="Konfirmasi Hapus Proyek"
                                          data-confirm-btn="Hapus Proyek"
                                          data-confirm-danger="true">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger btn-icon"
                                                title="Hapus proyek"
                                                aria-label="Hapus proyek {{ $proj->name }}">
                                            <x-lucide-trash-2 />
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6">
                                <div class="proj-empty">
                                    <span class="proj-empty-icon"><x-lucide-folder-search /></span>
                                    @if($hasFilters)
                                        <h3>Tidak ada proyek yang cocok</h3>
                                        <p>Kata kunci atau status yang dipilih tidak menemukan proyek. Coba ubah filter atau bersihkan pencarian.</p>
                                        <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                                            <x-lucide-rotate-ccw /> Bersihkan Filter
                                        </a>
                                    @else
                                        <h3>Belum ada data proyek</h3>
                                        <p>Tambahkan proyek pertama untuk mulai mengelola jadwal pelaksanaan dan gudang site.</p>
                                        @if($canManageProjects)
                                        <a href="{{ route('projects.create') }}" class="btn btn-primary">
                                            <x-lucide-circle-plus /> Tambah Proyek Pertama
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

            @if($projects->hasPages())
            <div class="proj-pagination">
                {{ $projects->links() }}
            </div>
            @endif
        </section>
    </div>
</x-app-layout>
