<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\MaterialRequestController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\ToolAssignmentController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseReceiptController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\MaterialUsageController;
use App\Http\Controllers\DailyLogController;
use App\Http\Controllers\PrintTemplateController;
use App\Http\Controllers\TransitLossController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => redirect()->route('login'));

Route::middleware(['auth', 'auth.session', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Workspace
    Route::post('/workspace/switch', [WorkspaceController::class, 'switch'])->name('workspace.switch');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
    Route::get('/notifications/unread', [NotificationController::class, 'unreadList'])->name('notifications.unread');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::post('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Master Data
    Route::post('/materials/delete-group', [MaterialController::class, 'deleteGroup'])->name('materials.delete-group');
    Route::resource('materials', MaterialController::class);
    Route::post('/tools/delete-group', [ToolController::class, 'deleteGroup'])->name('tools.delete-group');
    Route::post('/tools/{tool}/add-stock', [ToolController::class, 'addStock'])->name('tools.addStock');
    Route::resource('tools', ToolController::class);
    Route::resource('suppliers', SupplierController::class);

    // Categories & Units (combined page)
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroyCategory'])->name('categories.destroy');
    Route::post('/units', [CategoryController::class, 'storeUnit'])->name('units.store');
    Route::put('/units/{unit}', [CategoryController::class, 'updateUnit'])->name('units.update');
    Route::delete('/units/{unit}', [CategoryController::class, 'destroyUnit'])->name('units.destroy');


    // Goods Receipts
    Route::get('/goods-receipts/scheduled-incoming', [GoodsReceiptController::class, 'getScheduledIncoming'])->name('goods-receipts.scheduledIncoming');
    Route::get('/goods-receipts/po-items/{purchaseOrder}', [GoodsReceiptController::class, 'getPoItems'])->name('goods-receipts.poItems');
    Route::get('/goods-receipts/mr-items/{materialRequest}', [GoodsReceiptController::class, 'getMrItems'])->name('goods-receipts.mrItems');
    Route::post('/goods-receipts/{goodsReceipt}/confirm', [GoodsReceiptController::class, 'confirm'])->name('goods-receipts.confirm');
    Route::get('/goods-receipts/{goodsReceipt}/print', [GoodsReceiptController::class, 'print'])->name('goods-receipts.print');
    Route::resource('goods-receipts', GoodsReceiptController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

    // Transit Losses (Kehilangan Barang di Perjalanan)
    Route::get('/transit-losses', [TransitLossController::class, 'index'])->name('transit-losses.index');
    Route::post('/transit-losses', [TransitLossController::class, 'store'])->name('transit-losses.store');
    Route::post('/transit-losses/{transitLoss}/approve', [TransitLossController::class, 'approve'])->name('transit-losses.approve');
    Route::post('/transit-losses/{transitLoss}/status', [TransitLossController::class, 'updateStatus'])->name('transit-losses.status');

    // Material Requests
    Route::get('/material-requests/stock', [MaterialRequestController::class, 'getStock'])->name('material-requests.stock');
    Route::post('/material-requests/{materialRequest}/approve', [MaterialRequestController::class, 'approve'])->name('material-requests.approve');
    Route::post('/material-requests/{materialRequest}/reject', [MaterialRequestController::class, 'reject'])->name('material-requests.reject');
    Route::resource('material-requests', MaterialRequestController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update']);

    // Distributions
    Route::get('/distributions/{distribution}/print', [DistributionController::class, 'print'])->name('distributions.print');
    Route::match(['get', 'post'], '/distributions/{distribution}/ship', [DistributionController::class, 'ship'])->name('distributions.ship');
    Route::post('/distributions/{distribution}/receive', [DistributionController::class, 'receive'])->name('distributions.receive');
    Route::resource('distributions', DistributionController::class)->only(['index', 'create', 'store', 'show']);

    // Material Usages (Pemakaian Material Proyek / Lapangan)
    Route::get('/material-requests/{materialRequest}/details', [MaterialUsageController::class, 'getMRDetails'])->name('material-requests.details');
    Route::resource('material-usages', MaterialUsageController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/material-usages/{materialUsage}/print', [MaterialUsageController::class, 'print'])->name('material-usages.print');
    Route::post('/material-usages/{materialUsage}/cancel', [MaterialUsageController::class, 'cancel'])->name('material-usages.cancel');

    // Daily Site Activity & Material Log (Laporan / Log Harian Proyek)
    Route::get('/daily-log', [DailyLogController::class, 'index'])->name('daily-log.index');
    Route::get('/daily-log/print', [DailyLogController::class, 'print'])->name('daily-log.print');

    // Tool Assignments / Loans
    Route::resource('tool-assignments', ToolAssignmentController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/tool-assignments/{toolLoan}/approve', [ToolAssignmentController::class, 'approve'])->name('tool-assignments.approve');
    Route::post('/tool-assignments/{toolLoan}/reject', [ToolAssignmentController::class, 'reject'])->name('tool-assignments.reject');
    Route::post('/tool-assignments/{toolLoan}/return', [ToolAssignmentController::class, 'return'])->name('tool-assignments.return');
    Route::post('/tool-assignments/{toolLoan}/cancel', [ToolAssignmentController::class, 'cancel'])->name('tool-assignments.cancel');

    // Stock Opname
    Route::resource('stock-opnames', StockOpnameController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('/stock-opnames/{stockOpname}/approve', [StockOpnameController::class, 'approve'])->name('stock-opnames.approve');
    Route::post('/stock-opnames/{stockOpname}/reject', [StockOpnameController::class, 'reject'])->name('stock-opnames.reject');

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/{inventory}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::get('/inventory/{inventory}/edit', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/stock', [ReportController::class, 'stockReport'])->name('reports.stock');
    Route::get('/reports/mutation', [ReportController::class, 'mutationReport'])->name('reports.mutation');
    Route::get('/reports/discrepancy', [ReportController::class, 'discrepancyReport'])->name('reports.discrepancy');
    Route::get('/reports/tools', [ReportController::class, 'toolReport'])->name('reports.tools');

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Procurement Requests
    Route::resource('procurement', ProcurementController::class)->only(['index','create','store','show']);
    Route::post('/procurement/{procurement}/approve', [ProcurementController::class, 'approve'])->name('procurement.approve');
    Route::post('/procurement/{procurement}/reject',  [ProcurementController::class, 'reject'])->name('procurement.reject');

    // Purchase Orders (Hanya Admin Pusat, Admin PO, Owner, po_lapangan, dan po_nota_harian / admin-po-nota untuk read-only)
    Route::group(['middleware' => ['role:Owner|Super Admin|Admin Pusat|Admin|Admin PO|po_lapangan|po_nota_harian|admin-po-nota|admin_pusat']], function () {
        Route::resource('purchase-orders', PurchaseOrderController::class)->only(['index','create','store','show','edit','update']);
        Route::get('/purchase-orders/{purchaseOrder}/print',   [PurchaseOrderController::class, 'print'])->name('purchase-orders.print');
        Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
        Route::post('/purchase-orders/{purchaseOrder}/send',   [PurchaseOrderController::class, 'send'])->name('purchase-orders.send');
        Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
        Route::post('/purchase-orders/{purchaseOrder}/close',  [PurchaseOrderController::class, 'close'])->name('purchase-orders.close');
        Route::put('/purchase-orders/{purchaseOrder}/schedules', [PurchaseOrderController::class, 'updateSchedules'])->name('purchase-orders.update-schedules');
    });

    // Nota Pembelian Harian / Purchase Receipts
    Route::get('/purchase-receipts/report/weekly',   [PurchaseReceiptController::class, 'weeklyReport'])->name('purchase-receipts.weekly-report');
    Route::get('/purchase-receipts/report/monthly',  [PurchaseReceiptController::class, 'monthlyReport'])->name('purchase-receipts.monthly-report');
    Route::patch('/purchase-receipts/{purchaseReceipt}/update-status', [PurchaseReceiptController::class, 'updateStatus'])->name('purchase-receipts.update-status');
    Route::get('/purchase-receipts/{purchaseReceipt}/print',         [PurchaseReceiptController::class, 'print'])->name('purchase-receipts.print');
    Route::get('/purchase-receipts/{purchaseReceipt}/print-weekly',  [PurchaseReceiptController::class, 'printWeekly'])->name('purchase-receipts.print-weekly');
    Route::get('/purchase-receipts/{purchaseReceipt}/print-monthly', [PurchaseReceiptController::class, 'printMonthly'])->name('purchase-receipts.print-monthly');
    Route::get('/purchase-receipts/{purchaseReceipt}/file',          [PurchaseReceiptController::class, 'file'])->name('purchase-receipts.file');
    Route::resource('purchase-receipts', PurchaseReceiptController::class);

    // Payments
    Route::resource('payments', PaymentController::class)->only(['index','create','store','show']);
    Route::post('/payments/topup-balance', [PaymentController::class, 'topupBalance'])->name('payments.topupBalance');
    Route::post('/payments/request-balance', [PaymentController::class, 'requestBalance'])->name('payments.requestBalance');
    Route::post('/payments/balance-requests/{balanceRequest}/approve', [PaymentController::class, 'approveBalanceRequest'])->name('payments.approveBalanceRequest');
    Route::post('/payments/balance-requests/{balanceRequest}/reject', [PaymentController::class, 'rejectBalanceRequest'])->name('payments.rejectBalanceRequest');
    Route::post('/payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

    // Returns
    Route::resource('returns', ReturnController::class)->only(['index','create','store','show']);
    Route::post('/returns/{return}/approve', [ReturnController::class, 'approve'])->name('returns.approve');
    Route::post('/returns/{return}/receive', [ReturnController::class, 'receive'])->name('returns.receive');
    Route::post('/returns/{return}/reject',  [ReturnController::class, 'reject'])->name('returns.reject');

    // Admin: Users, Roles, Warehouses, Projects
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('warehouses', WarehouseController::class)->except(['show']);
    Route::resource('projects', ProjectController::class)->except(['show']);

    // Print Template Manager (Hanya Owner, Super Admin, Admin Pusat)
    Route::group(['middleware' => ['role:Owner|Super Admin|Admin Pusat|Admin']], function () {
        Route::resource('print-templates', PrintTemplateController::class)
            ->only(['index', 'store', 'update', 'destroy']);
        Route::post('/print-templates/{printTemplate}/set-active',
            [PrintTemplateController::class, 'setActive'])
            ->name('print-templates.setActive');
    });

    Route::get('/print-templates/{printTemplate}/file',
        [PrintTemplateController::class, 'file'])
        ->name('print-templates.file')
        ->middleware('signed');
});

require __DIR__.'/auth.php';
