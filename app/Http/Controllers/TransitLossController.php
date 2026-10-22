<?php

namespace App\Http\Controllers;

use App\Models\TransitLoss;
use App\Services\TransitLossService;
use DomainException;
use Exception;
use Illuminate\Http\Request;

class TransitLossController extends Controller
{
    public function __construct(private TransitLossService $service) {}

    /**
     * Tampilkan daftar laporan kehilangan barang di perjalanan.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->can('create transit losses') && !$user->can('approve transit losses') && !$user->hasRole('Owner')) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat laporan kehilangan barang.');
        }

        $query = TransitLoss::with([
            'goodsReceipt',
            'goodsReceiptItem.material.unit',
            'goodsReceiptItem.tool',
            'purchaseOrder.supplier',
            'recordedByUser',
            'approvedByUser',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereHas('goodsReceipt', fn($gr) => $gr->where('receipt_number', 'like', "%{$search}%"))
                  ->orWhereHas('purchaseOrder', fn($po) => $po->where('po_number', 'like', "%{$search}%"))
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $transitLosses = $query->latest()->paginate(15)->withQueryString();

        return view('transit-losses.index', compact('transitLosses'));
    }

    /**
     * Catat laporan kehilangan barang (Aksi "Nyatakan Hilang").
     */
    public function store(Request $request)
    {
        $this->authorize('create transit losses');

        $validated = $request->validate([
            'goods_receipt_item_id' => 'required|exists:goods_receipt_items,id',
            'evidence'              => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,webp|mimetypes:image/jpeg,image/png,image/webp,application/pdf',
            'notes'                 => 'required|string|max:2000',
        ], [
            'goods_receipt_item_id.required' => 'Item barang penerimaan wajib dipilih.',
            'evidence.required'              => 'Bukti foto atau dokumen surat jalan wajib diunggah.',
            'evidence.file'                  => 'Format bukti kehilangan tidak valid.',
            'evidence.max'                   => 'Ukuran bukti maksimal 10MB.',
            'notes.required'                 => 'Catatan pengiriman (keterangan sopir/ekspedisi/surat jalan) wajib diisi.',
        ]);

        try {
            $loss = $this->service->recordLoss([
                'goods_receipt_item_id' => $validated['goods_receipt_item_id'],
                'evidence'              => $request->file('evidence'),
                'notes'                 => $validated['notes'],
            ], auth()->id());

            return back()->with('success', "Laporan kehilangan barang berhasil dicatat (Status: Recorded). Menunggu persetujuan.");
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Terjadi kesalahan sistem saat mencatat kehilangan barang: ' . $e->getMessage());
        }
    }

    /**
     * Setujui laporan kehilangan barang (Aksi "Approve Transit Loss").
     */
    public function approve(TransitLoss $transitLoss)
    {
        $this->authorize('approve transit losses');

        try {
            $this->service->approveLoss($transitLoss, auth()->id());

            return back()->with('success', 'Laporan kehilangan barang berhasil disetujui. Sisa kuota dan status dokumen telah diperbarui.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses persetujuan kehilangan: ' . $e->getMessage());
        }
    }

    /**
     * Perbarui status klaim kehilangan barang.
     */
    public function updateStatus(Request $request, TransitLoss $transitLoss)
    {
        $this->authorize('approve transit losses');

        $validated = $request->validate([
            'status' => 'required|in:claim_filed,recovered,written_off',
            'notes'  => 'nullable|string|max:1000',
        ]);

        try {
            $this->service->updateClaimStatus($transitLoss, $validated['status'], $validated['notes'] ?? null, auth()->id());

            return back()->with('success', "Status klaim kehilangan berhasil diperbarui menjadi {$validated['status']}.");
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memperbarui status klaim: ' . $e->getMessage());
        }
    }
}
