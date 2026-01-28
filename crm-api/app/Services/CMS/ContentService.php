<?php

namespace App\Services\CMS;

use App\Models\CMS\Page;
use App\Models\CMS\Post;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ContentService
{
    public function __construct(
        protected VersioningService $versioningService
    ) {}

    // Page Methods
    public function createPage(array $data): Page
    {
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title'], Page::class);
        }

        $page = Page::create($data);

        // Create initial version
        $this->versioningService->createVersion($page, $data['created_by']);

        return $page;
    }

    public function updatePage(Page $page, array $data): Page
    {
        // Ensure slug is unique if changed
        if (isset($data['slug']) && $data['slug'] !== $page->slug) {
            $data['slug'] = $this->generateUniqueSlug($data['slug'], Page::class, $page->id);
        }

        $page->update($data);

        // Create new version
        if (isset($data['created_by'])) {
            $this->versioningService->createVersion($page, $data['created_by'], $data['change_summary'] ?? null);
        }

        return $page->fresh();
    }

    public function deletePage(Page $page): bool
    {
        return $page->delete();
    }

    // Post Methods
    public function createPost(array $data): Post
    {
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title'], Post::class);
        }

        $post = Post::create($data);

        // Create initial version
        $this->versioningService->createVersion($post, $data['created_by']);

        return $post;
    }

    public function updatePost(Post $post, array $data): Post
    {
        // Ensure slug is unique if changed
        if (isset($data['slug']) && $data['slug'] !== $post->slug) {
            $data['slug'] = $this->generateUniqueSlug($data['slug'], Post::class, $post->id);
        }

        $post->update($data);

        // Create new version
        if (isset($data['created_by'])) {
            $this->versioningService->createVersion($post, $data['created_by'], $data['change_summary'] ?? null);
        }

        return $post->fresh();
    }

    public function deletePost(Post $post): bool
    {
        return $post->delete();
    }

    // Common Methods
    public function getPublishedPages(int $siteId): Collection
    {
        return Page::where('site_id', $siteId)
            ->published()
            ->orderBy('order')
            ->get();
    }

    public function getPublishedPosts(int $siteId, ?int $categoryId = null): Collection
    {
        $query = Post::where('site_id', $siteId)->published();

        if ($categoryId) {
            $query->byCategory($categoryId);
        }

        return $query->orderBy('published_at', 'desc')->get();
    }

    public function getDraftContent(int $siteId, string $type = 'all'): Collection
    {
        if ($type === 'pages') {
            return Page::where('site_id', $siteId)->draft()->get();
        }

        if ($type === 'posts') {
            return Post::where('site_id', $siteId)->draft()->get();
        }

        return collect()
            ->merge(Page::where('site_id', $siteId)->draft()->get())
            ->merge(Post::where('site_id', $siteId)->draft()->get());
    }

    public function findBySlug(int $siteId, string $slug, string $type = 'page'): ?Model
    {
        $model = $type === 'post' ? Post::class : Page::class;

        return $model::where('site_id', $siteId)
            ->where('slug', $slug)
            ->first();
    }

    protected function generateUniqueSlug(string $title, string $model, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while ($this->slugExists($slug, $model, $excludeId)) {
            $slug = $originalSlug.'-'.$count;
            $count++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, string $model, ?int $excludeId = null): bool
    {
        $query = $model::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
