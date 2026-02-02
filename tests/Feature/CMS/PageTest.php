<?php

namespace Tests\Feature\CMS;

use App\Enums\ContentStatus;
use App\Models\CMS\Page;
use App\Models\CMS\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['company_id' => 1]);
        $this->site = Site::factory()->create(['company_id' => $this->user->company_id]);
    }

    public function test_can_list_pages(): void
    {
        Page::factory()->count(3)->create(['site_id' => $this->site->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/cms/pages');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_filter_pages_by_site(): void
    {
        Page::factory()->count(2)->create(['site_id' => $this->site->id]);

        $otherSite = Site::factory()->create(['company_id' => $this->user->company_id]);
        Page::factory()->create(['site_id' => $otherSite->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/cms/pages?site_id={$this->site->id}");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_can_create_page(): void
    {
        $data = [
            'site_id' => $this->site->id,
            'title' => 'Test Page',
            'content' => '<p>Test content</p>',
            'status' => ContentStatus::DRAFT->value,
            'meta_title' => 'Test Meta',
            'created_by' => $this->user->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/cms/pages', $data);

        $response->assertCreated()
            ->assertJsonFragment(['title' => 'Test Page']);

        $this->assertDatabaseHas('pages', [
            'title' => 'Test Page',
            'site_id' => $this->site->id,
        ]);
    }

    public function test_generates_slug_automatically(): void
    {
        $data = [
            'site_id' => $this->site->id,
            'title' => 'Test Page Title',
            'content' => '<p>Content</p>',
            'status' => ContentStatus::DRAFT->value,
            'created_by' => $this->user->id,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/cms/pages', $data);

        $response->assertCreated()
            ->assertJsonPath('data.slug', 'test-page-title');
    }

    public function test_can_update_page(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'created_by' => $this->user->id,
        ]);

        $data = [
            'title' => 'Updated Title',
            'created_by' => $this->user->id,
        ];

        $response = $this->actingAs($this->user)
            ->putJson("/api/cms/pages/{$page->id}", $data);

        $response->assertOk()
            ->assertJsonFragment(['title' => 'Updated Title']);
    }

    public function test_can_delete_page(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/cms/pages/{$page->id}");

        $response->assertOk();
        $this->assertSoftDeleted('pages', ['id' => $page->id]);
    }

    public function test_can_publish_page(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'status' => ContentStatus::DRAFT,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/cms/pages/{$page->id}/publish");

        $response->assertOk();

        $page->refresh();
        $this->assertEquals(ContentStatus::PUBLISHED, $page->status);
        $this->assertNotNull($page->published_at);
    }

    public function test_can_unpublish_page(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'status' => ContentStatus::PUBLISHED,
            'published_at' => now(),
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/cms/pages/{$page->id}/unpublish");

        $response->assertOk();

        $page->refresh();
        $this->assertEquals(ContentStatus::DRAFT, $page->status);
    }

    public function test_can_request_approval(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'status' => ContentStatus::DRAFT,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/cms/pages/{$page->id}/request-approval", [
                'message' => 'Please review',
            ]);

        $response->assertOk()
            ->assertJsonStructure(['approval_id']);

        $page->refresh();
        $this->assertEquals(ContentStatus::PENDING, $page->status);
    }

    public function test_cannot_access_other_company_page(): void
    {
        $otherSite = Site::factory()->create(['company_id' => 999]);
        $otherPage = Page::factory()->create([
            'site_id' => $otherSite->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/cms/pages/{$otherPage->id}");

        $response->assertForbidden();
    }

    public function test_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/cms/pages', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['site_id', 'title', 'content', 'status']);
    }
}
