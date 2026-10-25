<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SidebarIconsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);
    }

    public function test_all_sidebar_lucide_icons_render_valid_svg(): void
    {
        $icons = [
            'layout-dashboard',
            'file-plus',
            'truck',
            'circle-plus',
            'receipt',
            'wallet',
            'file-text',
            'banknote',
            'layout-template',
            'wrench',
            'layers',
            'calendar-check',
            'package',
            'package-minus',
            'hand-helping',
            'package-check',
            'undo-2',
            'clipboard-check',
            'chart-column',
            'scroll-text',
            'users',
            'warehouse',
        ];

        foreach ($icons as $icon) {
            $html = Blade::render("<x-lucide-{$icon} class=\"nav-icon\" aria-hidden=\"true\" />");
            $this->assertStringContainsString('<svg', $html, "Ikon lucide-{$icon} harus menghasilkan tag <svg>");
            $this->assertStringContainsString('class="nav-icon"', $html, "Ikon lucide-{$icon} harus memiliki class nav-icon");
            $this->assertStringContainsString('aria-hidden="true"', $html, "Ikon lucide-{$icon} harus memiliki aria-hidden=\"true\"");
        }
    }

    #[DataProvider('roleProvider')]
    public function test_sidebar_renders_lucide_svg_icons_for_role(string $roleName, string $email): void
    {
        $user = User::where('email', $email)->first()
            ?? User::factory()->create(['email' => $email]);
        $user->email_verified_at = now();
        $user->save();
        $user->syncRoles([$roleName]);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();

        $content = $response->getContent();

        // Verifikasi sidebar mengandung svg nav-icon
        $this->assertStringContainsString('class="nav-icon"', $content, "User dengan role {$roleName} harus melihat ikon SVG Lucide di sidebar.");
        $this->assertStringContainsString('<svg', $content, "User dengan role {$roleName} harus memiliki elemen <svg> di sidebar.");
    }

    public static function roleProvider(): array
    {
        return [
            'Owner' => ['Owner', 'owner@arsikon.co.id'],
            'Admin Pusat' => ['Admin Pusat', 'admin.pusat@arsikon.co.id'],
            'Admin Gudang Pusat' => ['Admin Gudang Pusat', 'gudang.pusat@arsikon.co.id'],
            'Admin Gudang Proyek' => ['Admin Gudang Proyek', 'admin.proyek1@arsikon.co.id'],
            'Admin PO' => ['Admin PO', 'admin.po@arsikon.co.id'],
            'Karyawan' => ['Karyawan', 'karyawan@arsikon.co.id'],
        ];
    }

    public function test_sidebar_does_not_contain_old_solid_fontawesome_icons_on_menu_items(): void
    {
        $owner = User::where('email', 'owner@arsikon.co.id')->firstOrFail();

        $response = $this->actingAs($owner)->get(route('dashboard'));
        $response->assertOk();
        $content = $response->getContent();

        // Ambil hanya markup sidebar
        preg_match('/<nav style="flex:1; padding: 6px 0;">(.*?)<\/nav>/s', $content, $matches);
        $sidebarNav = $matches[1] ?? '';

        // Pastikan kelas FA menu lama sudah tidak ada
        $oldFaIcons = [
            'fa-table-cells-large',
            'fa-file-circle-plus',
            'fa-file-invoice-dollar',
            'fa-money-bill-wave',
            'fa-stamp',
            'fa-screwdriver-wrench',
            'fa-layer-group',
            'fa-boxes-stacked',
            'fa-hand-holding',
            'fa-truck-ramp-box',
            'fa-rotate-left',
        ];

        foreach ($oldFaIcons as $oldIcon) {
            $this->assertStringNotContainsString($oldIcon, $sidebarNav, "Ikon lama {$oldIcon} tidak boleh ada lagi di dalam menu sidebar.");
        }
    }
}
