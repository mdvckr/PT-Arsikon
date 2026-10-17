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
        $posTeknis = strpos($content, 'TEKNIS');
        $posPengadaan = strpos($content, 'PENGADAAN');
        $posLogistik = strpos($content, 'LOGISTIK');

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

        // Pastikan PO Lapangan muncul setelah label Pengadaan
        $posPengadaan = strpos($content, 'PENGADAAN');
        $posPoLapangan = strpos($content, 'PO Lapangan');

        $this->assertNotFalse($posPengadaan);
        $this->assertNotFalse($posPoLapangan);
        $this->assertTrue($posPengadaan < $posPoLapangan, 'PO Lapangan harus berada di dalam grup Pengadaan');

        // Untuk Admin PO, grup Teknis dan Logistik dihapus/disembunyikan dari sidebar
        $this->assertStringNotContainsString('nav-section-label">TEKNIS', $content);
        $this->assertStringNotContainsString('nav-section-label">LOGISTIK', $content);
    }

    private function getSidebarContent(string $html): string
    {
        if (preg_match('/<aside class="sidebar"[^>]*>(.*?)<\/aside>/s', $html, $matches)) {
            return $matches[1];
        }
        return $html;
    }

    public function test_admin_po_sidebar_visibility_and_restrictions(): void
    {
        $response = $this->actingAs($this->adminPO)->get(route('dashboard'));
        $response->assertOk();

        $sidebar = $this->getSidebarContent($response->getContent());

        // Boleh muncul di bawah Pengadaan:
        $this->assertStringContainsString('PO Lapangan', $sidebar);
        $this->assertStringContainsString('Purchase Order (PO)', $sidebar);
        $this->assertStringContainsString('Nota Pembelian Harian', $sidebar);
        $this->assertStringContainsString('Pembayaran', $sidebar);
        $this->assertStringContainsString('Surat Jalan', $sidebar);
        $this->assertStringContainsString('Template Kop Surat', $sidebar);

        // Tidak boleh muncul di sidebar (Logistik dihapus untuk Admin PO, Teknis disatukan ke Pengadaan, tidak ada GR, tidak ada Admin Sistem, tidak ada dropdown chevron):
        $this->assertStringNotContainsString('nav-section-label">LOGISTIK', $sidebar);
        $this->assertStringNotContainsString('nav-section-label">TEKNIS', $sidebar);
        $this->assertStringNotContainsString('nav-section-chevron', $sidebar);
        $this->assertStringNotContainsString('Inventori', $sidebar);
        $this->assertStringNotContainsString('Penerimaan Barang', $sidebar);
        $this->assertStringNotContainsString('Admin Sistem', $sidebar);
    }

    public function test_admin_gudang_proyek_sidebar_visibility_and_restrictions(): void
    {
        $response = $this->actingAs($this->adminProyek)->get(route('dashboard'));
        $response->assertOk();

        $sidebar = $this->getSidebarContent($response->getContent());

        // Boleh muncul:
        $this->assertStringContainsString('Penerimaan Barang', $sidebar);
        $this->assertStringContainsString('Surat Jalan', $sidebar);
        $this->assertStringContainsString('Pemakaian Material', $sidebar);
        $this->assertStringContainsString('Retur Material', $sidebar);
        $this->assertStringContainsString('PO Lapangan', $sidebar);

        // Tidak boleh muncul:
        // Admin Gudang Proyek tidak boleh akses Pengadaan (termasuk Nota Pembelian Harian, Purchase Order, Pembayaran), Admin Sistem, dan sidebar tanpa dropdown chevron
        $this->assertStringNotContainsString('nav-section-label">PENGADAAN', $sidebar);
        $this->assertStringNotContainsString('Nota Pembelian Harian', $sidebar);
        $this->assertStringNotContainsString('Purchase Order (PO)', $sidebar);
        $this->assertStringNotContainsString('Pembayaran', $sidebar);
        $this->assertStringNotContainsString('Admin Sistem', $sidebar);
        $this->assertStringNotContainsString('nav-section-chevron', $sidebar);
    }

    public function test_karyawan_sidebar_visibility_and_restrictions(): void
    {
        $response = $this->actingAs($this->karyawan)->get(route('dashboard'));
        $response->assertOk();

        $sidebar = $this->getSidebarContent($response->getContent());

        // Karyawan boleh: PO Lapangan, Pemakaian Material, Peminjaman Alat, Surat Jalan
        $this->assertStringContainsString('PO Lapangan', $sidebar);
        $this->assertStringContainsString('Pemakaian Material', $sidebar);
        $this->assertStringContainsString('Peminjaman Alat', $sidebar);
        $this->assertStringContainsString('Surat Jalan', $sidebar);

        // Karyawan DILARANG di sidebar:
        $this->assertStringNotContainsString('Purchase Order (PO)', $sidebar);
        $this->assertStringNotContainsString('Permintaan Pengadaan', $sidebar);
        $this->assertStringNotContainsString('Nota Pembelian Harian', $sidebar);
        $this->assertStringNotContainsString('Pembayaran', $sidebar);
        $this->assertStringNotContainsString('Penerimaan Barang', $sidebar);
        $this->assertStringNotContainsString('Retur Material', $sidebar);
        $this->assertStringNotContainsString('Log Harian Proyek', $sidebar);
        $this->assertStringNotContainsString('Laporan & Audit', $sidebar);
        $this->assertStringNotContainsString('Admin Sistem', $sidebar);
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
        // Kunjungi halaman di dalam grup Pengadaan (Purchase Order) sebagai Owner (yang menggunakan dropdown accordion)
        $response = $this->actingAs($this->owner)->get(route('purchase-orders.index'));
        $response->assertOk();

        $content = $response->getContent();

        // Grup Pengadaan harus diinisialisasi dengan open: true
        $this->assertStringContainsString('x-data="{ open: true }"', $content);
    }

    public function test_redundant_menu_items_are_removed_from_sidebar(): void
    {
        $response = $this->actingAs($this->owner)->get(route('dashboard'));
        $response->assertOk();

        // Menu kembar Penerimaan Surat Jalan harus sudah dihilangkan (cukup Surat Jalan (Distribusi))
        $response->assertDontSee('Penerimaan Surat Jalan');

        // Menu Permintaan Pengadaan disatukan ke PO Lapangan
        $response->assertDontSee('Permintaan Pengadaan');

        // Menu Jabatan & Hak Akses dihapus dari sidebar
        $response->assertDontSee('Jabatan & Hak Akses');
    }
}
