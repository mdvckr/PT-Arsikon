<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin.pusat@arsikon.co.id')->firstOrFail();
    }

    public function test_project_can_be_created_with_description(): void
    {
        $response = $this->actingAs($this->admin)->post(route('projects.store'), [
            'name' => 'Proyek Gedung Uji',
            'code' => 'PRJ-TEST-001',
            'location' => 'Yogyakarta',
            'description' => 'Pembangunan gedung kantor tiga lantai di kawasan industri.',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(6)->toDateString(),
            'status' => 'active',
        ]);

        $response->assertRedirect(route('projects.index'));

        $this->assertDatabaseHas('projects', [
            'code' => 'PRJ-TEST-001',
            'name' => 'Proyek Gedung Uji',
            'description' => 'Pembangunan gedung kantor tiga lantai di kawasan industri.',
            'status' => 'active',
        ]);
    }

    public function test_project_description_can_be_updated(): void
    {
        $project = Project::where('code', 'PRJ-001')->firstOrFail();

        $response = $this->actingAs($this->admin)->put(route('projects.update', $project), [
            'name' => $project->name,
            'code' => $project->code,
            'location' => $project->location,
            'description' => 'Deskripsi proyek yang sudah diperbarui.',
            'start_date' => $project->start_date?->format('Y-m-d'),
            'end_date' => $project->end_date?->format('Y-m-d'),
            'status' => $project->status,
        ]);

        $response->assertRedirect(route('projects.index'));

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'description' => 'Deskripsi proyek yang sudah diperbarui.',
        ]);
    }

    public function test_project_index_filters_by_search_and_status(): void
    {
        Project::create([
            'code' => 'PRJ-PLAN-001',
            'name' => 'Proyek Renovasi Pelabuhan',
            'location' => 'Makassar',
            'description' => 'Pekerjaan dermaga dan fasilitas bongkar muat.',
            'status' => 'planning',
            'start_date' => now()->addMonth()->toDateString(),
        ]);

        $this->actingAs($this->admin)
            ->get(route('projects.index', ['search' => 'Gedung A']))
            ->assertOk()
            ->assertSee('Proyek Pembangunan Gedung A')
            ->assertDontSee('Proyek Pembangunan Gedung B');

        $this->actingAs($this->admin)
            ->get(route('projects.index', ['search' => 'dermaga']))
            ->assertOk()
            ->assertSee('Proyek Renovasi Pelabuhan')
            ->assertDontSee('Proyek Pembangunan Gedung A');

        $this->actingAs($this->admin)
            ->get(route('projects.index', ['status' => 'planning']))
            ->assertOk()
            ->assertSee('Proyek Renovasi Pelabuhan')
            ->assertDontSee('Proyek Pembangunan Gedung A');
    }

    public function test_project_index_shows_description_snippet_and_status_labels(): void
    {
        Project::create([
            'code' => 'PRJ-SNIPPET-001',
            'name' => 'Proyek Jembatan Layang',
            'location' => 'Bandung',
            'description' => 'Pekerjaan struktur jembatan layang sepanjang 1,2 kilometer.',
            'status' => 'on_hold',
            'start_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('projects.index'));

        $response->assertOk()
            ->assertSee('Pekerjaan struktur jembatan layang')
            ->assertSee('Berjalan')
            ->assertSee('Perencanaan')
            ->assertSee('Ditangguhkan');
    }

    public function test_project_form_pages_render_without_emoji_status_labels(): void
    {
        $project = Project::where('code', 'PRJ-001')->firstOrFail();

        $create = $this->actingAs($this->admin)->get(route('projects.create'));
        $create->assertOk()
            ->assertSee('name="description"', false)
            ->assertDontSee('📋')
            ->assertDontSee('⚡')
            ->assertDontSee('⏸️')
            ->assertDontSee('✅');

        $edit = $this->actingAs($this->admin)->get(route('projects.edit', $project));
        $edit->assertOk()
            ->assertSee('name="description"', false)
            ->assertDontSee('📋')
            ->assertDontSee('⚡')
            ->assertDontSee('⏸️')
            ->assertDontSee('✅');
    }
}
