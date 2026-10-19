<?php

namespace App\Http\Controllers;

use App\Models\PurchaseReceipt;
use App\Models\PurchaseOrder;
use App\Models\Project;
use App\Models\Supplier;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseReceiptController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view purchase receipts');

        $query = PurchaseReceipt::with(['purchaseOrder', 'project', 'supplier', 'creator', 'items']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('receipt_number', 'like', "%{$request->search}%")
                  ->orWhere('day_label', 'like', "%{$request->search}%");
            });
        }

        if ($request->po_id) {
            $query->where('purchase_order_id', $request->po_id);
        }

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->date) {
            $query->whereDate('receipt_date', $request->date);
        }

        $receipts = $query->latest('receipt_date')->latest('id')->paginate(15)->withQueryString();
        $pos = PurchaseOrder::orderBy('po_number', 'desc')->get();

        return view('purchase-receipts.index', compact('receipts', 'pos'));
    }

    public function create(Request $request)
    {
        $this->authorize('create purchase receipts');

        $user = auth()->user();
        $pos = PurchaseOrder::with([
            'supplier',
            'deliveryWarehouse.project',
            'items.material.unit',
            'items.tool',
        ])->latest()->get();

        $projects = Project::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        $materials = Material::with('unit')->orderBy('name')->get();

        $targetPoId = $request->po_id ?? old('purchase_order_id');
        $selectedPO = $targetPoId ? $pos->firstWhere('id', $targetPoId) : null;

        $poItemsMap = $pos->mapWithKeys(function ($po) {
            $items = $po->items->map(function ($it) {
                return [
                    'id'               => $it->id,
                    'material_id'      => $it->material_id,
                    'item_name'        => $it->displayName(),
                    'quantity'         => (float) $it->quantity,
                    'unit_price'       => (float) $it->unit_price,
                    'subtotal'         => (float) $it->subtotal,
                    'unit'             => $it->displayUnit(),
                    'notes'            => $it->notes ?? '',
                ];
            })->values();

            $warehouse = $po->deliveryWarehouse;
            $projectName = $warehouse?->project?->name ?? $warehouse?->name ?? '';
            $supplierName = $po->display_supplier_name !== '-' ? $po->display_supplier_name : '';

            return [$po->id => [
                'id'            => $po->id,
                'po_number'     => $po->po_number,
                'supplier_name' => $supplierName,
                'project_name'  => $projectName,
                'order_date'    => $po->order_date ? $po->order_date->format('Y-m-d') : null,
                'items'         => $items,
            ]];
        });

        $defaultProjectName = $selectedPO
            ? ($selectedPO->deliveryWarehouse?->project?->name ?? $selectedPO->deliveryWarehouse?->name ?? '')
            : ($user->activeWarehouse()?->project?->name
                ?? $user->warehouses->firstWhere('type', 'project')?->project?->name
                ?? '');

        $defaultSupplierName = $selectedPO
            ? ($selectedPO->display_supplier_name !== '-' ? $selectedPO->display_supplier_name : '')
            : '';

        return view('purchase-receipts.create', compact(
            'pos',
            'poItemsMap',
            'projects',
            'suppliers',
            'materials',
            'selectedPO',
            'defaultProjectName',
            'defaultSupplierName'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('create purchase receipts');

        $validated = $request->validate([
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'project_name'      => 'nullable|string|max:255',
            'supplier_name'     => 'nullable|string|max:255',
            'receipt_number'    => 'nullable|string|max:100',
            'receipt_date'      => 'required|date',
            'payment_status'    => 'required|in:unpaid,paid',
            'image'             => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp|max:10240',
            'notes'             => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.material_id'=> 'nullable|exists:materials,id',
            'items.*.item_name'  => 'nullable|string|max:255',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes'      => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $this->handleReceiptUpload($request->file('image'));
            }

            $supplierName = $validated['supplier_name'] ?? null;
            $supplierId = null;
            $projectId = null;
            if (!empty($validated['purchase_order_id'])) {
                $po = PurchaseOrder::with(['supplier', 'deliveryWarehouse.project'])->find($validated['purchase_order_id']);
                if ($po) {
                    $supplierId = $po->supplier_id;
                    if (!$supplierName) {
                        $supplierName = $po->display_supplier_name !== '-' ? $po->display_supplier_name : $po->supplier?->name;
                    }
                    if (empty($validated['project_name'])) {
                        $validated['project_name'] = $po->deliveryWarehouse?->project?->name ?? $po->deliveryWarehouse?->name;
                    }
                    $projectId = $po->deliveryWarehouse?->project_id;
                }
            }

            $days = [
                'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
            ];
            $dayEnglish = date('l', strtotime($validated['receipt_date']));
            $dayLabel = $days[$dayEnglish] ?? $dayEnglish;

            $receipt = PurchaseReceipt::create([
                'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                'project_id'        => $projectId,
                'project_name'      => $validated['project_name'] ?? null,
                'supplier_id'       => $supplierId,
                'supplier_name'     => $supplierName,
                'receipt_number'    => $validated['receipt_number'] ?? null,
                'receipt_date'      => $validated['receipt_date'],
                'day_label'         => $dayLabel,
                'payment_status'    => $validated['payment_status'],
                'image_path'        => $imagePath,
                'notes'             => $validated['notes'] ?? null,
                'created_by'        => auth()->id(),
                'total_amount'      => 0,
            ]);

            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $material = !empty($item['material_id']) ? Material::find($item['material_id']) : null;
                $itemName = $item['item_name'] ?? ($material ? $material->name : 'Item Nota');
                $subtotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $subtotal;

                $receipt->items()->create([
                    'material_id' => $item['material_id'] ?? null,
                    'item_name'   => $itemName,
                    'quantity'    => $item['quantity'],
                    'unit_price'  => $item['unit_price'],
                    'subtotal'    => $subtotal,
                    'notes'       => $item['notes'] ?? null,
                ]);
            }

            $receipt->update(['total_amount' => $totalAmount]);

            DB::commit();

            return redirect()->route('purchase-receipts.show', $receipt)
                ->with('success', 'Nota Pembelian Harian berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan nota: ' . $e->getMessage());
        }
    }

    public function show(PurchaseReceipt $purchaseReceipt)
    {
        $this->authorize('view purchase receipts');

        $purchaseReceipt->load(['purchaseOrder.payments', 'project', 'supplier', 'creator', 'items.material.unit']);
        return view('purchase-receipts.show', compact('purchaseReceipt'));
    }

    public function updateStatus(Request $request, PurchaseReceipt $purchaseReceipt)
    {
        $this->authorize('create purchase receipts');

        $validated = $request->validate([
            'payment_status' => 'required|in:unpaid,paid,cancelled',
        ]);

        $purchaseReceipt->update([
            'payment_status' => $validated['payment_status'],
        ]);

        $label = $purchaseReceipt->status_label;
        $noNota = $purchaseReceipt->receipt_number ? "No. {$purchaseReceipt->receipt_number}" : "ID #{$purchaseReceipt->id}";

        return back()->with('success', "Status nota {$noNota} berhasil diubah menjadi: {$label}.");
    }

    public function destroy(PurchaseReceipt $purchaseReceipt)
    {
        $this->authorize('delete purchase receipts');

        if ($purchaseReceipt->image_path) {
            Storage::disk('public')->delete($purchaseReceipt->image_path);
        }
        $purchaseReceipt->delete();

        return redirect()->route('purchase-receipts.index')
            ->with('success', 'Nota Pembelian berhasil dihapus.');
    }

    /**
     * Handle receipt upload with auto-compression for images (GD) and direct store for PDFs.
     */
    protected function handleReceiptUpload($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        // File PDF disimpan langsung tanpa dikompres
        if ($extension === 'pdf') {
            return $file->store('purchase_receipts', 'public');
        }

        // Kompres gambar (JPG, PNG, WEBP) menggunakan ekstensi GD
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp']) && function_exists('imagecreatefromstring')) {
            try {
                $imageContent = file_get_contents($file->getRealPath());
                $srcImage = @imagecreatefromstring($imageContent);

                if ($srcImage !== false) {
                    // Deteksi rotasi kamera HP dari EXIF agar foto tidak terbalik/miring
                    if (function_exists('exif_read_data')) {
                        $exif = @exif_read_data($file->getRealPath());
                        if (!empty($exif['Orientation'])) {
                            switch ($exif['Orientation']) {
                                case 3:
                                    $srcImage = imagerotate($srcImage, 180, 0);
                                    break;
                                case 6:
                                    $srcImage = imagerotate($srcImage, -90, 0);
                                    break;
                                case 8:
                                    $srcImage = imagerotate($srcImage, 90, 0);
                                    break;
                            }
                        }
                    }

                    $origWidth = imagesx($srcImage);
                    $origHeight = imagesy($srcImage);

                    // Resolusi maksimal 1600px - menjaga tulisan nota, nota rincian, dan stempel tetap sangat tajam
                    $maxDimension = 1600;
                    $newWidth = $origWidth;
                    $newHeight = $origHeight;

                    if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
                        if ($origWidth > $origHeight) {
                            $newWidth = $maxDimension;
                            $newHeight = (int) round(($origHeight / $origWidth) * $maxDimension);
                        } else {
                            $newHeight = $maxDimension;
                            $newWidth = (int) round(($origWidth / $origHeight) * $maxDimension);
                        }
                    }

                    $targetImage = imagecreatetruecolor($newWidth, $newHeight);

                    // Background putih (jika file PNG/WEBP transparan)
                    $white = imagecolorallocate($targetImage, 255, 255, 255);
                    imagefilledrectangle($targetImage, 0, 0, $newWidth, $newHeight, $white);

                    // Resample gambar berkualitas tinggi
                    imagecopyresampled($targetImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

                    // Kompres output ke format JPEG dengan kualitas 78% (sweet spot hemat ukuran & detail tajam)
                    ob_start();
                    imagejpeg($targetImage, null, 78);
                    $compressedData = ob_get_clean();

                    imagedestroy($srcImage);
                    imagedestroy($targetImage);

                    if (!empty($compressedData)) {
                        $filename = 'purchase_receipts/' . Str::random(40) . '.jpg';
                        Storage::disk('public')->put($filename, $compressedData);
                        return $filename;
                    }
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Fallback jika bukan gambar atau GD gagal
        return $file->store('purchase_receipts', 'public');
    }
}

