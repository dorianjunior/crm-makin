<?php

namespace Tests\Feature\CMS;

use App\Models\CMS\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'company_id' => 1,
        ]);
    }

    public function test_can_list_sites(): void
    {
        Site::factory()->count(3)->create(['company_id' => $this->user->company_id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/cms/sites');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_site(): void
    {
        $data = [
            'name' => 'Test Site',
            'domain' => 'test.com',
            'description' => 'A test site',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/cms/sites', $data);

        $response->assertCreated()
            ->assertJsonFragment(['name' => 'Test Site']);

        $this->assertDatabaseHas('sites', [
            'name' => 'Test Site',
            'domain' => 'test.com',
            'company_id' => $this->user->company_id,
        ]);
    }

    public function test_can_show_site(): void
    {
        $site = Site::factory()->create(['company_id' => $this->user->company_id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/cms/sites/{$site->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $site->id]);
    }

    public function test_can_update_site(): void
    {
        $site = Site::factory()->create(['company_id' => $this->user->company_id]);

        $data = ['name' => 'Updated Site Name'];

        $response = $this->actingAs($this->user)
            ->putJson("/api/cms/sites/{$site->id}", $data);

        $response->assertOk()
            ->assertJsonFragment(['name' => 'Updated Site Name']);

        $this->assertDatabaseHas('sites', [
            'id' => $site->id,
            'name' => 'Updated Site Name',
        ]);
    }

    public function test_can_delete_site(): void
    {
        $site = Site::factory()->create(['company_id' => $this->user->company_id]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/cms/sites/{$site->id}");

        $response->assertOk();

        $this->assertSoftDeleted('sites', ['id' => $site->id]);
    }

    public function test_can_regenerate_api_key(): void
    {
        $site = Site::factory()->create(['company_id' => $this->user->company_id]);
        $oldApiKey = $site->api_key;

        $response = $this->actingAs($this->user)
            ->postJson("/api/cms/sites/{$site->id}/regenerate-api-key");

        $response->assertOk()
            ->assertJsonStructure(['api_key']);

        $site->refresh();
        $this->assertNotEquals($oldApiKey, $site->api_key);
    }

    public function test_can_activate_site(): void
    {
        $site = Site::factory()->create([
            'company_id' => $this->user->company_id,
            'active' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/cms/sites/{$site->id}/activate");

        $response->assertOk();

        $site->refresh();
        $this->assertTrue($site->active);
    }

    public function test_can_deactivate_site(): void
    {
        $site = Site::factory()->create([
            'company_id' => $this->user->company_id,
            'active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/cms/sites/{$site->id}/deactivate");

        $response->assertOk();

        $site->refresh();
        $this->assertFalse($site->active);
    }

    public function test_cannot_access_other_company_site(): void
    {
        $otherCompanySite = Site::factory()->create(['company_id' => 999]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/cms/sites/{$otherCompanySite->id}");

        $response->assertForbidden();
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/cms/sites', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'domain']);
    }
}
