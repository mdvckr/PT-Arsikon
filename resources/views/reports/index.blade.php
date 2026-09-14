<x-app-layout>
    <x-slot name="title">Laporan</x-slot>

    <div class="mb-4">
        <h2 class="fw-700" style="font-size:20px;color:#0f172a;">Laporan</h2>
        <p class="text-muted" style="font-size:13px;margin-top:2px;">Pusat rekapitulasi data stok material, riwayat mutasi, diskrepansi opname, dan status peralatan.</p>
    </div>

    <div class="grid grid-2" style="gap:18px;">

        {{-- Laporan Stok Material --}}
        <div class="card" style="display:flex;flex-direction:column;justify-content:space-between;">
            <div>
                <div class="card-header" style="display:flex;align-items:center;gap:12px;padding:16px 20px;">
                    <div style="width:36px;height:36px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#334155;flex-shrink:0;">
                        <i class="fas fa-boxes-stacked" style="font-size:15px;"></i>
                    </div>
                    <div>
                        <div class="card-title" style="font-size:15px;margin:0;">Laporan Stok Material</div>
                        <div class="text-muted" style="font-size:11.5px;margin-top:2px;">Rekapitulasi kuantitas & estimasi nilai inventori</div>
                    </div>
                </div>
                <div class="card-body" style="padding:20px;">
                    <p class="text-muted mb-3" style="font-size:13px;line-height:1.5;">
                        Menampilkan posisi stok terkini untuk setiap material per gudang, lengkap dengan batas minimum stok dan status ketersediaan.
                    </p>
                    <form method="GET" action="{{ route('reports.stock') }}">
                        <div class="mb-3">
                            <label class="form-label">Filter Gudang</label>
                            <select name="warehouse_id" class="form-control">
                                <option value="">Semua Gudang</option>
                                @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Filter Kategori</label>
                            <select name="category_id" class="form-control">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-full" style="justify-content:center;padding:9px 16px;">
                            Lihat Laporan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Laporan Mutasi Stok --}}
        <div class="card" style="display:flex;flex-direction:column;justify-content:space-between;">
            <div>
                <div class="card-header" style="display:flex;align-items:center;gap:12px;padding:16px 20px;">
                    <div style="width:36px;height:36px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#334155;flex-shrink:0;">
                        <i class="fas fa-arrow-right-arrow-left" style="font-size:15px;"></i>
                    </div>
                    <div>
                        <div class="card-title" style="font-size:15px;margin:0;">Laporan Mutasi Stok</div>
                        <div class="text-muted" style="font-size:11.5px;margin-top:2px;">Riwayat arus barang masuk & keluar</div>
                    </div>
                </div>
                <div class="card-body" style="padding:20px;">
                    <p class="text-muted mb-3" style="font-size:13px;line-height:1.5;">
                        Rekapitulasi riwayat mutasi barang masuk (inbound) dan barang keluar (outbound) berdasarkan periode tanggal transaksi.
                    </p>
                    <form method="GET" action="{{ route('reports.mutation') }}">
                        <div class="mb-3">
                            <label class="form-label">Filter Gudang</label>
                            <select name="warehouse_id" class="form-control">
                                <option value="">Semua Gudang</option>
                                @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-2 mb-4">
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
                        <button type="submit" class="btn btn-primary w-full" style="justify-content:center;padding:9px 16px;">
                            Lihat Laporan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Laporan Diskrepansi Opname --}}
        <div class="card" style="display:flex;flex-direction:column;justify-content:space-between;">
            <div>
                <div class="card-header" style="display:flex;align-items:center;gap:12px;padding:16px 20px;">
                    <div style="width:36px;height:36px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#334155;flex-shrink:0;">
                        <i class="fas fa-clipboard-check" style="font-size:15px;"></i>
                    </div>
                    <div>
                        <div class="card-title" style="font-size:15px;margin:0;">Laporan Diskrepansi Opname</div>
                        <div class="text-muted" style="font-size:11.5px;margin-top:2px;">Hasil audit stok sistem vs fisik</div>
                    </div>
                </div>
                <div class="card-body" style="padding:20px;">
                    <p class="text-muted mb-3" style="font-size:13px;line-height:1.5;">
                        Daftar selisih antara pencatatan stok sistem dan perhitungan fisik riil dari proses stock opname yang telah dilakukan.
                    </p>
                    <form method="GET" action="{{ route('reports.discrepancy') }}">
                        <div class="mb-4">
                            <label class="form-label">Filter Gudang</label>
                            <select name="warehouse_id" class="form-control">
                                <option value="">Semua Gudang</option>
                                @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="margin-top:48px;">
                            <button type="submit" class="btn btn-primary w-full" style="justify-content:center;padding:9px 16px;">
                                Lihat Laporan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Laporan Status Alat --}}
        <div class="card" style="display:flex;flex-direction:column;justify-content:space-between;">
            <div>
                <div class="card-header" style="display:flex;align-items:center;gap:12px;padding:16px 20px;">
                    <div style="width:36px;height:36px;border-radius:8px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#334155;flex-shrink:0;">
                        <i class="fas fa-toolbox" style="font-size:15px;"></i>
                    </div>
                    <div>
                        <div class="card-title" style="font-size:15px;margin:0;">Laporan Status Alat</div>
                        <div class="text-muted" style="font-size:11.5px;margin-top:2px;">Kondisi & ketersediaan inventori peralatan</div>
                    </div>
                </div>
                <div class="card-body" style="padding:20px;">
                    <p class="text-muted mb-3" style="font-size:13px;line-height:1.5;">
                        Rekapitulasi lengkap status pemakaian seluruh peralatan kerja: unit siap pakai (tersedia), sedang dipinjam proyek, maupun dalam perbaikan.
                    </p>
                    <div class="p-3 mb-4 rounded border" style="background:#f8fafc;font-size:12.5px;color:#475569;line-height:1.5;">
                        Laporan menyajikan ringkasan jumlah alat terdaftar, lokasi penyimpanan saat ini, serta status kondisi fisik alat.
                    </div>
                    <form method="GET" action="{{ route('reports.tools') }}">
                        <button type="submit" class="btn btn-primary w-full" style="justify-content:center;padding:9px 16px;">
                            Lihat Laporan
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
