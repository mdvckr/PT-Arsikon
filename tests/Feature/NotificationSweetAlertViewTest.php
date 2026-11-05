<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationSweetAlertViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_sweetalert2_pastel_assets_and_flash_script(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Owner');

        $response = $this->actingAs($user)
            ->withSession(['success' => 'Operasi berhasil disimpan'])
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('sweetalert2');
        $response->assertSee('sweetalert-pastel-toast.css');
        $response->assertSee('sweetalert-notifications.js');
        $response->assertSee('Operasi berhasil disimpan');
    }

    public function test_sweetalert_notifications_script_positions_toast_at_top_center(): void
    {
        $script = file_get_contents(public_path('js/sweetalert-notifications.js'));
        $this->assertStringContainsString("position: 'top'", $script);
        $this->assertStringNotContainsString("position: 'top-end'", $script);
    }

    public function test_sweetalert_pastel_css_configures_top_center_and_pill_flex_order(): void
    {
        $css = file_get_contents(public_path('css/sweetalert-pastel-toast.css'));
        $this->assertStringContainsString('.swal2-container.swal2-top', $css);
        $this->assertStringContainsString('transform: translateX(-50%)', $css);
        $this->assertStringContainsString('order: 1 !important', $css);
        $this->assertStringContainsString('order: 2 !important', $css);
    }
}

