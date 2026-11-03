<?php

namespace Tests\Feature;

use App\Exports\PurchaseOrderItemTemplateExport;
use App\Imports\PurchaseOrderItemImport;
use App\Models\Category;
use App\Models\Material;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class PurchaseOrderItemExcelImportTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminPo;
    protected Material $material;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->adminPo = User::firstOrCreate(
            ['email' => 'admin.po.excel@arsikon.com'],
            ['name' => 'Admin PO Excel Test', 'password' => bcrypt('password'), 'email_verified_at' => now()]
        );
        $this->adminPo->syncRoles(['Admin PO']);

        $category = Category::firstOrCreate(['name' => 'Struktur']);
        $unitSak = Unit::firstOrCreate(['code' => 'SAK'], ['name' => 'Sak']);

        $this->material = Material::firstOrCreate(
            ['sku' => 'MAT-TEST-EXCEL-001'],
            [
                'name'        => 'Semen Gresik 50kg',
                'category_id' => $category->id,
                'unit_id'     => $unitSak->id,
            ]
        );
    }

    public function test_template_export_contains_correct_headers_and_sample_rows(): void
    {
        $export = new PurchaseOrderItemTemplateExport();

        $headings = $export->headings();
        $this->assertEquals([
            'Nama Barang / SKU',
            'Jumlah (Qty)',
            'Satuan',
            'Harga Satuan (Rp)',
            'Catatan / Keterangan',
        ], $headings);

        $samples = $export->array();
        $this->assertNotEmpty($samples);
        $this->assertCount(5, $samples[0]);
    }

    public function test_can_parse_valid_excel_items_with_material_matching_and_custom_items(): void
    {
        $importer = new PurchaseOrderItemImport();

        $rows = [
            // Row 1: Cocok dengan Master Material berdasarkan Nama
            [
                'nama_barang'          => 'Semen Gresik 50kg',
                'jumlah_qty'           => '25',
                'satuan'               => 'sak',
                'harga_satuan_rp'      => '68000',
                'catatan_keterangan'   => 'Kebutuhan cor balok',
            ],
            // Row 2: Barang Proyek / Custom (Non-katalog)
            [
                'nama_barang'          => 'Paku Seng Ulir 7cm',
                'jumlah_qty'           => '5',
                'satuan'               => 'dus',
                'harga_satuan_rp'      => '45000',
                'catatan_keterangan'   => 'Beli di toko terdekat',
            ],
            // Row 3: Baris tidak valid (jumlah kosong / 0) -> Wajib dilewati (skipped)
            [
                'nama_barang'          => 'Bata Ringan Rusak',
                'jumlah_qty'           => '0',
                'satuan'               => 'm3',
                'harga_satuan_rp'      => '700000',
                'catatan_keterangan'   => 'Dilewati',
            ],
            // Row 4: Baris kosong tanpa nama -> Wajib dilewati
            [
                'nama_barang'          => '',
                'jumlah_qty'           => '10',
                'satuan'               => 'pcs',
                'harga_satuan_rp'      => '1000',
                'catatan_keterangan'   => '',
            ],
        ];

        $parsedItems = $importer->parseRows($rows);

        $this->assertCount(2, $parsedItems);

        // Assert Item 1: Master matched
        $item1 = $parsedItems[0];
        $this->assertEquals($this->material->id, $item1['material_id']);
        $this->assertEquals('Semen Gresik 50kg', $item1['name']);
        $this->assertEquals(25.0, (float)$item1['quantity']);
        $this->assertEquals('sak', $item1['unit']);
        $this->assertEquals(68000.0, (float)$item1['unit_price']);
        $this->assertEquals(1700000.0, (float)$item1['subtotal']);
        $this->assertEquals('Kebutuhan cor balok', $item1['notes']);
        $this->assertFalse($item1['is_custom']);

        // Assert Item 2: Custom item
        $item2 = $parsedItems[1];
        $this->assertNull($item2['material_id']);
        $this->assertEquals('Paku Seng Ulir 7cm', $item2['name']);
        $this->assertEquals(5.0, (float)$item2['quantity']);
        $this->assertEquals('dus', $item2['unit']);
        $this->assertEquals(45000.0, (float)$item2['unit_price']);
        $this->assertEquals(225000.0, (float)$item2['subtotal']);
        $this->assertEquals('Beli di toko terdekat', $item2['notes']);
        $this->assertTrue($item2['is_custom']);
    }

    public function test_admin_po_can_download_items_template(): void
    {
        $response = $this->actingAs($this->adminPo)
            ->get(route('purchase-orders.template-items-excel'));

        $response->assertOk();
        $this->assertTrue(
            str_contains((string)$response->headers->get('content-disposition'), 'attachment') ||
            str_contains((string)$response->headers->get('content-type'), 'spreadsheet') ||
            str_contains((string)$response->headers->get('content-type'), 'vnd.openxmlformats')
        );
    }

    public function test_admin_po_can_import_excel_items_via_endpoint(): void
    {
        // Buat file CSV sederhana dengan baris header dan 2 baris item
        $content = "Nama Barang / SKU,Jumlah (Qty),Satuan,Harga Satuan (Rp),Catatan / Keterangan\n";
        $content .= "Semen Gresik 50kg,30,sak,68000,Cor Dak Lt 2\n";
        $content .= "Besi Hollow 4x4,15,batang,95000,Rangka Plafon\n";

        $file = UploadedFile::fake()->createWithContent('test_items.csv', $content);

        $response = $this->actingAs($this->adminPo)
            ->postJson(route('purchase-orders.import-items-excel'), [
                'file' => $file,
            ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'message',
            'items' => [
                '*' => [
                    'material_id',
                    'name',
                    'quantity',
                    'unit',
                    'unit_price',
                    'subtotal',
                    'notes',
                    'is_custom',
                ]
            ]
        ]);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertCount(2, $data['items']);
        $this->assertEquals($this->material->id, $data['items'][0]['material_id']);
        $this->assertEquals('Semen Gresik 50kg', $data['items'][0]['name']);
        $this->assertEquals(30, $data['items'][0]['quantity']);
        $this->assertNull($data['items'][1]['material_id']);
        $this->assertTrue($data['items'][1]['is_custom']);
    }

    public function test_import_endpoint_validates_required_file(): void
    {
        $response = $this->actingAs($this->adminPo)
            ->postJson(route('purchase-orders.import-items-excel'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }
}
