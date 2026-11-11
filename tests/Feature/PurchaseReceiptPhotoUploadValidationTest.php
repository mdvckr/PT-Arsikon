<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseReceiptPhotoUploadValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $permissions = [
            'view purchase receipts',
            'create purchase receipts',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => 'admin-po-nota', 'guard_name' => 'web']);
        $role->syncPermissions($permissions);

        $this->user = User::factory()->create();
        $this->user->assignRole('admin-po-nota');

        $this->project = Project::create([
            'code'                => 'PRJ-FOTO-TEST',
            'name'                => 'Proyek Test Upload Foto Wajib',
            'status'              => 'active',
            'operational_balance' => 5000000.00,
        ]);

        $wh = Warehouse::create([
            'code'       => 'GDG-TEST',
            'name'       => 'Gudang Lapangan Test',
            'project_id' => $this->project->id,
            'is_central' => false,
        ]);

        $this->user->warehouses()->attach($wh->id);
    }

    public function test_creating_purchase_receipt_without_image_fails_validation(): void
    {
        $response = $this->actingAs($this->user)->post(route('purchase-receipts.store'), [
            'project_id'     => $this->project->id,
            'receipt_date'   => now()->toDateString(),
            'payment_source' => 'project_cash',
            'items'          => [
                [
                    'material_name' => 'Semen Gresik',
                    'quantity'      => 5,
                    'unit_price'    => 70000,
                ],
            ],
            // 'image' intentionally not provided
        ]);

        $response->assertSessionHasErrors('image');
    }

    public function test_creating_purchase_receipt_with_valid_image_succeeds(): void
    {
        Storage::fake('private');

        $imageFile = UploadedFile::fake()->image('nota_asli.jpg', 600, 600);

        $response = $this->actingAs($this->user)->post(route('purchase-receipts.store'), [
            'project_id'     => $this->project->id,
            'receipt_date'   => now()->toDateString(),
            'payment_source' => 'project_cash',
            'image'          => $imageFile,
            'items'          => [
                [
                    'material_name' => 'Semen Gresik',
                    'quantity'      => 5,
                    'unit_price'    => 70000,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('purchase_receipts', [
            'project_id' => $this->project->id,
        ]);
    }
}
