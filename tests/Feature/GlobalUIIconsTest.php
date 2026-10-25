<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalUIIconsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);
    }

    public function test_login_page_renders_lucide_icons_and_no_legacy_fa_icons(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        // Memastikan tag SVG dari Blade Lucide muncul
        $response->assertSee('<svg', false);

        // Memastikan tidak ada lagi tag <i> Font Awesome lama
        $content = $response->getContent();
        $this->assertDoesNotMatchRegularExpression('/<i\s+class=["\'][^"\']*fa-/i', $content, 'Halaman login tidak boleh memuat tag <i> Font Awesome');
    }

    public function test_owner_pages_render_lucide_svgs_without_legacy_icons(): void
    {
        $owner = User::where('email', 'owner@arsikon.co.id')->firstOrFail();
        $owner->forceFill(['email_verified_at' => now()])->save();

        $routes = [
            '/dashboard',
            '/materials',
            '/tools',
            '/inventory',
            '/purchase-orders',
            '/goods-receipts',
            '/material-requests',
            '/material-usages',
            '/distributions',
            '/stock-opnames',
            '/reports',
            '/profile',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($owner)->get($route);
            $this->assertEquals(200, $response->status(), "Route {$route} (Owner) harus mengembalikan status 200");
            $response->assertSee('<svg', false, "Route {$route} harus memuat tag SVG Lucide");

            $content = $response->getContent();
            $this->assertDoesNotMatchRegularExpression(
                '/<i\s+class=["\'][^"\']*fa-/i',
                $content,
                "Route {$route} tidak boleh memuat tag <i> Font Awesome lama"
            );
        }
    }

    public function test_admin_pusat_pages_render_lucide_svgs_without_legacy_icons(): void
    {
        $adminPusat = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
        $adminPusat->forceFill(['email_verified_at' => now()])->save();

        $routes = [
            '/users',
            '/roles',
            '/warehouses',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($adminPusat)->get($route);
            $this->assertEquals(200, $response->status(), "Route {$route} (Admin Pusat) harus mengembalikan status 200");
            $response->assertSee('<svg', false, "Route {$route} harus memuat tag SVG Lucide");

            $content = $response->getContent();
            $this->assertDoesNotMatchRegularExpression(
                '/<i\s+class=["\'][^"\']*fa-/i',
                $content,
                "Route {$route} tidak boleh memuat tag <i> Font Awesome lama"
            );
        }
    }
}
