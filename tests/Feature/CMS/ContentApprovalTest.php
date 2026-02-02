<?php

namespace Tests\Feature\CMS;

use App\Enums\ContentStatus;
use App\Models\CMS\ContentApproval;
use App\Models\CMS\Page;
use App\Models\CMS\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $manager;

    private Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['company_id' => 1]);
        $this->manager = User::factory()->create(['company_id' => 1]);
        $this->site = Site::factory()->create(['company_id' => $this->user->company_id]);
    }

    public function test_can_list_approvals(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'status' => ContentStatus::PENDING,
            'created_by' => $this->user->id,
        ]);

        ContentApproval::factory()->count(3)->create([
            'approvable_type' => Page::class,
            'approvable_id' => $page->id,
            'requested_by' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/cms/approvals');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_can_show_approval(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'created_by' => $this->user->id,
        ]);

        $approval = ContentApproval::factory()->create([
            'approvable_type' => Page::class,
            'approvable_id' => $page->id,
            'requested_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/cms/approvals/{$approval->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $approval->id]);
    }

    public function test_can_approve_content(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'status' => ContentStatus::PENDING,
            'created_by' => $this->user->id,
        ]);

        $approval = ContentApproval::factory()->create([
            'approvable_type' => Page::class,
            'approvable_id' => $page->id,
            'requested_by' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->manager)
            ->postJson("/api/cms/approvals/{$approval->id}/approve");

        $response->assertOk();

        $approval->refresh();
        $this->assertEquals('approved', $approval->status);
        $this->assertEquals($this->manager->id, $approval->reviewed_by);

        $page->refresh();
        $this->assertEquals(ContentStatus::PUBLISHED, $page->status);
    }

    public function test_can_reject_content(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'status' => ContentStatus::PENDING,
            'created_by' => $this->user->id,
        ]);

        $approval = ContentApproval::factory()->create([
            'approvable_type' => Page::class,
            'approvable_id' => $page->id,
            'requested_by' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->manager)
            ->postJson("/api/cms/approvals/{$approval->id}/reject", [
                'reason' => 'Content needs revision',
            ]);

        $response->assertOk();

        $approval->refresh();
        $this->assertEquals('rejected', $approval->status);
        $this->assertEquals($this->manager->id, $approval->reviewed_by);
        $this->assertStringContainsString('revision', $approval->review_notes);

        $page->refresh();
        $this->assertEquals(ContentStatus::DRAFT, $page->status);
    }

    public function test_cannot_approve_already_processed_approval(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'created_by' => $this->user->id,
        ]);

        $approval = ContentApproval::factory()->create([
            'approvable_type' => Page::class,
            'approvable_id' => $page->id,
            'requested_by' => $this->user->id,
            'status' => 'approved',
            'reviewed_by' => $this->manager->id,
        ]);

        $response = $this->actingAs($this->manager)
            ->postJson("/api/cms/approvals/{$approval->id}/approve");

        $response->assertStatus(400);
    }

    public function test_requires_reason_for_rejection(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'created_by' => $this->user->id,
        ]);

        $approval = ContentApproval::factory()->create([
            'approvable_type' => Page::class,
            'approvable_id' => $page->id,
            'requested_by' => $this->user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->manager)
            ->postJson("/api/cms/approvals/{$approval->id}/reject", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    }

    public function test_can_filter_by_status(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'created_by' => $this->user->id,
        ]);

        ContentApproval::factory()->create([
            'approvable_type' => Page::class,
            'approvable_id' => $page->id,
            'requested_by' => $this->user->id,
            'status' => 'pending',
        ]);

        ContentApproval::factory()->create([
            'approvable_type' => Page::class,
            'approvable_id' => $page->id,
            'requested_by' => $this->user->id,
            'status' => 'approved',
            'reviewed_by' => $this->manager->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/cms/approvals?status=pending');

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
