<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectBalanceLog;
use App\Models\ProjectBalanceRequest;
use App\Models\ProjectBalanceRequestItem;
use App\Services\DocumentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectBalanceRequestController extends Controller
{
    /**
     * Tampilkan riwayat Pengajuan Saldo Kas Proyek
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isRoleAdminPOProyek = $user && $user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek']) && !$user->hasAnyRole(['Admin Pusat', 'Admin Pengadaan', 'Admin Pengadaan', 'Owner', 'Super Admin']);
        
        // Halaman ini khusus untuk Admin Pengadaan Proyek. Admin Pengadaan Pusat dan role manajemen mengelola pengajuan lewat menu Keuangan.
        if (!$isRoleAdminPOProyek) {
            return redirect()->route('payments.index')
                ->with('info', 'Pengajuan kas operasional proyek dikelola langsung melalui menu Keuangan.');
        }

        $userProjectIds = $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray();

        if (!empty($userProjectIds)) {
            $projects = Project::whereIn('id', $userProjectIds)->orderBy('name')->get();
            $selectedProjectId = $request->filled('project_id') && in_array((int)$request->project_id, $userProjectIds)
                ? (int)$request->project_id
                : $userProjectIds[0];
            $selectedProject = Project::find($selectedProjectId);
        } else {
            $selectedProjectId = $request->filled('project_id') ? (int)$request->project_id : null;
            $projects = $selectedProjectId ? Project::where('id', $selectedProjectId)->get() : Project::orderBy('name')->get();
            $selectedProject = $selectedProjectId ? Project::find($selectedProjectId) : $projects->first();
        }

        $query = ProjectBalanceRequest::with(['project', 'requester', 'approver', 'items'])->latest('id');

        if ($selectedProject) {
            $query->where('project_id', $selectedProject->id);
        } elseif ($isRoleAdminPOProyek && !empty($userProjectIds)) {
            $query->whereIn('project_id', $userProjectIds);
        }

        $balanceRequests = $query->paginate(15);

        // Ringkasan metrik
        $pendingCount  = (clone $query)->where('status', 'pending')->count();
        $approvedCount = (clone $query)->where('status', 'approved')->count();
        $totalApproved = (float) (clone $query)->where('status', 'approved')->sum('amount');

        return view('balance-requests.index', compact(
            'balanceRequests',
            'projects',
            'selectedProject',
            'isRoleAdminPOProyek',
            'pendingCount',
            'approvedCount',
            'totalApproved'
        ));
    }

    /**
     * Tampilkan formulir pengajuan saldo kas proyek baru
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        $isRoleAdminPOProyek = $user && $user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek']) && !$user->hasAnyRole(['Admin Pusat', 'Admin Pengadaan', 'Admin Pengadaan', 'Owner', 'Super Admin']);

        if (!$isRoleAdminPOProyek) {
            return redirect()->route('payments.index')
                ->with('info', 'Pembuatan formulir pengajuan kas lapangan dikhususkan untuk Admin Pengadaan Proyek.');
        }

        $userProjectIds = $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray();

        if (!empty($userProjectIds)) {
            $projects = Project::whereIn('id', $userProjectIds)->orderBy('name')->get();
            $project = $projects->first();
        } else {
            $projects = Project::orderBy('name')->get();
            $projectId = $request->filled('project_id') ? (int)$request->project_id : null;
            $project = $projectId ? Project::find($projectId) : $projects->first();
        }

        if (!$project) {
            return redirect()->route('project-balance-requests.index')->with('error', 'Proyek tidak ditemukan untuk pengajuan kas.');
        }

        return view('balance-requests.create', compact('project', 'projects', 'isRoleAdminPOProyek'));
    }

    /**
     * Simpan pengajuan saldo kas ke database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'          => 'required|exists:projects,id',
            'purpose'             => 'required|string|max:500',
            'urgency'             => 'required|in:normal,urgent',
            'signer_site_manager' => 'nullable|string|max:150',
            'items'               => 'required|array|min:1',
            'items.*.item_name'   => 'required|string|max:255',
            'items.*.quantity'    => 'required|numeric|min:0.01',
            'items.*.unit'        => 'required|string|max:50',
            'items.*.price'       => 'required|numeric|min:0',
            'items.*.notes'       => 'nullable|string|max:255',
        ], [
            'items.required'      => 'Mohon tambahkan minimal 1 rincian item kebutuhan pengajuan kas.',
            'items.min'           => 'Mohon tambahkan minimal 1 rincian item kebutuhan pengajuan kas.',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $user = auth()->user();

        // Hitung total dari rincian item
        $totalAmount = 0;
        $itemsData = [];

        foreach ($validated['items'] as $item) {
            $qty = (float) $item['quantity'];
            $price = (float) $item['price'];
            $subtotal = $qty * $price;
            $totalAmount += $subtotal;

            $itemsData[] = [
                'item_name'            => trim($item['item_name']),
                'quantity'             => $qty,
                'unit'                 => trim($item['unit']),
                'estimated_unit_price' => $price,
                'estimated_subtotal'   => $subtotal,
                'notes'                => !empty($item['notes']) ? trim($item['notes']) : null,
            ];
        }

        if ($totalAmount <= 0) {
            return back()->withInput()->with('error', 'Total estimasi kebutuhan dana harus lebih dari Rp 0.');
        }

        $balanceRequest = DB::transaction(function () use ($project, $user, $validated, $totalAmount, $itemsData) {
            $req = ProjectBalanceRequest::create([
                'request_number'      => DocumentNumberService::generate('PKL'),
                'request_date'        => now()->toDateString(),
                'project_id'          => $project->id,
                'requested_by'        => $user->id,
                'amount'              => $totalAmount,
                'purpose'             => $validated['purpose'],
                'urgency'             => $validated['urgency'],
                'status'              => 'pending',
                'signer_requester'    => $user->name,
                'signer_site_manager' => !empty($validated['signer_site_manager']) ? $validated['signer_site_manager'] : 'Site Manager Lapangan',
            ]);

            foreach ($itemsData as $data) {
                $req->items()->create($data);
            }

            return $req;
        });

        $formatted = number_format($balanceRequest->amount, 0, ',', '.');
        return redirect()->route('project-balance-requests.index', ['project_id' => $balanceRequest->project_id])
            ->with('success', "Pengajuan Kas Lapangan #{$balanceRequest->request_number} sebesar Rp {$formatted} berhasil dikirim ke Admin Pengadaan Pusat. Anda dapat mencetak formulirnya sekarang.");
    }

    /**
     * Tampilkan Detail Pengajuan Kas Lapangan (show.blade.php)
     */
    public function show(ProjectBalanceRequest $balanceRequest)
    {
        $user = auth()->user();
        $isRoleAdminPOProyek = $user && $user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek']) && !$user->hasAnyRole(['Admin Pusat', 'Admin Pengadaan', 'Admin Pengadaan', 'Owner', 'Super Admin']);
        $userProjectIds = $isRoleAdminPOProyek ? $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray() : [];

        if ($isRoleAdminPOProyek && !empty($userProjectIds) && !in_array($balanceRequest->project_id, $userProjectIds) && $balanceRequest->requested_by !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat pengajuan kas proyek ini.');
        }

        $balanceRequest->load(['project', 'requester', 'approver', 'items']);
        return view('balance-requests.show', compact('balanceRequest', 'isRoleAdminPOProyek'));
    }

    /**
     * Cetak Formulir Resmi Pengajuan Kas Lapangan (A4 Portrait)
     */
    public function print(ProjectBalanceRequest $balanceRequest)
    {
        $user = auth()->user();
        $isRoleAdminPOProyek = $user && $user->hasAnyRole(['Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek', 'Admin Pengadaan Proyek']) && !$user->hasAnyRole(['Admin Pusat', 'Admin Pengadaan', 'Admin Pengadaan', 'Owner', 'Super Admin']);
        $userProjectIds = $isRoleAdminPOProyek ? $user->warehouses()->whereNotNull('project_id')->pluck('project_id')->filter()->unique()->toArray() : [];

        if (!empty($userProjectIds) && !in_array($balanceRequest->project_id, $userProjectIds) && $balanceRequest->requested_by !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak pengajuan kas proyek ini.');
        }

        $balanceRequest->load(['project', 'requester', 'approver', 'items']);
        return view('balance-requests.print', compact('balanceRequest'));
    }

    /**
     * Persetujuan Mandiri oleh Admin Pengadaan Pusat (Sebagai Manajer / Bank Pusat)
     */
    public function approve(Request $request, ProjectBalanceRequest $balanceRequest)
    {
        $user = auth()->user();

        // Verifikasi hak akses: Admin Pengadaan, Admin Pengadaan, Admin Pusat, Super Admin
        if (!$user->hasAnyRole(['Admin Pengadaan', 'Admin Pengadaan', 'Admin Pusat', 'Owner', 'Super Admin']) && !$user->can('approve balance requests')) {
            abort(403, 'Hanya Admin Pengadaan Pusat atau Manajer yang berwenang menyetujui pengajuan kas proyek.');
        }

        if ($balanceRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'reference_number' => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($balanceRequest, $validated, $user) {
            $project = Project::where('id', $balanceRequest->project_id)->lockForUpdate()->firstOrFail();
            $before  = (float) $project->operational_balance;
            $amount  = (float) $balanceRequest->amount;
            $after   = $before + $amount;

            $project->update(['operational_balance' => $after]);

            $ref = (!empty($validated['reference_number'])) ? $validated['reference_number'] : $balanceRequest->request_number;
            $requesterName = $balanceRequest->requester?->name ?? 'Admin Pengadaan Proyek';
            $noteText = (!empty($validated['notes'])) ? $validated['notes'] : $balanceRequest->purpose;

            ProjectBalanceLog::create([
                'project_id'       => $project->id,
                'user_id'          => $user->id,
                'type'             => 'topup',
                'amount'           => $amount,
                'balance_before'   => $before,
                'balance_after'    => $after,
                'reference_number' => $ref,
                'notes'            => "Disetujui & dicairkan oleh Admin Pengadaan Pusat ({$user->name}) dari Pengajuan #{$balanceRequest->request_number}: " . $noteText,
            ]);

            $balanceRequest->update([
                'status'           => 'approved',
                'approved_by'      => $user->id,
                'approved_at'      => now(),
                'reference_number' => $ref,
                'signer_approver'  => $user->name,
            ]);
        });

        $formatted = number_format($balanceRequest->amount, 0, ',', '.');
        return back()->with('success', "Pengajuan Kas #{$balanceRequest->request_number} sebesar Rp {$formatted} berhasil disetujui & dicairkan oleh Admin Pengadaan Pusat ke saldo proyek!");
    }

    /**
     * Penolakan oleh Admin Pengadaan Pusat
     */
    public function reject(Request $request, ProjectBalanceRequest $balanceRequest)
    {
        $user = auth()->user();

        if (!$user->hasAnyRole(['Admin Pengadaan', 'Admin Pengadaan', 'Admin Pusat', 'Owner', 'Super Admin']) && !$user->can('reject balance requests')) {
            abort(403, 'Hanya Admin Pengadaan Pusat atau Manajer yang berwenang menolak pengajuan kas proyek.');
        }

        if ($balanceRequest->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $balanceRequest->update([
            'status'           => 'rejected',
            'approved_by'      => $user->id,
            'approved_at'      => now(),
            'rejection_reason' => $validated['rejection_reason'],
            'signer_approver'  => $user->name,
        ]);

        return back()->with('success', "Pengajuan kas #{$balanceRequest->request_number} telah ditolak.");
    }
}

