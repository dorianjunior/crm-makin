<?php

namespace Tests\Unit\Services\CMS;

use App\Enums\ContentStatus;
use App\Models\CMS\Page;
use App\Models\CMS\Post;
use App\Models\CMS\Site;
use App\Services\CMS\ContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentServiceTest extends TestCase
{
    use RefreshDatabase;

    private ContentService $service;

    private Site $site;

    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(ContentService::class);
        $this->site = Site::factory()->create();
        $this->userId = 1;
    }

    public function test_creates_page_with_generated_slug(): void
    {
        $data = [
            'site_id' => $this->site->id,
            'title' => 'Test Page',
            'content' => '<p>Content</p>',
            'status' => ContentStatus::DRAFT,
            'created_by' => $this->userId,
        ];

        $page = $this->service->createPage($data);

        $this->assertInstanceOf(Page::class, $page);
        $this->assertEquals('test-page', $page->slug);
        $this->assertEquals('Test Page', $page->title);
        $this->assertDatabaseHas('pages', ['id' => $page->id]);
    }

    public function test_creates_page_with_custom_slug(): void
    {
        $data = [
            'site_id' => $this->site->id,
            'title' => 'Test Page',
            'slug' => 'custom-slug',
            'content' => '<p>Content</p>',
            'status' => ContentStatus::DRAFT,
            'created_by' => $this->userId,
        ];

        $page = $this->service->createPage($data);

        $this->assertEquals('custom-slug', $page->slug);
    }

    public function test_generates_unique_slug_on_conflict(): void
    {
        // Create first page
        Page::factory()->create([
            'site_id' => $this->site->id,
            'slug' => 'test-page',
            'created_by' => $this->userId,
        ]);

        // Create second page with same title
        $data = [
            'site_id' => $this->site->id,
            'title' => 'Test Page',
            'content' => '<p>Content</p>',
            'status' => ContentStatus::DRAFT,
            'created_by' => $this->userId,
        ];

        $page = $this->service->createPage($data);

        $this->assertStringStartsWith('test-page-', $page->slug);
        $this->assertNotEquals('test-page', $page->slug);
    }

    public function test_updates_page(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'title' => 'Original Title',
            'created_by' => $this->userId,
        ]);

        $updatedPage = $this->service->updatePage($page, [
            'title' => 'Updated Title',
            'created_by' => $this->userId,
        ]);

        $this->assertEquals('Updated Title', $updatedPage->title);
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_updates_page_slug_when_changed(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'slug' => 'original-slug',
            'created_by' => $this->userId,
        ]);

        $updatedPage = $this->service->updatePage($page, [
            'slug' => 'new-slug',
            'created_by' => $this->userId,
        ]);

        $this->assertEquals('new-slug', $updatedPage->slug);
    }

    public function test_deletes_page(): void
    {
        $page = Page::factory()->create([
            'site_id' => $this->site->id,
            'created_by' => $this->userId,
        ]);

        $result = $this->service->deletePage($page);

        $this->assertTrue($result);
        $this->assertSoftDeleted('pages', ['id' => $page->id]);
    }

    public function test_creates_post_with_generated_slug(): void
    {
        $data = [
            'site_id' => $this->site->id,
            'title' => 'Test Post',
            'content' => '<p>Content</p>',
            'status' => ContentStatus::DRAFT,
            'created_by' => $this->userId,
        ];

        $post = $this->service->createPost($data);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals('test-post', $post->slug);
        $this->assertEquals('Test Post', $post->title);
    }

    public function test_gets_published_pages_for_site(): void
    {
        Page::factory()->count(3)->create([
            'site_id' => $this->site->id,
            'status' => ContentStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        Page::factory()->create([
            'site_id' => $this->site->id,
            'status' => ContentStatus::DRAFT,
        ]);

        $pages = $this->service->getPublishedPages($this->site->id);

        $this->assertCount(3, $pages);
        $pages->each(function ($page) {
            $this->assertEquals(ContentStatus::PUBLISHED, $page->status);
        });
    }

    public function test_searches_pages_by_keyword(): void
    {
        Page::factory()->create([
            'site_id' => $this->site->id,
            'title' => 'Laravel Tutorial',
            'content' => '<p>Learn Laravel</p>',
            'status' => ContentStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        Page::factory()->create([
            'site_id' => $this->site->id,
            'title' => 'PHP Guide',
            'content' => '<p>Learn PHP</p>',
            'status' => ContentStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        // TODO: Implement searchPages method in ContentService
        $this->markTestSkipped('searchPages method not yet implemented');
    }
}
