<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $u = App\Models\User::whereHas('roles', fn ($q) => $q->where('name', 'Admin Gudang Proyek'))->first();
    echo "USER: " . ($u->id ?? 'none') . "\n";
    echo "ACC_IDS: " . json_encode($u->accessibleWarehouseIds()) . "\n";
    echo "USER_WH_COUNT: " . $u->warehouses()->count() . " | TOTAL_WH: " . App\Models\Warehouse::count() . "\n";
    echo "OWN_WH_IDS: " . json_encode($u->warehouses()->pluck('warehouses.id')) . "\n";
    $active = $u->activeWarehouse();
    echo "ACTIVE: " . ($active->id ?? 'NULL') . " " . ($active->name ?? 'NULL') . " is_central=" . ($active->is_central ?? 'NULL') . "\n";

    try {
        $res = app(App\Http\Controllers\MaterialUsageController::class);
        $ctl = app()->make(App\Http\Controllers\MaterialUsageController::class);
        $u->makeVisible('everything');
        Auth::login($u);
        $resp = $ctl->create(new \Illuminate\Http\Request());
        echo "CREATE OK: " . get_class($resp) . "\n";
    } catch (\Throwable $e) {
        echo "CREATE_ERR: " . get_class($e) . ": " . $e->getMessage() . "\n";
        echo $e->getTraceAsString();
    }
} catch (\Throwable $e) {
    echo "OUTER_ERR: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
