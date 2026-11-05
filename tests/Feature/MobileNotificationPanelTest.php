<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MobileNotificationPanelTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
        $this->user = User::factory()->create();
        $this->user->assignRole('Owner');
    }

    public function test_glass_panel_renders_mobile_responsive_positioning_classes(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertOk();
        // Mobile fixed inset-x-3 and desktop sm:absolute sm:right-0
        $response->assertSee('fixed inset-x-3', false);
        $response->assertSee('sm:absolute', false);
        $response->assertSee('sm:right-0', false);
        $response->assertSee('sm:left-auto', false);
    }

    public function test_glass_panel_includes_mobile_backdrop_overlay(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertOk();
        // Mobile touch dismiss backdrop overlay
        $response->assertSee('id="glass-notification-backdrop"', false);
        $response->assertSee('sm:hidden', false);
    }

    public function test_glass_panel_header_includes_mobile_close_button(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('id="btn-close-notification-panel"', false);
        $response->assertSee('@click="panelOpen = false"', false);
    }

    public function test_glass_panel_uses_viewport_clamped_scroll_height(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('max-h-[calc(100dvh-190px)]', false);
    }
}
