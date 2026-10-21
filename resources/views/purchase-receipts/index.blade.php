<x-app-layout>
    <x-slot name="title">Nota Pembelian Harian</x-slot>

    <div class="flex justify-between items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 style="font-size:20px;font-weight:700;color:#0f172a;">Pencatatan Nota Pembelian Harian</h2>
            <p class="text-muted" style="font-size:13px;">Daftar belanja nota fisik harian di proyek, dikelompokkan per 1 minggu & di dalamnya per hari.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('purchase-receipts.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-1"></i> Catat Nota Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4 flex items-center justify-between" style="background:#ecfdf5;border:1px solid #10b981;color:#065f46;padding:12px 16px;border-radius:8px;">
            <span><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-4 flex items-center justify-between" style="background:#fef2f2;border:1px solid #ef4444;color:#991b1b;padding:12px 16px;border-radius:8px;">
            <span><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="card mb-4">
        <div class="card-header flex flex-wrap gap-3 items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="card-title font-semibold" style="font-size:14px;">
                    <i class="fas fa-filter text-primary me-1"></i> Filter Nota
                </span>
                @if($groupedWeeks->count() > 0)
                <div class="flex items-center gap-1 ms-2">
                    <button type="button" onclick="toggleAllWeeks(true)" class="btn btn-light btn-xs border" style="font-size:11px;padding:3px 8px;font-weight:600;">
                        <i class="fas fa-angles-down me-1"></i> Buka Semua
                    </button>
                    <button type="button" onclick="toggleAllWeeks(false)" class="btn btn-light btn-xs border" style="font-size:11px;padding:3px 8px;font-weight:600;">
                        <i class="fas fa-angles-up me-1"></i> Tutup Semua
                    </button>
                </div>
                @endif
            </div>

            <form method="GET" class="flex gap-2 ms-auto flex-wrap">
                <select name="project_id" class="form-control" style="width:200px;padding:6px 10px;font-size:13px;" onchange="this.form.submit()">
                    <option value="">-- Semua Proyek --</option>
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                            {{ $proj->name }}
                        </option>
                    @endforeach
                </select>
                <select name="payment_status" class="form-control" style="width:160px;padding:6px 10px;font-size:13px;" onchange="this.form.submit()">
                    <option value="">-- Status Bayar --</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>✅ Lunas (Instan)</option>
                    <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>⏳ Belum Dibayar</option>
                </select>
                <input type="date" name="date" value="{{ request('date') }}" class="form-control" style="width:160px;padding:6px 10px;font-size:13px;" onchange="this.form.submit()">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor nota / supplier..." class="form-control" style="width:210px;padding:6px 10px;font-size:13px;">
                <button class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
                @if(request('search') || request('project_id') || request('date') || request('payment_status'))
                    <a href="{{ route('purchase-receipts.index') }}" class="btn btn-light btn-sm" title="Reset Filter"><i class="fas fa-undo"></i></a>
                @endif
            </form>
        </div>
    </div>

    {{-- Grouped View: Per 1 Minggu -> Di dalamnya Per Hari --}}
    @forelse($groupedWeeks as $weekIdx => $week)
        <div class="card mb-4 week-card" style="border:1.5px solid #cbd5e1;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.04);">
            
            {{-- Header Minggu (Clickable Accordion) --}}
            <div onclick="toggleWeek('{{ $weekIdx }}')" 
                 style="background:linear-gradient(135deg, #0f172a 0%, #1e293b 100%);color:#ffffff;padding:14px 20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;cursor:pointer;user-select:none;">
                
                <div class="flex items-center gap-3">
                    <div style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,0.12);display:flex;align-items:center;justify-content:center;font-size:16px;">
                        <i class="fas fa-calendar-week text-emerald-400"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 style="font-size:15.5px;font-weight:700;margin:0;letter-spacing:-0.2px;color:#ffffff;">
                                {{ $week->week_label }}
                            </h3>
                            <span class="badge" style="background:#047857;color:#a7f3d0;border:1px solid #059669;font-size:10.5px;padding:2px 8px;">
                                Periode 1 Minggu
                            </span>
                        </div>
                        <span style="font-size:11.5px;color:#94a3b8;">
                            Total {{ count($week->days) }} hari belanja tercatat pada minggu ini &bull; Klik untuk buka/tutup
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span style="background:rgba(255,255,255,0.12);color:#e2e8f0;padding:5px 12px;border-radius:6px;font-size:12px;font-weight:600;">
                        <i class="fas fa-file-invoice me-1"></i> {{ $week->count }} Nota
                    </span>
                    <span style="background:#10b981;color:#ffffff;padding:5px 14px;border-radius:6px;font-size:13px;font-weight:800;letter-spacing:0.2px;">
                        Total: Rp {{ number_format($week->total, 0, ',', '.') }}
                    </span>
                    <i class="fas fa-chevron-down week-icon" id="week-icon-{{ $weekIdx }}" style="font-size:14px;color:#94a3b8;transition:transform 0.2s;"></i>
                </div>
            </div>

            {{-- Isi Minggu: Hari-Hari di Dalam Minggu Tersebut --}}
            <div id="week-content-{{ $weekIdx }}" class="week-content" style="display:block;">
                @foreach($week->days as $dayIdx => $day)
                    <div style="border-bottom:1px solid #e2e8f0;">
                        
                        {{-- Subheader Hari --}}
                        <div style="background:#f1f5f9;padding:11px 20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;border-top:1px solid #e2e8f0;">
                            <div class="flex items-center gap-2">
                                <i class="far fa-calendar-alt text-emerald-600" style="font-size:16px;"></i>
                                <strong style="font-size:13.5px;color:#0f172a;">📅 Hari: {{ $day->date_label }}</strong>
                                <span class="badge badge-secondary" style="font-size:11px;padding:2px 8px;">{{ $day->count }} nota</span>
                            </div>
                            <div style="font-size:13px;font-weight:700;color:#334155;">
                                Subtotal Hari: <strong class="text-success" style="font-size:14px;">Rp {{ number_format($day->total, 0, ',', '.') }}</strong>
                            </div>
                        </div>

                        {{-- Tabel Nota Hari Ini --}}
                        <div class="table-wrap">
                            <table class="data-table" style="font-size:13px;margin:0;">
                                <thead>
                                    <tr style="background:#ffffff;border-bottom:2px solid #e2e8f0;">
                                        <th style="width:40px;text-align:center;">No</th>
                                        <th>No. Nota Fisik</th>
                                        <th>Proyek</th>
                                        <th>Supplier / Toko</th>
                                        <th>Item Belanja</th>
                                        <th>Status Pembayaran</th>
                                        <th class="text-end">Total Belanja</th>
                                        <th>Bukti Nota</th>
                                        <th>Dicatat Oleh</th>
                                        <th class="text-center" style="width:90px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($day->receipts as $idx => $receipt)
                                    <tr>
                                        <td class="text-muted text-center">{{ $idx + 1 }}</td>
                                        <td>
                                            <code>{{ $receipt->receipt_number ?? '-' }}</code>
                                        </td>
                                        <td>
                                            @if($receipt->project)
                                                <strong class="text-primary">{{ $receipt->project->name }}</strong>
                                            @elseif($receipt->project_name)
                                                <span>{{ $receipt->project_name }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td><strong>{{ $receipt->supplier_name ?? $receipt->supplier?->name ?? '-' }}</strong></td>
                                        <td>
                                            <div class="flex items-center gap-1">
                                                <span class="badge badge-secondary" style="font-size:11px;">{{ $receipt->items->count() }} item</span>
                                                @if($receipt->items->count() > 0)
                                                    <span class="text-muted text-xs truncate" style="max-width:140px;" title="{{ $receipt->items->pluck('item_name')->join(', ') }}">
                                                        ({{ $receipt->items->first()->item_name }}@if($receipt->items->count() > 1)...@endif)
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($receipt->payment_status === 'paid')
                                                <span class="badge" style="background:#dcfce7;color:#15803d;border:1px solid #86efac;font-size:11.5px;font-weight:700;">
                                                    <i class="fas fa-bolt me-1"></i> Lunas (Instan)
                                                </span>
                                            @else
                                                <span class="badge badge-{{ $receipt->status_color }}">{{ $receipt->status_label }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-success" style="font-size:13.5px;">
                                                Rp {{ number_format($receipt->total_amount, 0, ',', '.') }}
                                            </strong>
                                        </td>
                                        <td>
                                            @if($receipt->image_path)
                                                <a href="{{ asset('storage/' . $receipt->image_path) }}" target="_blank" class="btn btn-xs btn-outline-info" style="font-size:11px;padding:3px 8px;">
                                                    <i class="fas fa-file-image me-1"></i> Lihat Foto
                                                </a>
                                            @else
                                                <span class="text-muted" style="font-size:11.5px;">Tanpa Foto</span>
                                            @endif
                                        </td>
                                        <td class="text-muted" style="font-size:12px;">{{ $receipt->creator?->name ?? '-' }}</td>
                                        <td class="text-nowrap text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                <a href="{{ route('purchase-receipts.show', $receipt) }}" class="btn btn-sm btn-secondary btn-icon" title="Detail Nota">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @can('delete purchase receipts')
                                                    <form action="{{ route('purchase-receipts.destroy', $receipt) }}" method="POST" class="inline" onsubmit="return confirm('Hapus nota {{ $receipt->receipt_number ?? '#'.$receipt->id }}? Saldo kas operasional proyek akan otomatis dikembalikan (refund).')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" title="Hapus Nota (Refund Saldo)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="card p-8 text-center text-muted" style="padding:48px 20px;">
            <i class="fas fa-receipt text-gray-300 mb-3" style="font-size:44px;"></i>
            <h4 style="font-size:16px;font-weight:600;color:#334155;">Belum Ada Pencatatan Nota Pembelian Harian</h4>
            <p class="text-muted text-sm mt-1">Gunakan tombol "Catat Nota Baru" di atas untuk menambahkan nota belanja harian proyek.</p>
        </div>
    @endforelse

    @if($receipts->hasPages())
        <div class="card p-3 mb-4">
            {{ $receipts->links() }}
        </div>
    @endif

    @push('scripts')
    <script>
        function toggleWeek(idx) {
            const content = document.getElementById('week-content-' + idx);
            const icon = document.getElementById('week-icon-' + idx);
            if (!content) return;
            const isHidden = (content.style.display === 'none');
            content.style.display = isHidden ? 'block' : 'none';
            if (icon) {
                icon.style.transform = isHidden ? 'rotate(0deg)' : 'rotate(-90deg)';
            }
        }

        function toggleAllWeeks(expand) {
            document.querySelectorAll('.week-content').forEach(c => {
                c.style.display = expand ? 'block' : 'none';
            });
            document.querySelectorAll('.week-icon').forEach(icon => {
                icon.style.transform = expand ? 'rotate(0deg)' : 'rotate(-90deg)';
            });
        }
    </script>
    @endpush
</x-app-layout>
