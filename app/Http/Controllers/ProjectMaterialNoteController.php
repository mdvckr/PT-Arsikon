<?php

namespace App\Http\Controllers;

use App\Exports\BulkMaterialExport;
use App\Exports\SingleMaterialLedgerExport;
use App\Exports\ToolRentalSheet;
use App\Exports\WeeklyMaterialAndRentalExport;
use App\Models\MaterialLedger;
use App\Models\MaterialLedgerEntry;
use App\Models\Project;
use App\Models\ProjectBulkMaterial;
use App\Models\ProjectBulkMaterialItem;
use App\Models\ProjectToolRental;
use App\Models\ProjectToolRentalLog;
use App\Models\ProjectMaterialTransfer;
use App\Models\ProjectMaterialTransferItem;
use App\Models\Supplier;
use App\Services\MaterialLedgerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProjectMaterialNoteController extends Controller
{
    protected MaterialLedgerService $ledgerService;

    public function __construct(MaterialLedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $isRoleAdminPOProyek = $user && $user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin PO Proyek', 'admin-po-nota', 'po_nota_harian']) && !$user->hasAnyRole(['Admin Pusat', 'Admin PO', 'Admin Pengadaan', 'Owner', 'Super Admin']);
        $userProjectIds = $isRoleAdminPOProyek ? $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray() : [];

        // 1. Projects for filter dropdown
        if ($isRoleAdminPOProyek) {
            $projects = !empty($userProjectIds) ? Project::whereIn('id', $userProjectIds)->orderBy('name')->get() : Project::where('id', 0)->get();
            $defaultProjectId = $projects->first()?->id;
        } else {
            $projects = Project::orderBy('name')->get();
            $defaultProjectId = null;
        }

        $selectedProjectId = $request->filled('project_id') ? (int) $request->project_id : $defaultProjectId;
        $selectedProject = $selectedProjectId ? Project::find($selectedProjectId) : null;

        // 2. Query Ledgers
        $ledgerQuery = MaterialLedger::with(['entries', 'supplier', 'project', 'creator']);
        if ($selectedProjectId) {
            $ledgerQuery->where('project_id', $selectedProjectId);
        } elseif ($isRoleAdminPOProyek && !empty($userProjectIds)) {
            $ledgerQuery->whereIn('project_id', $userProjectIds);
        }

        // 3. Query Tool Rentals
        $rentalQuery = ProjectToolRental::with(['project', 'creator', 'logs']);
        if ($selectedProjectId) {
            $rentalQuery->where('project_id', $selectedProjectId);
        } elseif ($isRoleAdminPOProyek && !empty($userProjectIds)) {
            $rentalQuery->whereIn('project_id', $userProjectIds);
        }

        // 3b. Query Material / Tool Transfers (Pemindahan Barang & Mobilisasi)
        $transferQuery = ProjectMaterialTransfer::with(['items', 'project', 'creator']);
        if ($selectedProjectId) {
            $transferQuery->where('project_id', $selectedProjectId);
        } elseif ($isRoleAdminPOProyek && !empty($userProjectIds)) {
            $transferQuery->whereIn('project_id', $userProjectIds);
        }

        // 3c. Query Material Curah (Pasir, Batu, Ritase Lapangan)
        $bulkQuery = ProjectBulkMaterial::with(['items', 'supplier', 'project', 'creator']);
        if ($selectedProjectId) {
            $bulkQuery->where('project_id', $selectedProjectId);
        } elseif ($isRoleAdminPOProyek && !empty($userProjectIds)) {
            $bulkQuery->whereIn('project_id', $userProjectIds);
        }

        // 4. Distinct Weeks available for Filter Dropdown
        $allLedgersDates = MaterialLedger::select('start_date', 'week_number', 'year')->get();
        $allRentalsDates = ProjectToolRental::select('start_date', 'week_number', 'year')->get();
        $availableWeeks = collect();

        foreach ($allLedgersDates->concat($allRentalsDates) as $item) {
            $d = $item->start_date ? Carbon::parse($item->start_date) : null;
            if ($d) {
                $y = $d->isoWeekYear();
                $w = $d->isoWeek();
                $key = $y . '-W' . str_pad($w, 2, '0', STR_PAD_LEFT);
                $sampleDate = Carbon::now()->setISODate($y, $w);
                $startOfWeek = $sampleDate->copy()->startOfWeek(Carbon::MONDAY);
                $endOfWeek   = $sampleDate->copy()->endOfWeek(Carbon::SUNDAY);
                $availableWeeks->put($key, [
                    'key'   => $key,
                    'year'  => $y,
                    'week'  => $w,
                    'label' => "Minggu ke-{$w} ({$startOfWeek->translatedFormat('d M Y')} — {$endOfWeek->translatedFormat('d M Y')})",
                ]);
            }
        }
        $availableWeeks = $availableWeeks->sortByDesc('key')->values();

        // 5. Apply Week / Date / Search Filter if explicitly requested
        if ($request->filled('week_key')) {
            $parts = explode('-W', $request->week_key);
            if (count($parts) === 2) {
                $wYear = (int)$parts[0];
                $wNum  = (int)$parts[1];
                $ledgerQuery->where('year', $wYear)->where('week_number', $wNum);
                $rentalQuery->where('year', $wYear)->where('week_number', $wNum);
            }
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $fStart = Carbon::parse($request->start_date)->startOfDay();
            $fEnd   = Carbon::parse($request->end_date)->endOfDay();
            $ledgerQuery->whereBetween('start_date', [$fStart->format('Y-m-d'), $fEnd->format('Y-m-d')]);
            $rentalQuery->whereBetween('start_date', [$fStart->format('Y-m-d'), $fEnd->format('Y-m-d')]);
            $bulkQuery->whereBetween('start_date', [$fStart->format('Y-m-d'), $fEnd->format('Y-m-d')]);
        } elseif ($request->filled('date')) {
            $fDate  = Carbon::parse($request->date);
            $fStart = $fDate->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $fEnd   = $fDate->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
            $ledgerQuery->whereBetween('start_date', [$fStart->format('Y-m-d'), $fEnd->format('Y-m-d')]);
            $rentalQuery->whereBetween('start_date', [$fStart->format('Y-m-d'), $fEnd->format('Y-m-d')]);
            $bulkQuery->whereBetween('start_date', [$fStart->format('Y-m-d'), $fEnd->format('Y-m-d')]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $ledgerQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhere('account_behalf', 'like', "%{$search}%");
            });
            $rentalQuery->where(function ($q) use ($search) {
                $q->where('tool_name', 'like', "%{$search}%")
                  ->orWhere('vendor_name', 'like', "%{$search}%");
            });
            $transferQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('origin_location', 'like', "%{$search}%")
                  ->orWhere('destination_location', 'like', "%{$search}%")
                  ->orWhere('docket_number', 'like', "%{$search}%");
            });
            $bulkQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%");
            });
        }

        $ledgers       = $ledgerQuery->latest('start_date')->latest('id')->get();
        $rentals       = $rentalQuery->latest('start_date')->latest('id')->get();
        $transfers     = $transferQuery->latest('transfer_date')->latest('id')->get();
        $bulkMaterials = $bulkQuery->latest('start_date')->latest('id')->get();

        foreach ($ledgers as $ledger) {
            $ledger->calculated_entries = $this->ledgerService->getCalculatedEntries($ledger);
        }

        $totalDepositVol   = (float) $ledgers->sum('total_deposit_vol');
        $totalDeliveredVol = (float) $ledgers->sum('total_delivered_vol');
        $totalRemainingVol = (float) $ledgers->sum('remaining_deposit_vol');
        $totalMaterialCost = (float) $ledgers->sum('total_used_amount');
        $totalRentalCost   = (float) $rentals->sum('total_rental_cost');
        $totalBulkRit      = (float) $bulkMaterials->sum(fn($b) => $b->total_rit);
        $totalBulkCost     = (float) $bulkMaterials->sum(fn($b) => $b->total_amount);
        $grandTotal        = $totalMaterialCost + $totalRentalCost + $totalBulkCost;

        $suppliers = Supplier::orderBy('name')->get();

        return view('project-notes.index', compact(
            'projects',
            'selectedProject',
            'selectedProjectId',
            'availableWeeks',
            'ledgers',
            'rentals',
            'transfers',
            'bulkMaterials',
            'totalDepositVol',
            'totalDeliveredVol',
            'totalRemainingVol',
            'totalMaterialCost',
            'totalRentalCost',
            'totalBulkRit',
            'totalBulkCost',
            'grandTotal',
            'suppliers',
            'isRoleAdminPOProyek'
        ));
    }

    public function showLedger(MaterialLedger $ledger)
    {
        $ledger->load(['entries', 'supplier', 'project', 'creator']);
        $ledger->calculated_entries = $this->ledgerService->getCalculatedEntries($ledger);

        return view('project-notes.show', compact('ledger'));
    }

    public function storeLedger(Request $request)
    {
        $validated = $request->validate([
            'project_id'         => 'required|exists:projects,id',
            'title'              => 'required|string|max:255',
            'supplier_name'      => 'required|string|max:255',
            'supplier_id'        => 'nullable|exists:suppliers,id',
            'account_behalf'     => 'nullable|string|max:50',
            'default_unit'       => 'required|string|max:20',
            'default_unit_price' => 'nullable|numeric|min:0', // Kosongkan harga bisa
            'start_date'         => 'required|date',
            'end_date'           => 'nullable|date|after_or_equal:start_date',
            'notes'              => 'nullable|string',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = !empty($validated['end_date']) ? Carbon::parse($validated['end_date']) : $startDate->copy()->endOfWeek(Carbon::SUNDAY);

        $ledger = MaterialLedger::create([
            'project_id'         => $project->id,
            'project_name'       => $project->name,
            'title'              => $validated['title'],
            'supplier_id'        => $validated['supplier_id'] ?? null,
            'supplier_name'      => $validated['supplier_name'],
            'account_behalf'     => $validated['account_behalf'] ?? null,
            'default_unit'       => $validated['default_unit'],
            'default_unit_price' => $validated['default_unit_price'] ?? 0,
            'week_number'        => $startDate->isoWeek(),
            'year'               => $startDate->isoWeekYear(),
            'start_date'         => $startDate->format('Y-m-d'),
            'end_date'           => $endDate->format('Y-m-d'),
            'status'             => 'active',
            'notes'              => $validated['notes'] ?? null,
            'created_by'         => auth()->id(),
        ]);

        return back()->with('success', "Buku Catatan Material '{$ledger->title}' berhasil dibuat.");
    }

    public function updateLedger(Request $request, MaterialLedger $ledger)
    {
        $validated = $request->validate([
            'title'              => 'required|string|max:255',
            'supplier_name'      => 'required|string|max:255',
            'supplier_id'        => 'nullable|exists:suppliers,id',
            'account_behalf'     => 'nullable|string|max:50',
            'default_unit'       => 'required|string|max:20',
            'default_unit_price' => 'nullable|numeric|min:0', // Kosongkan harga bisa
            'start_date'         => 'required|date',
            'end_date'           => 'nullable|date|after_or_equal:start_date',
            'notes'              => 'nullable|string',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = !empty($validated['end_date']) ? Carbon::parse($validated['end_date']) : $startDate->copy()->endOfWeek(Carbon::SUNDAY);

        $ledger->update([
            'title'              => $validated['title'],
            'supplier_id'        => $validated['supplier_id'] ?? null,
            'supplier_name'      => $validated['supplier_name'],
            'account_behalf'     => $validated['account_behalf'] ?? null,
            'default_unit'       => $validated['default_unit'],
            'default_unit_price' => $validated['default_unit_price'] ?? 0,
            'week_number'        => $startDate->isoWeek(),
            'year'               => $startDate->isoWeekYear(),
            'start_date'         => $startDate->format('Y-m-d'),
            'end_date'           => $endDate->format('Y-m-d'),
            'notes'              => $validated['notes'] ?? null,
        ]);

        $this->ledgerService->syncLedgerSummary($ledger);

        return back()->with('success', "Buku Catatan Material '{$ledger->title}' berhasil diperbarui.");
    }

    public function destroyLedger(MaterialLedger $ledger)
    {
        $title = $ledger->title;
        $ledger->delete();

        return redirect()->route('project-notes.index')->with('success', "Buku Catatan Material '{$title}' berhasil dihapus.");
    }

    public function storeEntry(Request $request, MaterialLedger $ledger)
    {
        $validated = $request->validate([
            'delivery_date'          => 'required|date',
            'delivery_note_number'   => 'required|string|max:100',
            'material_specification' => 'required|string|max:255',
            'deposit_volume'         => 'nullable|numeric|min:0',
            'delivery_volume'        => 'required|numeric|min:0',
            'unit'                   => 'required|string|max:20',
            'unit_price'             => 'nullable|numeric|min:0', // Kosongkan harga bisa
            'batch_color'            => 'required|in:green,yellow,peach,blue',
            'account_behalf'         => 'nullable|string|max:50',
            'notes'                  => 'nullable|string',
        ]);

        $unitPrice = isset($validated['unit_price']) && $validated['unit_price'] !== '' ? (float)$validated['unit_price'] : (float)($ledger->default_unit_price ?? 0);
        $amountUsed = (float)$validated['delivery_volume'] * $unitPrice;

        $entry = $ledger->entries()->create([
            'delivery_date'          => $validated['delivery_date'],
            'supplier_name'          => $ledger->supplier_name,
            'account_behalf'         => $validated['account_behalf'] ?? $ledger->account_behalf,
            'delivery_note_number'   => $validated['delivery_note_number'],
            'material_specification' => $validated['material_specification'],
            'deposit_volume'         => isset($validated['deposit_volume']) && $validated['deposit_volume'] !== '' ? $validated['deposit_volume'] : null,
            'delivery_volume'        => $validated['delivery_volume'],
            'unit'                   => $validated['unit'],
            'unit_price'             => $unitPrice,
            'amount_used'            => $amountUsed,
            'batch_color'            => $validated['batch_color'],
            'notes'                  => $validated['notes'] ?? null,
            'sort_order'             => $ledger->entries()->count() + 1,
            'created_by'             => auth()->id(),
        ]);

        $this->ledgerService->syncLedgerSummary($ledger);

        return back()->with('success', "Surat Jalan No. {$entry->delivery_note_number} berhasil ditambahkan ke catatan material.");
    }

    public function updateEntry(Request $request, MaterialLedgerEntry $entry)
    {
        $validated = $request->validate([
            'delivery_date'          => 'required|date',
            'delivery_note_number'   => 'required|string|max:100',
            'material_specification' => 'required|string|max:255',
            'deposit_volume'         => 'nullable|numeric|min:0',
            'delivery_volume'        => 'required|numeric|min:0',
            'unit'                   => 'required|string|max:20',
            'unit_price'             => 'nullable|numeric|min:0', // Kosongkan harga bisa
            'batch_color'            => 'required|in:green,yellow,peach,blue',
            'account_behalf'         => 'nullable|string|max:50',
            'notes'                  => 'nullable|string',
        ]);

        $unitPrice = isset($validated['unit_price']) && $validated['unit_price'] !== '' ? (float)$validated['unit_price'] : (float)($entry->unit_price ?? 0);
        $amountUsed = (float)$validated['delivery_volume'] * $unitPrice;

        $entry->update([
            'delivery_date'          => $validated['delivery_date'],
            'account_behalf'         => $validated['account_behalf'] ?? $entry->account_behalf,
            'delivery_note_number'   => $validated['delivery_note_number'],
            'material_specification' => $validated['material_specification'],
            'deposit_volume'         => isset($validated['deposit_volume']) && $validated['deposit_volume'] !== '' ? $validated['deposit_volume'] : null,
            'delivery_volume'        => $validated['delivery_volume'],
            'unit'                   => $validated['unit'],
            'unit_price'             => $unitPrice,
            'amount_used'            => $amountUsed,
            'batch_color'            => $validated['batch_color'],
            'notes'                  => $validated['notes'] ?? null,
        ]);

        $this->ledgerService->syncLedgerSummary($entry->ledger);

        return back()->with('success', "Surat Jalan No. {$entry->delivery_note_number} berhasil diperbarui.");
    }

    public function destroyEntry(MaterialLedgerEntry $entry)
    {
        $ledger = $entry->ledger;
        $noSurat = $entry->delivery_note_number;
        $entry->delete();

        $this->ledgerService->syncLedgerSummary($ledger);

        return back()->with('success', "Surat Jalan No. {$noSurat} berhasil dihapus.");
    }

    public function storeToolRental(Request $request)
    {
        $validated = $request->validate([
            'project_id'        => 'required|exists:projects,id',
            'tool_name'         => 'required|string|max:255',
            'vendor_name'       => 'required|string|max:255',
            'docket_number'     => 'nullable|string|max:100',
            'start_date'        => 'required|date',
            'end_date'          => 'nullable|date|after_or_equal:start_date',
            'rental_duration'   => 'required|numeric|min:0.5',
            'duration_unit'     => 'required|string|max:30',
            'rate_per_unit'     => 'nullable|numeric|min:0', // Kosongkan harga bisa
            'deposit_amount'    => 'nullable|numeric|min:0',
            'status'            => 'required|in:active,returned,unpaid,paid',
            'notes'             => 'nullable|string',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = !empty($validated['end_date']) ? Carbon::parse($validated['end_date']) : null;
        $rate = (float)($validated['rate_per_unit'] ?? 0);
        $totalCost = ((float)$validated['rental_duration'] * $rate) + (float)($validated['deposit_amount'] ?? 0);

        $rental = ProjectToolRental::create([
            'project_id'        => $project->id,
            'project_name'      => $project->name,
            'tool_name'         => $validated['tool_name'],
            'vendor_name'       => $validated['vendor_name'],
            'docket_number'     => $validated['docket_number'] ?? null,
            'week_number'       => $startDate->isoWeek(),
            'year'              => $startDate->isoWeekYear(),
            'start_date'        => $startDate->format('Y-m-d'),
            'end_date'          => $endDate ? $endDate->format('Y-m-d') : null,
            'rental_duration'   => $validated['rental_duration'],
            'duration_unit'     => $validated['duration_unit'],
            'rate_per_unit'     => $rate,
            'deposit_amount'    => $validated['deposit_amount'] ?? 0,
            'total_rental_cost' => $totalCost,
            'status'            => $validated['status'],
            'notes'             => $validated['notes'] ?? null,
            'created_by'        => auth()->id(),
        ]);

        return back()->with('success', "Pencatatan sewa alat '{$rental->tool_name}' berhasil disimpan.");
    }

    public function updateToolRental(Request $request, ProjectToolRental $toolRental)
    {
        $validated = $request->validate([
            'tool_name'         => 'required|string|max:255',
            'vendor_name'       => 'required|string|max:255',
            'docket_number'     => 'nullable|string|max:100',
            'start_date'        => 'required|date',
            'end_date'          => 'nullable|date|after_or_equal:start_date',
            'rental_duration'   => 'required|numeric|min:0.5',
            'duration_unit'     => 'required|string|max:30',
            'rate_per_unit'     => 'nullable|numeric|min:0', // Kosongkan harga bisa
            'deposit_amount'    => 'nullable|numeric|min:0',
            'status'            => 'required|in:active,returned,unpaid,paid',
            'notes'             => 'nullable|string',
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = !empty($validated['end_date']) ? Carbon::parse($validated['end_date']) : null;
        $rate = (float)($validated['rate_per_unit'] ?? 0);
        $totalCost = ((float)$validated['rental_duration'] * $rate) + (float)($validated['deposit_amount'] ?? 0);

        $toolRental->update([
            'tool_name'         => $validated['tool_name'],
            'vendor_name'       => $validated['vendor_name'],
            'docket_number'     => $validated['docket_number'] ?? null,
            'week_number'       => $startDate->isoWeek(),
            'year'              => $startDate->isoWeekYear(),
            'start_date'        => $startDate->format('Y-m-d'),
            'end_date'          => $endDate ? $endDate->format('Y-m-d') : null,
            'rental_duration'   => $validated['rental_duration'],
            'duration_unit'     => $validated['duration_unit'],
            'rate_per_unit'     => $rate,
            'deposit_amount'    => $validated['deposit_amount'] ?? 0,
            'total_rental_cost' => $totalCost,
            'status'            => $validated['status'],
            'notes'             => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Data sewa alat '{$toolRental->tool_name}' berhasil diperbarui.");
    }

    public function destroyToolRental(ProjectToolRental $toolRental)
    {
        $toolName = $toolRental->tool_name;
        $toolRental->delete();

        return back()->with('success', "Catatan sewa alat '{$toolName}' berhasil dihapus.");
    }

    /**
     * 1/1 Export Single Material Ledger (.xlsx)
     */
    public function exportSingleMaterial(MaterialLedger $ledger)
    {
        $filename = "Catatan-Material-{$ledger->id}-" . Str::slug($ledger->title) . ".xlsx";
        return Excel::download(new SingleMaterialLedgerExport($ledger), $filename);
    }

    /**
     * 1/1 Print Single Material Ledger (A4 Landscape)
     */
    public function printSingleMaterial(MaterialLedger $ledger)
    {
        $ledger->load(['entries', 'supplier', 'project', 'creator']);
        $ledger->calculated_entries = $this->ledgerService->getCalculatedEntries($ledger);

        $project = $ledger->project;
        $ledgers = collect([$ledger]);
        $startDate = $ledger->start_date;
        $endDate = $ledger->end_date ?? $ledger->start_date;

        return view('project-notes.print-material', compact('project', 'ledgers', 'startDate', 'endDate'));
    }

    /**
     * 1/1 Export Filtered Material Ledgers (.xlsx)
     */
    public function exportMaterial(Request $request)
    {
        if ($request->filled('ledger_id')) {
            $ledger = MaterialLedger::findOrFail($request->ledger_id);
            return $this->exportSingleMaterial($ledger);
        }

        $query = MaterialLedger::with(['entries', 'supplier', 'project', 'creator']);
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('week_key')) {
            $parts = explode('-W', $request->week_key);
            if (count($parts) === 2) {
                $query->where('year', (int)$parts[0])->where('week_number', (int)$parts[1]);
            }
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('date')) {
            $fDate  = Carbon::parse($request->date);
            $query->whereBetween('start_date', [$fDate->copy()->startOfWeek()->format('Y-m-d'), $fDate->copy()->endOfWeek()->format('Y-m-d')]);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")->orWhere('supplier_name', 'like', "%{$s}%");
            });
        }

        $ledgers = $query->latest('start_date')->get();
        if ($ledgers->isEmpty()) {
            return back()->with('error', 'Tidak ada data Catatan Material untuk diexport.');
        }

        return $this->exportSingleMaterial($ledgers->first());
    }

    /**
     * 1/1 Print Filtered Material Ledgers (A4 Landscape)
     */
    public function printMaterial(Request $request)
    {
        if ($request->filled('ledger_id')) {
            $ledger = MaterialLedger::findOrFail($request->ledger_id);
            return $this->printSingleMaterial($ledger);
        }

        $project = $request->filled('project_id') ? Project::find($request->project_id) : null;
        $query = MaterialLedger::with(['entries', 'supplier', 'project', 'creator']);
        if ($project) {
            $query->where('project_id', $project->id);
        }
        if ($request->filled('week_key')) {
            $parts = explode('-W', $request->week_key);
            if (count($parts) === 2) {
                $query->where('year', (int)$parts[0])->where('week_number', (int)$parts[1]);
            }
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('date')) {
            $fDate  = Carbon::parse($request->date);
            $query->whereBetween('start_date', [$fDate->copy()->startOfWeek()->format('Y-m-d'), $fDate->copy()->endOfWeek()->format('Y-m-d')]);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")->orWhere('supplier_name', 'like', "%{$s}%");
            });
        }

        $ledgers = $query->latest('start_date')->get();
        foreach ($ledgers as $ledger) {
            $ledger->calculated_entries = $this->ledgerService->getCalculatedEntries($ledger);
        }

        $startDate = $ledgers->min('start_date') ? Carbon::parse($ledgers->min('start_date')) : now()->startOfWeek();
        $endDate   = $ledgers->max('end_date') ? Carbon::parse($ledgers->max('end_date')) : ($startDate ? $startDate->copy()->endOfWeek() : now()->endOfWeek());

        return view('project-notes.print-material', compact('project', 'ledgers', 'startDate', 'endDate'));
    }

    /**
     * 1/1 Export Tool Rentals (.xlsx)
     */
    public function exportRentals(Request $request)
    {
        $project = $request->filled('project_id') ? Project::find($request->project_id) : null;
        $query = ProjectToolRental::with(['project', 'creator']);
        if ($project) {
            $query->where('project_id', $project->id);
        }
        if ($request->filled('week_key')) {
            $parts = explode('-W', $request->week_key);
            if (count($parts) === 2) {
                $query->where('year', (int)$parts[0])->where('week_number', (int)$parts[1]);
            }
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('date')) {
            $fDate  = Carbon::parse($request->date);
            $query->whereBetween('start_date', [$fDate->copy()->startOfWeek()->format('Y-m-d'), $fDate->copy()->endOfWeek()->format('Y-m-d')]);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('tool_name', 'like', "%{$s}%")->orWhere('vendor_name', 'like', "%{$s}%");
            });
        }

        $rentals = $query->latest('start_date')->get();
        $startDate = $rentals->min('start_date') ? Carbon::parse($rentals->min('start_date')) : now()->startOfWeek();
        $endDate   = $rentals->max('end_date') ? Carbon::parse($rentals->max('end_date')) : ($startDate ? $startDate->copy()->endOfWeek() : now()->endOfWeek());

        $projSlug = $project ? Str::slug($project->name) : 'semua-proyek';
        $filename = "Rekap-Sewa-Alat-{$projSlug}-" . date('Ymd') . ".xlsx";

        return Excel::download(new ToolRentalSheet($rentals, $project, $startDate, $endDate), $filename);
    }

    /**
     * 1/1 Print Tool Rentals (A4 Landscape)
     */
    public function printRentals(Request $request)
    {
        $project = $request->filled('project_id') ? Project::find($request->project_id) : null;
        $query = ProjectToolRental::with(['project', 'creator']);
        if ($project) {
            $query->where('project_id', $project->id);
        }
        if ($request->filled('week_key')) {
            $parts = explode('-W', $request->week_key);
            if (count($parts) === 2) {
                $query->where('year', (int)$parts[0])->where('week_number', (int)$parts[1]);
            }
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('date')) {
            $fDate  = Carbon::parse($request->date);
            $query->whereBetween('start_date', [$fDate->copy()->startOfWeek()->format('Y-m-d'), $fDate->copy()->endOfWeek()->format('Y-m-d')]);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('tool_name', 'like', "%{$s}%")->orWhere('vendor_name', 'like', "%{$s}%");
            });
        }

        $rentals = $query->latest('start_date')->get();
        $startDate = $rentals->min('start_date') ? Carbon::parse($rentals->min('start_date')) : now()->startOfWeek();
        $endDate   = $rentals->max('end_date') ? Carbon::parse($rentals->max('end_date')) : ($startDate ? $startDate->copy()->endOfWeek() : now()->endOfWeek());

        return view('project-notes.print-rentals', compact('project', 'rentals', 'startDate', 'endDate'));
    }

    /**
     * Backward-compatible Combined Weekly Export
     */
    public function exportWeekly(Request $request)
    {
        $user = auth()->user();
        $isRoleAdminPOProyek = $user && $user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin PO Proyek', 'admin-po-nota', 'po_nota_harian']) && !$user->hasAnyRole(['Admin Pusat', 'Admin PO', 'Admin Pengadaan', 'Owner', 'Super Admin']);
        $userProjectIds = $isRoleAdminPOProyek ? $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray() : [];

        $project = null;
        if ($request->filled('project_id')) {
            $project = Project::find($request->project_id);
        } elseif ($isRoleAdminPOProyek && !empty($userProjectIds)) {
            $project = Project::find($userProjectIds[0]);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate   = Carbon::parse($request->end_date)->endOfDay();
        } elseif ($request->filled('date')) {
            $date = Carbon::parse($request->date);
            $startDate = $date->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $endDate   = $date->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
        } else {
            $startDate = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $endDate   = now()->endOfWeek(Carbon::SUNDAY)->endOfDay();
        }

        $currentWeekNumber = $startDate->isoWeek();
        $currentYear = $startDate->isoWeekYear();

        $ledgerQuery = MaterialLedger::with(['entries', 'supplier', 'project', 'creator']);
        if ($project) {
            $ledgerQuery->where('project_id', $project->id);
        }
        $ledgers = $ledgerQuery->orderBy('id')->get();

        $rentalQuery = ProjectToolRental::with(['project', 'creator']);
        if ($project) {
            $rentalQuery->where('project_id', $project->id);
        }
        $rentals = $rentalQuery->orderBy('start_date')->get();

        $projectNameSlug = $project ? Str::slug($project->name) : 'semua-proyek';
        $filename = "Rekap-Material-dan-Sewa-Alat-{$projectNameSlug}-Minggu-{$currentWeekNumber}-{$startDate->format('Ymd')}.xlsx";

        return Excel::download(new WeeklyMaterialAndRentalExport($ledgers, $rentals, $project, $startDate, $endDate), $filename);
    }

    /**
     * Backward-compatible Combined Weekly Print
     */
    public function printWeekly(Request $request)
    {
        $user = auth()->user();
        $isRoleAdminPOProyek = $user && $user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin PO Proyek', 'admin-po-nota', 'po_nota_harian']) && !$user->hasAnyRole(['Admin Pusat', 'Admin PO', 'Admin Pengadaan', 'Owner', 'Super Admin']);
        $userProjectIds = $isRoleAdminPOProyek ? $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray() : [];

        $project = null;
        if ($request->filled('project_id')) {
            $project = Project::find($request->project_id);
        } elseif ($isRoleAdminPOProyek && !empty($userProjectIds)) {
            $project = Project::find($userProjectIds[0]);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate   = Carbon::parse($request->end_date)->endOfDay();
        } elseif ($request->filled('date')) {
            $date = Carbon::parse($request->date);
            $startDate = $date->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $endDate   = $date->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
        } else {
            $startDate = now()->startOfWeek(Carbon::MONDAY)->startOfDay();
            $endDate   = now()->endOfWeek(Carbon::SUNDAY)->endOfDay();
        }

        $currentWeekNumber = $startDate->isoWeek();
        $currentYear = $startDate->isoWeekYear();

        $ledgerQuery = MaterialLedger::with(['entries', 'supplier', 'project', 'creator']);
        if ($project) {
            $ledgerQuery->where('project_id', $project->id);
        }
        $ledgers = $ledgerQuery->orderBy('id')->get();
        foreach ($ledgers as $ledger) {
            $ledger->calculated_entries = $this->ledgerService->getCalculatedEntries($ledger);
        }

        $rentalQuery = ProjectToolRental::with(['project', 'creator']);
        if ($project) {
            $rentalQuery->where('project_id', $project->id);
        }
        $rentals = $rentalQuery->orderBy('start_date')->get();

        return view('project-notes.print', compact(
            'project',
            'startDate',
            'endDate',
            'currentWeekNumber',
            'currentYear',
            'ledgers',
            'rentals'
        ));
    }

    // =========================================================================
    // DETAIL & LOGBOOK HARIAN SEWA ALAT (REKAP TIBO URIP MODEL)
    // =========================================================================

    public function showRental(ProjectToolRental $rental)
    {
        $rental->load(['logs', 'project', 'creator']);
        return view('project-notes.rental-show', compact('rental'));
    }

    public function storeRentalLog(Request $request, ProjectToolRental $rental)
    {
        $validated = $request->validate([
            'usage_date'     => 'required|date',
            'usage_duration' => 'required|numeric|min:0.1',
            'duration_unit'  => 'required|string|max:30',
            'activity_type'  => 'required|string|max:255',
            'equipment_spec' => 'nullable|string|max:255',
            'notes'          => 'nullable|string',
        ]);

        $log = $rental->logs()->create([
            'usage_date'     => $validated['usage_date'],
            'usage_duration' => $validated['usage_duration'],
            'duration_unit'  => $validated['duration_unit'],
            'activity_type'  => $validated['activity_type'],
            'equipment_spec' => $validated['equipment_spec'] ?? null,
            'notes'          => $validated['notes'] ?? null,
            'sort_order'     => $rental->logs()->count() + 1,
            'created_by'     => auth()->id(),
        ]);

        $rental->recalculateFromLogs();

        return back()->with('success', "Log pemakaian tanggal {$log->usage_date->format('d/m/Y')} ({$log->usage_duration} {$log->duration_unit}) berhasil ditambahkan.");
    }

    public function updateRentalLog(Request $request, ProjectToolRentalLog $log)
    {
        $validated = $request->validate([
            'usage_date'     => 'required|date',
            'usage_duration' => 'required|numeric|min:0.1',
            'duration_unit'  => 'required|string|max:30',
            'activity_type'  => 'required|string|max:255',
            'equipment_spec' => 'nullable|string|max:255',
            'notes'          => 'nullable|string',
        ]);

        $log->update([
            'usage_date'     => $validated['usage_date'],
            'usage_duration' => $validated['usage_duration'],
            'duration_unit'  => $validated['duration_unit'],
            'activity_type'  => $validated['activity_type'],
            'equipment_spec' => $validated['equipment_spec'] ?? null,
            'notes'          => $validated['notes'] ?? null,
        ]);

        $log->rental->recalculateFromLogs();

        return back()->with('success', "Log pemakaian tanggal {$log->usage_date->format('d/m/Y')} berhasil diperbarui.");
    }

    public function destroyRentalLog(ProjectToolRentalLog $log)
    {
        $rental = $log->rental;
        $tgl = $log->usage_date ? $log->usage_date->format('d/m/Y') : '';
        $log->delete();

        $rental->recalculateFromLogs();

        return back()->with('success', "Log pemakaian tanggal {$tgl} berhasil dihapus.");
    }

    // =========================================================================
    // PEMINDAHAN / MOBILISASI BARANG & ALAT ANTAR PROYEK (SURAT JALAN FAPERTA MODEL)
    // =========================================================================

    public function storeTransfer(Request $request)
    {
        $validated = $request->validate([
            'project_id'            => 'required|exists:projects,id',
            'title'                 => 'required|string|max:255',
            'origin_location'       => 'nullable|string|max:255',
            'destination_location'  => 'nullable|string|max:255',
            'transfer_date'         => 'required|date',
            'docket_number'         => 'nullable|string|max:100',
            'driver_or_transporter' => 'nullable|string|max:150',
            'status'                => 'required|in:pending,in_transit,received,completed',
            'notes'                 => 'nullable|string',
        ]);

        $project = Project::findOrFail($validated['project_id']);

        $transfer = ProjectMaterialTransfer::create([
            'project_id'            => $project->id,
            'project_name'          => $project->name,
            'title'                 => $validated['title'],
            'origin_location'       => $validated['origin_location'] ?? null,
            'destination_location'  => $validated['destination_location'] ?? null,
            'transfer_date'         => $validated['transfer_date'],
            'docket_number'         => $validated['docket_number'] ?? null,
            'driver_or_transporter' => $validated['driver_or_transporter'] ?? null,
            'status'                => $validated['status'],
            'notes'                 => $validated['notes'] ?? null,
            'created_by'            => auth()->id(),
        ]);

        return back()->with('success', "Surat jalan pemindahan '{$transfer->title}' berhasil dibuat.");
    }

    public function showTransfer(ProjectMaterialTransfer $transfer)
    {
        $transfer->load(['items', 'project', 'creator']);
        return view('project-notes.transfer-show', compact('transfer'));
    }

    public function updateTransfer(Request $request, ProjectMaterialTransfer $transfer)
    {
        $validated = $request->validate([
            'title'                 => 'required|string|max:255',
            'origin_location'       => 'nullable|string|max:255',
            'destination_location'  => 'nullable|string|max:255',
            'transfer_date'         => 'required|date',
            'docket_number'         => 'nullable|string|max:100',
            'driver_or_transporter' => 'nullable|string|max:150',
            'status'                => 'required|in:pending,in_transit,received,completed',
            'notes'                 => 'nullable|string',
        ]);

        $transfer->update([
            'title'                 => $validated['title'],
            'origin_location'       => $validated['origin_location'] ?? null,
            'destination_location'  => $validated['destination_location'] ?? null,
            'transfer_date'         => $validated['transfer_date'],
            'docket_number'         => $validated['docket_number'] ?? null,
            'driver_or_transporter' => $validated['driver_or_transporter'] ?? null,
            'status'                => $validated['status'],
            'notes'                 => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Surat jalan '{$transfer->title}' berhasil diperbarui.");
    }

    public function destroyTransfer(ProjectMaterialTransfer $transfer)
    {
        $title = $transfer->title;
        $transfer->delete();
        return redirect()->route('project-notes.index')->with('success', "Surat jalan '{$title}' berhasil dihapus.");
    }

    public function storeTransferItem(Request $request, ProjectMaterialTransfer $transfer)
    {
        $validated = $request->validate([
            'transfer_date' => 'nullable|date',
            'item_name'     => 'required|string|max:255',
            'unit'          => 'required|string|max:30',
            'quantity'      => 'required|numeric|min:0.01',
            'notes'         => 'nullable|string',
        ]);

        $transfer->items()->create([
            'transfer_date' => $validated['transfer_date'] ?? $transfer->transfer_date,
            'item_name'     => $validated['item_name'],
            'unit'          => $validated['unit'],
            'quantity'      => $validated['quantity'],
            'notes'         => $validated['notes'] ?? null,
            'sort_order'    => $transfer->items()->count() + 1,
            'created_by'    => auth()->id(),
        ]);

        return back()->with('success', "Item barang '{$validated['item_name']}' berhasil ditambahkan ke surat jalan.");
    }

    public function updateTransferItem(Request $request, ProjectMaterialTransferItem $item)
    {
        $validated = $request->validate([
            'transfer_date' => 'nullable|date',
            'item_name'     => 'required|string|max:255',
            'unit'          => 'required|string|max:30',
            'quantity'      => 'required|numeric|min:0.01',
            'notes'         => 'nullable|string',
        ]);

        $item->update([
            'transfer_date' => $validated['transfer_date'] ?? $item->transfer_date,
            'item_name'     => $validated['item_name'],
            'unit'          => $validated['unit'],
            'quantity'      => $validated['quantity'],
            'notes'         => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Item '{$item->item_name}' berhasil diperbarui.");
    }

    public function destroyTransferItem(ProjectMaterialTransferItem $item)
    {
        $name = $item->item_name;
        $item->delete();
        return back()->with('success', "Item '{$name}' berhasil dihapus dari surat jalan.");
    }

    // =========================================================================
    // CATATAN MATERIAL CURAH LAPANGAN (PASIR, BATU, RITASE - MODEL SUPRI)
    // =========================================================================

    public function storeBulkMaterial(Request $request)
    {
        $validated = $request->validate([
            'project_id'    => 'required|exists:projects,id',
            'title'         => 'required|string|max:255',
            'supplier_name' => 'required|string|max:255',
            'supplier_id'   => 'nullable|exists:suppliers,id',
            'start_date'    => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'status'        => 'required|in:active,completed,archived',
            'notes'         => 'nullable|string',
        ]);

        $project = Project::findOrFail($validated['project_id']);

        $bulk = ProjectBulkMaterial::create([
            'project_id'    => $project->id,
            'project_name'  => $project->name,
            'title'         => $validated['title'],
            'supplier_id'   => $validated['supplier_id'] ?? null,
            'supplier_name' => $validated['supplier_name'],
            'start_date'    => $validated['start_date'],
            'end_date'      => $validated['end_date'] ?? $validated['start_date'],
            'status'        => $validated['status'],
            'notes'         => $validated['notes'] ?? null,
            'created_by'    => auth()->id(),
        ]);

        return back()->with('success', "Catatan material curah '{$bulk->title}' berhasil dibuat.");
    }

    public function showBulkMaterial(ProjectBulkMaterial $bulkMaterial)
    {
        $bulkMaterial->load(['items', 'project', 'supplier', 'creator']);
        return view('project-notes.show-bulk', compact('bulkMaterial'));
    }

    public function updateBulkMaterial(Request $request, ProjectBulkMaterial $bulkMaterial)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'supplier_name' => 'required|string|max:255',
            'supplier_id'   => 'nullable|exists:suppliers,id',
            'start_date'    => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'status'        => 'required|in:active,completed,archived',
            'notes'         => 'nullable|string',
        ]);

        $bulkMaterial->update([
            'title'         => $validated['title'],
            'supplier_id'   => $validated['supplier_id'] ?? null,
            'supplier_name' => $validated['supplier_name'],
            'start_date'    => $validated['start_date'],
            'end_date'      => $validated['end_date'] ?? $validated['start_date'],
            'status'        => $validated['status'],
            'notes'         => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Catatan material curah '{$bulkMaterial->title}' berhasil diperbarui.");
    }

    public function destroyBulkMaterial(ProjectBulkMaterial $bulkMaterial)
    {
        $title = $bulkMaterial->title;
        $bulkMaterial->delete();
        return redirect()->route('project-notes.index')->with('success', "Catatan '{$title}' berhasil dihapus.");
    }

    public function storeBulkItem(Request $request, ProjectBulkMaterial $bulkMaterial)
    {
        $validated = $request->validate([
            'delivery_date' => 'required|date',
            'ticket_number' => 'nullable|string|max:100',
            'quantity'      => 'required|numeric|min:0.01',
            'unit'          => 'required|string|max:30',
            'material_type' => 'required|string|max:100',
            'unit_price'    => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string',
        ]);

        $qty = (float) $validated['quantity'];
        $price = isset($validated['unit_price']) ? (float) $validated['unit_price'] : 0;
        $subtotal = $qty * $price;

        $nextSortOrder = ($bulkMaterial->items()->max('sort_order') ?? 0) + 1;

        $bulkMaterial->items()->create([
            'delivery_date' => $validated['delivery_date'],
            'ticket_number' => $validated['ticket_number'] ?? null,
            'quantity'      => $qty,
            'unit'          => $validated['unit'],
            'material_type' => $validated['material_type'],
            'unit_price'    => $price,
            'subtotal'      => $subtotal,
            'notes'         => $validated['notes'] ?? null,
            'sort_order'    => $nextSortOrder,
            'created_by'    => auth()->id(),
        ]);

        // Auto update date span of the header if needed
        $delDate = Carbon::parse($validated['delivery_date']);
        if (!$bulkMaterial->start_date || $delDate->lt($bulkMaterial->start_date)) {
            $bulkMaterial->update(['start_date' => $delDate->format('Y-m-d')]);
        }
        if (!$bulkMaterial->end_date || $delDate->gt($bulkMaterial->end_date)) {
            $bulkMaterial->update(['end_date' => $delDate->format('Y-m-d')]);
        }

        return back()->with('success', "Item {$qty} {$validated['unit']} {$validated['material_type']} berhasil dicatat.");
    }

    public function updateBulkItem(Request $request, ProjectBulkMaterialItem $item)
    {
        $validated = $request->validate([
            'delivery_date' => 'required|date',
            'ticket_number' => 'nullable|string|max:100',
            'quantity'      => 'required|numeric|min:0.01',
            'unit'          => 'required|string|max:30',
            'material_type' => 'required|string|max:100',
            'unit_price'    => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string',
        ]);

        $qty = (float) $validated['quantity'];
        $price = isset($validated['unit_price']) ? (float) $validated['unit_price'] : 0;
        $subtotal = $qty * $price;

        $item->update([
            'delivery_date' => $validated['delivery_date'],
            'ticket_number' => $validated['ticket_number'] ?? null,
            'quantity'      => $qty,
            'unit'          => $validated['unit'],
            'material_type' => $validated['material_type'],
            'unit_price'    => $price,
            'subtotal'      => $subtotal,
            'notes'         => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Item ritase berhasil diperbarui.");
    }

    public function destroyBulkItem(ProjectBulkMaterialItem $item)
    {
        $desc = "{$item->quantity} {$item->unit} {$item->material_type}";
        $item->delete();
        return back()->with('success', "Item ritase '{$desc}' berhasil dihapus.");
    }

    public function printBulkMaterial(ProjectBulkMaterial $bulkMaterial)
    {
        $bulkMaterial->load(['items', 'project', 'supplier', 'creator']);
        return view('project-notes.print-bulk', compact('bulkMaterial'));
    }

    public function exportBulkMaterial(ProjectBulkMaterial $bulkMaterial)
    {
        $filename = "Catatan-Curah-{$bulkMaterial->id}-" . Str::slug($bulkMaterial->title) . ".xlsx";
        return Excel::download(new BulkMaterialExport($bulkMaterial), $filename);
    }
}
