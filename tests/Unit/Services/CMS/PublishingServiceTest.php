<?php

namespace Tests\Unit\Services\CMS;

use App\Enums\ContentStatus;
use App\Models\CMS\ContentApproval;
use App\Models\CMS\Page;
use App\Models\CMS\Site;
use App\Services\CMS\PublishingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishingServiceTest extends TestCase
{
    use RefreshDatabase;

    private PublishingService $service;

    private Site $site;

    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PublishingService::class);
        $this->site = Site::factory()->create();
        $this->userId = 1;
    }

    public function test_publishes_content(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'status' => ContentStatus::DRAFT,
            'created_by' => $this->userId,
        ]);

        $result = $this->service->publish($page, $this->userId);

        $this->assertTrue($result);

        $page->refresh();
        $this->assertEquals(ContentStatus::PUBLISHED, $page->status);
        $this->assertNotNull($page->published_at);
    }

    public function test_unpublishes_content(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'status' => ContentStatus::PUBLISHED,
            'published_at' => now(),
            'created_by' => $this->userId,
        ]);

        $result = $this->service->unpublish($page);

        $this->assertTrue($result);

        $page->refresh();
        $this->assertEquals(ContentStatus::DRAFT, $page->status);
    }

    public function test_requests_approval(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'status' => ContentStatus::DRAFT,
            'created_by' => $this->userId,
        ]);

        $approval = $this->service->requestApproval(
            $page,
            $this->userId,
            'Please review this content'
        );

        $this->assertInstanceOf(ContentApproval::class, $approval);
        $this->assertEquals('pending', $approval->status);
        $this->assertEquals($this->userId, $approval->requested_by);

        $page->refresh();
        $this->assertEquals(ContentStatus::PENDING, $page->status);
    }

    public function test_does_not_create_duplicate_approval_requests(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'status' => ContentStatus::DRAFT,
            'created_by' => $this->userId,
        ]);

        $firstApproval = $this->service->requestApproval($page, $this->userId);
        $secondApproval = $this->service->requestApproval($page, $this->userId);

        $this->assertEquals($firstApproval->id, $secondApproval->id);
        $this->assertCount(1, ContentApproval::where('approvable_id', $page->id)->get());
    }

    public function test_approves_content(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'status' => ContentStatus::PENDING,
            'created_by' => $this->userId,
        ]);

        $approval = ContentApproval::factory()->create([
            'approvable_type' => Page::class,
            'approvable_id' => $page->id,
            'requested_by' => $this->userId,
            'status' => 'pending',
        ]);

        $reviewerId = 2;
        $result = $this->service->approve($approval, $reviewerId);

        $this->assertTrue($result);

        $approval->refresh();
        $this->assertEquals('approved', $approval->status);
        $this->assertEquals($reviewerId, $approval->reviewed_by);

        $page->refresh();
        $this->assertEquals(ContentStatus::PUBLISHED, $page->status);
    }

    public function test_rejects_content(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'status' => ContentStatus::PENDING,
            'created_by' => $this->userId,
        ]);

        $approval = ContentApproval::factory()->create([
            'approvable_type' => Page::class,
            'approvable_id' => $page->id,
            'requested_by' => $this->userId,
            'status' => 'pending',
        ]);

        $reviewerId = 2;
        $reason = 'Content needs improvement';

        $result = $this->service->reject($approval, $reviewerId, $reason);

        $this->assertTrue($result);

        $approval->refresh();
        $this->assertEquals('rejected', $approval->status);
        $this->assertEquals($reviewerId, $approval->reviewed_by);
        $this->assertStringContainsString($reason, $approval->review_notes);

        $page->refresh();
        $this->assertEquals(ContentStatus::DRAFT, $page->status);
    }

    public function test_gets_pending_approvals_for_site(): void
    {
        $page1 = Page::factory()->create([
            'site_id' => $this->site->id,
            'status' => ContentStatus::PENDING,
        ]);

        $page2 = Page::factory()->create([
            'site_id' => $this->site->id,
            'status' => ContentStatus::PENDING,
        ]);

        ContentApproval::factory()->create([
            'approvable_type' => Page::class,
            'approvable_id' => $page1->id,
            'status' => 'pending',
        ]);

        ContentApproval::factory()->create([
            'approvable_type' => Page::class,
            'approvable_id' => $page2->id,
            'status' => 'pending',
        ]);

        $results = $this->service->getPendingApprovals($this->site->id);

        $this->assertArrayHasKey('pages', $results);
        $this->assertArrayHasKey('posts', $results);
        $this->assertCount(2, $results['pages']);
    }
}
