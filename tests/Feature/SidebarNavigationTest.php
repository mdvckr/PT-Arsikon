<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $adminPO;
    protected User $adminProyek;
    protected User $karyawan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->owner = User::where('email', 'owner@arsikon.co.id')->first()
            ?? User::factory()->create(['email' => 'owner@arsikon.co.id']);
        $this->owner->syncRoles(['Owner']);

        $this->adminPO = User::where('email', 'admin.po@arsikon.co.id')->first()
            ?? User::factory()->create(['email' => 'admin.po@arsikon.co.id']);
        $this->adminPO->syncRoles(['Admin PO']);

        $this->adminProyek = User::where('email', 'admin.proyek1@arsikon.co.id')->first()
            ?? User::factory()->create(['email' => 'admin.proyek1@arsikon.co.id']);
        $this->adminProyek->syncRoles(['Admin Gudang Proyek']);

        $this->karyawan = User::where('email', 'karyawan@arsikon.co.id')->first()
            ?? User::factory()->create(['email' => 'karyawan@arsikon.co.id']);
        $this->karyawan->syncRoles(['Karyawan']);
    }

    public function test_sidebar_group_order_is_teknis_pengadaan_logistik(): void
    {
        $response = $this->actingAs($this->owner)->get(route('dashboard'));
        $response->assertOk();

        $content = $response->getContent();

        $posDashboard = strpos($content, 'Dashboard');
        $posTeknis = strpos($content, 'Teknis');
        $posPengadaan = strpos($content, 'Pengadaan');
        $posLogistik = strpos($content, 'Logistik');

        $this->assertNotFalse($posDashboard);
        $this->assertNotFalse($posTeknis);
        $this->assertNotFalse($posPengadaan);
        $this->assertNotFalse($posLogistik);

        // Dashboard paling atas, diikuti Teknis -> Pengadaan -> Logistik
        $this->assertTrue($posDashboard < $posTeknis, 'Dashboard harus berada di atas Teknis');
        $this->assertTrue($posTeknis < $posPengadaan, 'Grup Teknis harus berada sebelum Pengadaan');
        $this->assertTrue($posPengadaan < $posLogistik, 'Grup Pengadaan harus berada sebelum Logistik');
    }

    public function test_po_lapangan_is_located_under_pengadaan_and_links_to_material_requests(): void
    {
        $response = $this->actingAs($this->adminPO)->get(route('dashboard'));
        $response->assertOk();

        $content = $response->getContent();

        // Cek link PO Lapangan mengarah ke route material-requests.index
        $mrRouteUrl = route('material-requests.index');
        $this->assertStringContainsString($mrRouteUrl, $content);
        $this->assertStringContainsString('PO Lapangan', $content);

        // Pastikan PO Lapangan muncul setelah label Pengadaan dan sebelum label Logistik
        $posPengadaan = strpos($content, 'Pengadaan');
        $posPoLapangan = strpos($content, 'PO Lapangan');
        $posLogistik = strpos($content, 'Logistik');

        $this->assertTrue($posPengadaan < $posPoLapangan, 'PO Lapangan harus berada di dalam grup Pengadaan');
        $this->assertTrue($posPoLapangan < $posLogistik, 'PO Lapangan harus muncul sebelum grup Logistik');
    }

    public function test_admin_po_sidebar_visibility_and_restrictions(): void
    {
        $response = $this->actingAs($this->adminPO)->get(route('dashboard'));
        $response->assertOk();

        // Boleh muncul:
        $response->assertSee('PO Lapangan');
        $response->assertSee('Permintaan Pengadaan');
        $response->assertSee('Purchase Order (PO)');
        $response->assertSee('Nota Pembelian Harian');
        $response->assertSee('Pembayaran');
        $response->assertSee('Surat Jalan (Distribusi)');
        $response->assertSee('Inventori');

        // Tidak boleh muncul (karena Admin PO tidak memiliki izin view goods receipts):
        $response->assertDontSee('Penerimaan Barang (GR)');
        // Dan tidak punya akses Admin Sistem
        $response->assertDontSee('Admin Sistem');
    }

    public function test_admin_gudang_proyek_sidebar_visibility_and_restrictions(): void
    {
        $response = $this->actingAs($this->adminProyek)->get(route('dashboard'));
        $response->assertOk();

        // Boleh muncul:
        $response->assertSee('Penerimaan Barang (GR)');
        $response->assertSee('Surat Jalan (Distribusi)');
        $response->assertSee('Pemakaian Material');
        $response->assertSee('Retur Material');
        $response->assertSee('PO Lapangan');

        // Tidak boleh muncul:
        // Admin Gudang Proyek tidak boleh akses Purchase Order (PO), Nota Pembelian, Pembayaran
        $response->assertDontSee('Purchase Order (PO)');
        $response->assertDontSee('Nota Pembelian Harian');
        $response->assertDontSee('Pembayaran');
        $response->assertDontSee('Admin Sistem');
    }

    public function test_karyawan_sidebar_visibility_and_restrictions(): void
    {
        $response = $this->actingAs($this->karyawan)->get(route('dashboard'));
        $response->assertOk();

        // Karyawan boleh: PO Lapangan, Pemakaian Material, Peminjaman Alat, Surat Jalan
        $response->assertSee('PO Lapangan');
        $response->assertSee('Pemakaian Material');
        $response->assertSee('Peminjaman Alat');
        $response->assertSee('Surat Jalan (Distribusi)');

        // Karyawan DILARANG:
        $response->assertDontSee('Purchase Order (PO)');
        $response->assertDontSee('Permintaan Pengadaan');
        $response->assertDontSee('Nota Pembelian Harian');
        $response->assertDontSee('Pembayaran');
        $response->assertDontSee('Penerimaan Barang (GR)');
        $response->assertDontSee('Retur Material');
        $response->assertDontSee('Log Harian Proyek');
        $response->assertDontSee('Laporan & Audit');
        $response->assertDontSee('Admin Sistem');
    }

    public function test_empty_group_is_not_rendered_when_user_has_no_permissions(): void
    {
        // Buat user tanpa izin sama sekali kecuali login
        $restrictedUser = User::factory()->create([
            'email' => 'restricted.user@arsikon.co.id',
            'name' => 'Restricted User',
        ]);
        // Tidak diberikan role apapun

        $response = $this->actingAs($restrictedUser)->get(route('dashboard'));
        $response->assertOk();

        // Seluruh 3 grup harus disembunyikan karena tidak ada satupun item yang berhak diakses
        $response->assertDontSee('Teknis');
        $response->assertDontSee('Pengadaan');
        $response->assertDontSee('Logistik');
        $response->assertDontSee('Laporan & Audit');
        $response->assertDontSee('Admin Sistem');

        // Hanya Dashboard yang muncul
        $response->assertSee('Dashboard');
    }

    public function test_active_route_opens_group_automatically(): void
    {
        // Kunjungi halaman di dalam grup Pengadaan (PO Lapangan)
        $response = $this->actingAs($this->adminPO)->get(route('material-requests.index'));
        $response->assertOk();

        $content = $response->getContent();

        // Grup Pengadaan harus diinisialisasi dengan open: true
        $this->assertStringContainsString('x-data="{ open: true }"', $content);
    }
}
