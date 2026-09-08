<x-app-layout>
    <x-slot name="title">Laporan</x-slot>

    <div class="mb-4">
        <h2 class="fw-700" style="font-size:20px;">Laporan & Analitik</h2>
        <p class="text-muted" style="font-size:13px;margin-top:2px;">Pilih jenis laporan yang ingin dihasilkan</p>
    </div>

    <div class="grid grid-2" style="gap:16px;">

        {{-- Stock Report --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-layer-group text-primary"></i>
                <span class="card-title">Laporan Stok</span>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3" style="font-size:13px;">Rekap stok material per gudang, termasuk item di bawah minimum.</p>
                <form method="GET" action="{{ route('reports.stock') }}">
                    <div class="mb-3">
                        <label class="form-label">Gudang</label>
                        <select name="warehouse_id" class="form-control">
                            <option value="">Semua Gudang</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-control">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">
                        <i class="fas fa-chart-bar"></i> Tampilkan Laporan
                    </button>
                </form>
            </div>
        </div>

        {{-- Mutation Report --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-arrows-up-down text-primary"></i>
                <span class="card-title">Laporan Mutasi Stok</span>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3" style="font-size:13px;">Rekap pergerakan stok (masuk/keluar) dalam rentang waktu tertentu.</p>
                <form method="GET" action="{{ route('reports.mutation') }}">
                    <div class="mb-3">
                        <label class="form-label">Gudang</label>
                        <select name="warehouse_id" class="form-control">
                            <option value="">Semua Gudang</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-2 mb-3">
                        <div>
                            <label class="form-label">Dari Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="date_from" class="form-control"
                                value="{{ date('Y-m-01') }}" required>
                        </div>
                        <div>
                            <label class="form-label">Sampai Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="date_to" class="form-control"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">
                        <i class="fas fa-chart-line"></i> Tampilkan Laporan
                    </button>
                </form>
            </div>
        </div>

        {{-- Discrepancy Report --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-triangle-exclamation text-warning"></i>
                <span class="card-title">Laporan Diskrepansi Stok Opname</span>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3" style="font-size:13px;">Perbandingan stok sistem vs fisik dari Stock Opname yang sudah dikonfirmasi.</p>
                <form method="GET" action="{{ route('reports.discrepancy') }}">
                    <div class="mb-3">
                        <label class="form-label">Gudang</label>
                        <select name="warehouse_id" class="form-control">
                            <option value="">Semua Gudang</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-warning w-full" style="justify-content:center;">
                        <i class="fas fa-magnifying-glass-chart"></i> Tampilkan Laporan
                    </button>
                </form>
            </div>
        </div>

        {{-- Tool Report --}}
        <div class="card">
            <div class="card-header">
                <i class="fas fa-screwdriver-wrench text-primary"></i>
                <span class="card-title">Laporan Status Alat</span>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3" style="font-size:13px;">Rekap status semua alat: tersedia, dipinjam, maintenance, dan riwayat peminjaman.</p>
                <form method="GET" action="{{ route('reports.tools') }}">
                    <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">
                        <i class="fas fa-chart-pie"></i> Tampilkan Laporan
                    </button>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
