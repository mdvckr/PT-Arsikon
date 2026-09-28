<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\Tool;
use App\Models\ToolInventory;
use App\Models\Unit;
use App\Models\Warehouse;
use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SupabaseSyncService
{
    /**
     * Nama koneksi database untuk Supabase.
     */
    public const CONNECTION_NAME = 'supabase';

    /**
     * Periksa apakah kredensial Supabase sudah terisi di .env
     */
    public function isConfigured(): bool
    {
        $host = config('database.connections.' . self::CONNECTION_NAME . '.host');
        $password = config('database.connections.' . self::CONNECTION_NAME . '.password');

        return !empty($host) && !empty($password);
    }

    /**
     * Dapatkan ringkasan konfigurasi (tanpa membocorkan password).
     */
    public function getConfigSummary(): array
    {
        $conn = config('database.connections.' . self::CONNECTION_NAME, []);

        return [
            'is_configured' => $this->isConfigured(),
            'host'          => $conn['host'] ?? null,
            'port'          => $conn['port'] ?? 5432,
            'database'      => $conn['database'] ?? 'postgres',
            'username'      => $conn['username'] ?? 'postgres',
            'sslmode'       => $conn['sslmode'] ?? 'prefer',
        ];
    }

    /**
     * Tes koneksi ke Supabase secara aman.
     */
    public function testConnection(): array
    {
        if (!$this->isConfigured()) {
            return [
                'connected' => false,
                'message'   => 'Kredensial Supabase belum dikonfigurasi di file .env (SUPABASE_DB_HOST & SUPABASE_DB_PASSWORD masih kosong).',
                'latency_ms'=> null,
            ];
        }

        $startTime = microtime(true);

        try {
            DB::purge(self::CONNECTION_NAME);
            $pdo = DB::connection(self::CONNECTION_NAME)->getPdo();
            $result = DB::connection(self::CONNECTION_NAME)->select('SELECT version();');

            $latency = round((microtime(true) - $startTime) * 1000);

            return [
                'connected'  => true,
                'message'    => 'Koneksi ke Supabase Cloud berhasil!',
                'latency_ms' => $latency,
                'version'    => $result[0]->version ?? 'PostgreSQL',
            ];
        } catch (Throwable $e) {
            Log::warning('Supabase connection test failed: ' . $e->getMessage());

            return [
                'connected' => false,
                'message'   => 'Gagal terhubung ke Supabase: ' . $e->getMessage(),
                'latency_ms'=> null,
            ];
        }
    }

    /**
     * Pastikan tabel backup di Supabase sudah siap secara otomatis.
     */
    public function ensureTablesExist(): void
    {
        $conn = DB::connection(self::CONNECTION_NAME);

        // 1. Tabel backup_categories (Master Kategori Material & Alat)
        $conn->statement("
            CREATE TABLE IF NOT EXISTS backup_categories (
                code VARCHAR(50) PRIMARY KEY,
                name VARCHAR(150) NOT NULL,
                type VARCHAR(50) NOT NULL DEFAULT 'material',
                description TEXT,
                synced_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // 2. Tabel backup_units (Master Satuan)
        $conn->statement("
            CREATE TABLE IF NOT EXISTS backup_units (
                code VARCHAR(50) PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                is_decimal BOOLEAN DEFAULT FALSE,
                synced_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // 3. Tabel backup_materials
        $conn->statement("
            CREATE TABLE IF NOT EXISTS backup_materials (
                sku VARCHAR(100) PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                brand VARCHAR(150),
                type VARCHAR(150),
                size VARCHAR(150),
                description TEXT,
                category_name VARCHAR(150),
                category_code VARCHAR(50),
                unit_code VARCHAR(50),
                unit_name VARCHAR(100),
                unit_is_decimal BOOLEAN DEFAULT FALSE,
                supplier_name VARCHAR(255),
                min_stock_central NUMERIC(15, 2) DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
                incoming_stages JSONB,
                synced_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // 4. Tabel backup_tools
        $conn->statement("
            CREATE TABLE IF NOT EXISTS backup_tools (
                code VARCHAR(100) PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                brand VARCHAR(150),
                type VARCHAR(150),
                size VARCHAR(150),
                notes TEXT,
                category_name VARCHAR(150),
                category_code VARCHAR(50),
                is_active BOOLEAN DEFAULT TRUE,
                stock_total INTEGER DEFAULT 0,
                stock_available INTEGER DEFAULT 0,
                stock_borrowed INTEGER DEFAULT 0,
                stock_maintenance INTEGER DEFAULT 0,
                stock_damaged INTEGER DEFAULT 0,
                incoming_stages JSONB,
                synced_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // 5. Tabel backup_inventories (Stok Real Material per Gudang/Proyek)
        $conn->statement("
            CREATE TABLE IF NOT EXISTS backup_inventories (
                warehouse_code VARCHAR(50) NOT NULL,
                material_sku VARCHAR(100) NOT NULL,
                warehouse_name VARCHAR(150),
                material_name VARCHAR(255),
                quantity NUMERIC(15, 2) DEFAULT 0,
                qty_allocated NUMERIC(15, 2) DEFAULT 0,
                qty_in_transit NUMERIC(15, 2) DEFAULT 0,
                synced_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (warehouse_code, material_sku)
            );
        ");

        // 6. Tabel backup_tool_inventories (Stok Real Alat per Gudang/Proyek)
        $conn->statement("
            CREATE TABLE IF NOT EXISTS backup_tool_inventories (
                warehouse_code VARCHAR(50) NOT NULL,
                tool_code VARCHAR(100) NOT NULL,
                warehouse_name VARCHAR(150),
                tool_name VARCHAR(255),
                stock_total INTEGER DEFAULT 0,
                stock_available INTEGER DEFAULT 0,
                stock_borrowed INTEGER DEFAULT 0,
                stock_maintenance INTEGER DEFAULT 0,
                stock_damaged INTEGER DEFAULT 0,
                synced_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (warehouse_code, tool_code)
            );
        ");
    }

    /**
     * PUSH: Cadangkan Kategori, Satuan, Material, Alat, & Stok per Gudang dari MySQL lokal ke Supabase.
     */
    public function pushToSupabase(): array
    {
        $test = $this->testConnection();
        if (!$test['connected']) {
            throw new Exception($test['message']);
        }

        $this->ensureTablesExist();

        $conn = DB::connection(self::CONNECTION_NAME);
        $now = now()->toIso8601String();

        // 1. Cadangkan Categories
        $categories = Category::all();
        $categoriesCount = 0;
        foreach ($categories as $cat) {
            $code = $cat->code ?: strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $cat->name), 0, 10));
            $conn->statement("
                INSERT INTO backup_categories (code, name, type, description, synced_at)
                VALUES (?, ?, ?, ?, ?)
                ON CONFLICT (code) DO UPDATE SET
                    name = EXCLUDED.name,
                    type = EXCLUDED.type,
                    description = EXCLUDED.description,
                    synced_at = EXCLUDED.synced_at;
            ", [
                $code,
                $cat->name,
                $cat->type ?? 'material',
                $cat->description,
                $now,
            ]);
            $categoriesCount++;
        }

        // 2. Cadangkan Units
        $units = Unit::all();
        $unitsCount = 0;
        foreach ($units as $u) {
            $conn->statement("
                INSERT INTO backup_units (code, name, is_decimal, synced_at)
                VALUES (?, ?, ?, ?)
                ON CONFLICT (code) DO UPDATE SET
                    name = EXCLUDED.name,
                    is_decimal = EXCLUDED.is_decimal,
                    synced_at = EXCLUDED.synced_at;
            ", [
                $u->code,
                $u->name,
                (bool) $u->is_decimal ? 'true' : 'false',
                $now,
            ]);
            $unitsCount++;
        }

        // 3. Cadangkan Materials
        $materials = Material::with(['category', 'unit', 'supplier'])->get();
        $materialsCount = 0;
        foreach ($materials as $m) {
            $incomingStagesJson = !empty($m->incoming_stages)
                ? (is_string($m->incoming_stages) ? $m->incoming_stages : json_encode($m->incoming_stages))
                : null;

            $conn->statement("
                INSERT INTO backup_materials (
                    sku, name, brand, type, size, description,
                    category_name, category_code, unit_code, unit_name,
                    unit_is_decimal, supplier_name, min_stock_central,
                    is_active, incoming_stages, synced_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?::jsonb, ?
                )
                ON CONFLICT (sku) DO UPDATE SET
                    name = EXCLUDED.name,
                    brand = EXCLUDED.brand,
                    type = EXCLUDED.type,
                    size = EXCLUDED.size,
                    description = EXCLUDED.description,
                    category_name = EXCLUDED.category_name,
                    category_code = EXCLUDED.category_code,
                    unit_code = EXCLUDED.unit_code,
                    unit_name = EXCLUDED.unit_name,
                    unit_is_decimal = EXCLUDED.unit_is_decimal,
                    supplier_name = EXCLUDED.supplier_name,
                    min_stock_central = EXCLUDED.min_stock_central,
                    is_active = EXCLUDED.is_active,
                    incoming_stages = EXCLUDED.incoming_stages,
                    synced_at = EXCLUDED.synced_at;
            ", [
                $m->sku,
                $m->name,
                $m->brand,
                $m->type,
                $m->size,
                $m->description,
                $m->category?->name,
                $m->category?->code,
                $m->unit?->code,
                $m->unit?->name,
                (bool) ($m->unit?->is_decimal ?? false) ? 'true' : 'false',
                $m->supplier_name ?: $m->supplier?->name,
                (float) ($m->min_stock_central ?? 0),
                (bool) $m->is_active ? 'true' : 'false',
                $incomingStagesJson,
                $now,
            ]);

            $materialsCount++;
        }

        // 4. Cadangkan Tools
        $tools = Tool::with(['category'])->get();
        $toolsCount = 0;
        foreach ($tools as $t) {
            $incomingStagesJson = !empty($t->incoming_stages)
                ? (is_string($t->incoming_stages) ? $t->incoming_stages : json_encode($t->incoming_stages))
                : null;

            $conn->statement("
                INSERT INTO backup_tools (
                    code, name, brand, type, size, notes,
                    category_name, category_code, is_active,
                    stock_total, stock_available, stock_borrowed,
                    stock_maintenance, stock_damaged, incoming_stages, synced_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?::jsonb, ?
                )
                ON CONFLICT (code) DO UPDATE SET
                    name = EXCLUDED.name,
                    brand = EXCLUDED.brand,
                    type = EXCLUDED.type,
                    size = EXCLUDED.size,
                    notes = EXCLUDED.notes,
                    category_name = EXCLUDED.category_name,
                    category_code = EXCLUDED.category_code,
                    is_active = EXCLUDED.is_active,
                    stock_total = EXCLUDED.stock_total,
                    stock_available = EXCLUDED.stock_available,
                    stock_borrowed = EXCLUDED.stock_borrowed,
                    stock_maintenance = EXCLUDED.stock_maintenance,
                    stock_damaged = EXCLUDED.stock_damaged,
                    incoming_stages = EXCLUDED.incoming_stages,
                    synced_at = EXCLUDED.synced_at;
            ", [
                $t->code,
                $t->name,
                $t->brand,
                $t->type,
                $t->size,
                $t->notes,
                $t->category?->name,
                $t->category?->code,
                (bool) $t->is_active ? 'true' : 'false',
                (int) ($t->stock_total ?? 0),
                (int) ($t->stock_available ?? 0),
                (int) ($t->stock_borrowed ?? 0),
                (int) ($t->stock_maintenance ?? 0),
                (int) ($t->stock_damaged ?? 0),
                $incomingStagesJson,
                $now,
            ]);

            $toolsCount++;
        }

        // 5. Cadangkan Inventories (Stok Material per Gudang)
        $inventories = Inventory::with(['warehouse', 'material'])->get();
        $inventoriesCount = 0;
        foreach ($inventories as $inv) {
            if (!$inv->warehouse || !$inv->material) {
                continue;
            }
            $whCode = $inv->warehouse->code ?: ('WH-' . $inv->warehouse->id);

            $conn->statement("
                INSERT INTO backup_inventories (
                    warehouse_code, material_sku, warehouse_name, material_name,
                    quantity, qty_allocated, qty_in_transit, synced_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON CONFLICT (warehouse_code, material_sku) DO UPDATE SET
                    warehouse_name = EXCLUDED.warehouse_name,
                    material_name = EXCLUDED.material_name,
                    quantity = EXCLUDED.quantity,
                    qty_allocated = EXCLUDED.qty_allocated,
                    qty_in_transit = EXCLUDED.qty_in_transit,
                    synced_at = EXCLUDED.synced_at;
            ", [
                $whCode,
                $inv->material->sku,
                $inv->warehouse->name,
                $inv->material->name,
                (float) ($inv->quantity ?? 0),
                (float) ($inv->qty_allocated ?? 0),
                (float) ($inv->qty_in_transit ?? 0),
                $now,
            ]);
            $inventoriesCount++;
        }

        // 6. Cadangkan ToolInventories (Stok Alat per Gudang)
        $toolInventories = ToolInventory::with(['warehouse', 'tool'])->get();
        $toolInventoriesCount = 0;
        foreach ($toolInventories as $tInv) {
            if (!$tInv->warehouse || !$tInv->tool) {
                continue;
            }
            $whCode = $tInv->warehouse->code ?: ('WH-' . $tInv->warehouse->id);

            $conn->statement("
                INSERT INTO backup_tool_inventories (
                    warehouse_code, tool_code, warehouse_name, tool_name,
                    stock_total, stock_available, stock_borrowed,
                    stock_maintenance, stock_damaged, synced_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON CONFLICT (warehouse_code, tool_code) DO UPDATE SET
                    warehouse_name = EXCLUDED.warehouse_name,
                    tool_name = EXCLUDED.tool_name,
                    stock_total = EXCLUDED.stock_total,
                    stock_available = EXCLUDED.stock_available,
                    stock_borrowed = EXCLUDED.stock_borrowed,
                    stock_maintenance = EXCLUDED.stock_maintenance,
                    stock_damaged = EXCLUDED.stock_damaged,
                    synced_at = EXCLUDED.synced_at;
            ", [
                $whCode,
                $tInv->tool->code,
                $tInv->warehouse->name,
                $tInv->tool->name,
                (int) ($tInv->stock_total ?? 0),
                (int) ($tInv->stock_available ?? 0),
                (int) ($tInv->stock_borrowed ?? 0),
                (int) ($tInv->stock_maintenance ?? 0),
                (int) ($tInv->stock_damaged ?? 0),
                $now,
            ]);
            $toolInventoriesCount++;
        }

        return [
            'success'                => true,
            'message'                => "Berhasil mencadangkan {$categoriesCount} Kategori, {$unitsCount} Satuan, {$materialsCount} Material, {$toolsCount} Alat, {$inventoriesCount} Stok Material Gudang, dan {$toolInventoriesCount} Stok Alat Gudang ke Supabase Cloud.",
            'categories_count'       => $categoriesCount,
            'units_count'            => $unitsCount,
            'materials_count'        => $materialsCount,
            'tools_count'            => $toolsCount,
            'inventories_count'      => $inventoriesCount,
            'tool_inventories_count' => $toolInventoriesCount,
            'synced_at'              => $now,
        ];
    }

    /**
     * PULL / RESTORE: Tarik Kategori, Satuan, Material, Alat, & Stok per Gudang dari Supabase Cloud ke MySQL lokal.
     */
    public function pullFromSupabase(): array
    {
        $test = $this->testConnection();
        if (!$test['connected']) {
            throw new Exception($test['message']);
        }

        $this->ensureTablesExist();

        $conn = DB::connection(self::CONNECTION_NAME);

        // 1. Tarik Categories
        $cloudCategories = $conn->table('backup_categories')->get();
        $categoriesRestored = 0;
        foreach ($cloudCategories as $catRow) {
            Category::updateOrCreate(
                ['code' => $catRow->code],
                [
                    'name'        => $catRow->name,
                    'type'        => $catRow->type ?? 'material',
                    'description' => $catRow->description,
                ]
            );
            $categoriesRestored++;
        }

        // 2. Tarik Units
        $cloudUnits = $conn->table('backup_units')->get();
        $unitsRestored = 0;
        foreach ($cloudUnits as $uRow) {
            Unit::updateOrCreate(
                ['code' => $uRow->code],
                [
                    'name'       => $uRow->name,
                    'is_decimal' => filter_var($uRow->is_decimal ?? false, FILTER_VALIDATE_BOOLEAN),
                ]
            );
            $unitsRestored++;
        }

        // 3. Tarik Materials
        $cloudMaterials = $conn->table('backup_materials')->get();
        $materialsRestored = 0;
        foreach ($cloudMaterials as $row) {
            $category = null;
            if (!empty($row->category_name)) {
                $category = Category::where('name', $row->category_name)->where('type', 'material')->first();
                if (!$category && !empty($row->category_code)) {
                    $category = Category::where('code', $row->category_code)->first();
                }
                if (!$category) {
                    $category = Category::create([
                        'name' => $row->category_name,
                        'type' => 'material',
                        'code' => $row->category_code ?: strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $row->category_name), 0, 10)),
                    ]);
                }
            }

            $unit = null;
            if (!empty($row->unit_code)) {
                $unit = Unit::firstOrCreate(
                    ['code' => $row->unit_code],
                    [
                        'name'       => $row->unit_name ?: strtoupper($row->unit_code),
                        'is_decimal' => filter_var($row->unit_is_decimal ?? false, FILTER_VALIDATE_BOOLEAN),
                    ]
                );
            }

            $supplier = null;
            if (!empty($row->supplier_name)) {
                $supplier = Supplier::firstOrCreate(
                    ['name' => $row->supplier_name],
                    [
                        'code'      => 'SUP-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $row->supplier_name), 0, 6)),
                        'is_active' => true,
                    ]
                );
            }

            $incomingStages = null;
            if (!empty($row->incoming_stages)) {
                $incomingStages = is_string($row->incoming_stages)
                    ? json_decode($row->incoming_stages, true)
                    : (array) $row->incoming_stages;
            }

            Material::updateOrCreate(
                ['sku' => $row->sku],
                [
                    'name'              => $row->name,
                    'brand'             => $row->brand,
                    'type'              => $row->type,
                    'size'              => $row->size,
                    'description'       => $row->description,
                    'category_id'       => $category?->id,
                    'unit_id'           => $unit?->id,
                    'supplier_id'       => $supplier?->id,
                    'supplier_name'     => $row->supplier_name,
                    'min_stock_central' => (float) ($row->min_stock_central ?? 0),
                    'is_active'         => filter_var($row->is_active ?? true, FILTER_VALIDATE_BOOLEAN),
                    'incoming_stages'   => $incomingStages,
                ]
            );

            $materialsRestored++;
        }

        // 4. Tarik Tools
        $cloudTools = $conn->table('backup_tools')->get();
        $toolsRestored = 0;
        foreach ($cloudTools as $row) {
            $category = null;
            if (!empty($row->category_name)) {
                $category = Category::where('name', $row->category_name)->where('type', 'tool')->first();
                if (!$category && !empty($row->category_code)) {
                    $category = Category::where('code', $row->category_code)->first();
                }
                if (!$category) {
                    $category = Category::create([
                        'name' => $row->category_name,
                        'type' => 'tool',
                        'code' => $row->category_code ?: strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $row->category_name), 0, 10)),
                    ]);
                }
            }

            $incomingStages = null;
            if (!empty($row->incoming_stages)) {
                $incomingStages = is_string($row->incoming_stages)
                    ? json_decode($row->incoming_stages, true)
                    : (array) $row->incoming_stages;
            }

            Tool::updateOrCreate(
                ['code' => $row->code],
                [
                    'name'              => $row->name,
                    'brand'             => $row->brand,
                    'type'              => $row->type,
                    'size'              => $row->size,
                    'notes'             => $row->notes,
                    'category_id'       => $category?->id,
                    'is_active'         => filter_var($row->is_active ?? true, FILTER_VALIDATE_BOOLEAN),
                    'stock_total'       => (int) ($row->stock_total ?? 0),
                    'stock_available'   => (int) ($row->stock_available ?? 0),
                    'stock_borrowed'    => (int) ($row->stock_borrowed ?? 0),
                    'stock_maintenance' => (int) ($row->stock_maintenance ?? 0),
                    'stock_damaged'     => (int) ($row->stock_damaged ?? 0),
                    'incoming_stages'   => $incomingStages,
                ]
            );

            $toolsRestored++;
        }

        // 5. Tarik Inventories (Stok Material per Gudang)
        $cloudInventories = $conn->table('backup_inventories')->get();
        $inventoriesRestored = 0;
        foreach ($cloudInventories as $row) {
            $warehouse = Warehouse::where('code', $row->warehouse_code)->first();
            if (!$warehouse && !empty($row->warehouse_name)) {
                $warehouse = Warehouse::where('name', $row->warehouse_name)->first();
            }
            if (!$warehouse) {
                $warehouse = Warehouse::create([
                    'code'       => $row->warehouse_code ?: 'WH-' . time(),
                    'name'       => $row->warehouse_name ?: 'Gudang Proyek',
                    'type'       => str_contains(strtolower($row->warehouse_name ?? ''), 'pusat') ? 'central' : 'project',
                    'is_central' => str_contains(strtolower($row->warehouse_name ?? ''), 'pusat'),
                ]);
            }

            $material = Material::where('sku', $row->material_sku)->first();
            if ($material) {
                Inventory::updateOrCreate(
                    [
                        'warehouse_id' => $warehouse->id,
                        'material_id'  => $material->id,
                    ],
                    [
                        'quantity'       => (float) ($row->quantity ?? 0),
                        'qty_allocated'  => (float) ($row->qty_allocated ?? 0),
                        'qty_in_transit' => (float) ($row->qty_in_transit ?? 0),
                    ]
                );
                $inventoriesRestored++;
            }
        }

        // 6. Tarik ToolInventories (Stok Alat per Gudang)
        $cloudToolInventories = $conn->table('backup_tool_inventories')->get();
        $toolInventoriesRestored = 0;
        foreach ($cloudToolInventories as $row) {
            $warehouse = Warehouse::where('code', $row->warehouse_code)->first();
            if (!$warehouse && !empty($row->warehouse_name)) {
                $warehouse = Warehouse::where('name', $row->warehouse_name)->first();
            }
            if (!$warehouse) {
                $warehouse = Warehouse::create([
                    'code'       => $row->warehouse_code ?: 'WH-' . time(),
                    'name'       => $row->warehouse_name ?: 'Gudang Proyek',
                    'type'       => str_contains(strtolower($row->warehouse_name ?? ''), 'pusat') ? 'central' : 'project',
                    'is_central' => str_contains(strtolower($row->warehouse_name ?? ''), 'pusat'),
                ]);
            }

            $tool = Tool::where('code', $row->tool_code)->first();
            if ($tool) {
                ToolInventory::updateOrCreate(
                    [
                        'warehouse_id' => $warehouse->id,
                        'tool_id'      => $tool->id,
                    ],
                    [
                        'stock_total'       => (int) ($row->stock_total ?? 0),
                        'stock_available'   => (int) ($row->stock_available ?? 0),
                        'stock_borrowed'    => (int) ($row->stock_borrowed ?? 0),
                        'stock_maintenance' => (int) ($row->stock_maintenance ?? 0),
                        'stock_damaged'     => (int) ($row->stock_damaged ?? 0),
                    ]
                );
                $toolInventoriesRestored++;
            }
        }

        return [
            'success'                   => true,
            'message'                   => "Berhasil menarik {$categoriesRestored} Kategori, {$unitsRestored} Satuan, {$materialsRestored} Material, {$toolsRestored} Alat, {$inventoriesRestored} Stok Material Gudang, dan {$toolInventoriesRestored} Stok Alat Gudang dari Supabase ke database lokal.",
            'categories_restored'       => $categoriesRestored,
            'units_restored'            => $unitsRestored,
            'materials_restored'        => $materialsRestored,
            'tools_restored'            => $toolsRestored,
            'inventories_restored'      => $inventoriesRestored,
            'tool_inventories_restored' => $toolInventoriesRestored,
        ];
    }

    /**
     * Dapatkan perbandingan statistik data Lokal vs Supabase.
     */
    public function getStats(): array
    {
        $localCategoriesCount = Category::count();
        $localUnitsCount = Unit::count();
        $localMaterialsCount = Material::count();
        $localToolsCount = Tool::count();
        $localInventoriesCount = Inventory::count();
        $localToolInventoriesCount = ToolInventory::count();

        $cloudCategoriesCount = null;
        $cloudUnitsCount = null;
        $cloudMaterialsCount = null;
        $cloudToolsCount = null;
        $cloudInventoriesCount = null;
        $cloudToolInventoriesCount = null;
        $lastSyncedAt = null;

        $test = $this->testConnection();

        if ($test['connected']) {
            try {
                $this->ensureTablesExist();
                $conn = DB::connection(self::CONNECTION_NAME);

                $cloudCategoriesCount = $conn->table('backup_categories')->count();
                $cloudUnitsCount = $conn->table('backup_units')->count();
                $cloudMaterialsCount = $conn->table('backup_materials')->count();
                $cloudToolsCount = $conn->table('backup_tools')->count();
                $cloudInventoriesCount = $conn->table('backup_inventories')->count();
                $cloudToolInventoriesCount = $conn->table('backup_tool_inventories')->count();

                $latestCat = $conn->table('backup_categories')->max('synced_at');
                $latestUnit = $conn->table('backup_units')->max('synced_at');
                $latestMat = $conn->table('backup_materials')->max('synced_at');
                $latestTool = $conn->table('backup_tools')->max('synced_at');
                $latestInv = $conn->table('backup_inventories')->max('synced_at');
                $latestToolInv = $conn->table('backup_tool_inventories')->max('synced_at');

                $lastSyncedAt = max($latestCat, $latestUnit, $latestMat, $latestTool, $latestInv, $latestToolInv);
            } catch (Throwable $e) {
                Log::warning('Error fetching cloud stats: ' . $e->getMessage());
            }
        }

        return [
            'connection' => $test,
            'local' => [
                'categories_count'       => $localCategoriesCount,
                'units_count'            => $localUnitsCount,
                'materials_count'        => $localMaterialsCount,
                'tools_count'            => $localToolsCount,
                'inventories_count'      => $localInventoriesCount,
                'tool_inventories_count' => $localToolInventoriesCount,
            ],
            'cloud' => [
                'categories_count'       => $cloudCategoriesCount,
                'units_count'            => $cloudUnitsCount,
                'materials_count'        => $cloudMaterialsCount,
                'tools_count'            => $cloudToolsCount,
                'inventories_count'      => $cloudInventoriesCount,
                'tool_inventories_count' => $cloudToolInventoriesCount,
                'last_synced_at'         => $lastSyncedAt,
            ],
        ];
    }
}

