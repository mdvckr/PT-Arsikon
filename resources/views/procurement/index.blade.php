<x-app-layout>
    <x-slot name="title">Permintaan Pengadaan</x-slot>

    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 style="font-size:20px;font-weight:700;color:#0f172a;">Permintaan Pengadaan (PR)</h2>
            <p class="text-muted" style="font-size:13px;">Manajemen permintaan pengadaan material</p>
        </div>
        @can("create procurement")
        <a href="{{ route("procurement.create") }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Buat PR Baru
        </a>
        @endcan
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Daftar Permintaan Pengadaan</span>
            <form method="GET" class="flex gap-2 ms-auto">
                <select name="status" class="form-control" style="width:160px;padding:6px 10px;font-size:13px;" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="draft" {{ request("status")==="draft"?"selected":"" }}>Draft</option>
                    <option value="submitted" {{ request("status")==="submitted"?"selected":"" }}>Diajukan</option>
                    <option value="approved" {{ request("status")==="approved"?"selected":"" }}>Disetujui</option>
                    <option value="rejected" {{ request("status")==="rejected"?"selected":"" }}>Ditolak</option>
                    <option value="po_created" {{ request("status")==="po_created"?"selected":"" }}>PO Dibuat</option>
                </select>
                <input type="text" name="search" value="{{ request("search") }}" placeholder="Cari nomor PR..." class="form-control" style="width:200px;padding:6px 10px;font-size:13px;">
                <button class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr>
                    <th>#</th><th>No. PR</th><th>Diajukan Oleh</th><th>Dibutuhkan</th><th>Jml Item</th><th>Status</th><th>Dibuat</th><th>Aksi</th>
                </tr></thead>
                <tbody>
                    @forelse($procurements as $i => $pr)
                    <tr>
                        <td class="text-muted">{{ $procurements->firstItem() + $i }}</td>
                        <td><code style="background:#f1f5f9;padding:2px 7px;border-radius:5px;font-size:12px;">{{ $pr->pr_number }}</code></td>
                        <td>{{ $pr->requester?->name ?? "-" }}</td>
                        <td>{{ $pr->needed_by?->format("d M Y") ?? "-" }}</td>
                        <td><span class="badge badge-primary">{{ $pr->items->count() }} item</span></td>
                        <td><span class="badge badge-{{ $pr->status_color }}">{{ $pr->status_label }}</span></td>
                        <td class="text-muted" style="font-size:12px;">{{ $pr->created_at->format("d M Y") }}</td>
                        <td>
                            <a href="{{ route("procurement.show", $pr) }}" class="btn btn-sm btn-secondary btn-icon" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8"><div class="empty-state"><i class="fas fa-clipboard-list"></i><h3>Belum ada permintaan pengadaan</h3></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($procurements->hasPages())
        <div style="padding:16px 22px;border-top:1px solid #f1f5f9;">{{ $procurements->links() }}</div>
        @endif
    </div>
</x-app-layout>
