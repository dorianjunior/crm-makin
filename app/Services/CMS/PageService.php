<?php

namespace App\Services\CMS;

use App\Enums\ContentStatus;
use App\Models\CMS\Page;
use Illuminate\Support\Collection;

class PageService
{
    public function __construct(
        protected ContentService $contentService
    ) {}

    public function create(array $data): Page
    {
        return $this->contentService->createPage($data);
    }

    public function update(Page $page, array $data): Page
    {
        return $this->contentService->updatePage($page, $data);
    }

    public function delete(Page $page): bool
    {
        return $this->contentService->deletePage($page);
    }

    public function getBySite(int $siteId): Collection
    {
        return Page::where('site_id', $siteId)
            ->with('creator')
            ->orderBy('order')
            ->get();
    }

    public function getPublished(int $siteId): Collection
    {
        return Page::where('site_id', $siteId)
            ->published()
            ->with('creator')
            ->orderBy('order')
            ->get();
    }

    public function findBySite(int $id, int $siteId): ?Page
    {
        return Page::where('id', $id)
            ->where('site_id', $siteId)
            ->with('creator', 'site')
            ->first();
    }

    public function findBySlug(string $slug, int $siteId): ?Page
    {
        return Page::where('slug', $slug)
            ->where('site_id', $siteId)
            ->with('creator', 'site')
            ->first();
    }

    public function updateStatus(Page $page, ContentStatus $status): Page
    {
        $page->update(['status' => $status]);

        if ($status === ContentStatus::PUBLISHED && $page->published_at === null) {
            $page->update(['published_at' => now()]);
        }

        return $page->fresh();
    }

    public function publish(Page $page): Page
    {
        return $this->updateStatus($page, ContentStatus::PUBLISHED);
    }

    public function unpublish(Page $page): Page
    {
        return $this->updateStatus($page, ContentStatus::DRAFT);
    }

    public function reorder(int $siteId, array $order): void
    {
        foreach ($order as $position => $pageId) {
            Page::where('id', $pageId)
                ->where('site_id', $siteId)
                ->update(['order' => $position]);
        }
    }

    public function getForIndex(int $siteId, array $filters = [], int $perPage = 15)
    {
        $query = Page::where('site_id', $siteId)
            ->with(['creator', 'site'])
            ->orderBy('order')
            ->orderBy('created_at', 'desc');

        // Search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Status filter
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Site filter (para admin geral)
        if (! empty($filters['site_id'])) {
            $query->where('site_id', $filters['site_id']);
        }

        return $query->paginate($perPage);
    }

    public function getStats(int $siteId): array
    {
        $baseQuery = Page::where('site_id', $siteId);

        return [
            'total' => (clone $baseQuery)->count(),
            'published' => (clone $baseQuery)->where('status', ContentStatus::PUBLISHED)->count(),
            'draft' => (clone $baseQuery)->where('status', ContentStatus::DRAFT)->count(),
            'pending' => (clone $baseQuery)->where('status', ContentStatus::PENDING)->count(),
        ];
    }

    public function duplicate(Page $page): Page
    {
        $newPage = $page->replicate();
        $newPage->title = $page->title.' (Cópia)';
        $newPage->slug = $page->slug.'-copy-'.time();
        $newPage->status = ContentStatus::DRAFT;
        $newPage->published_at = null;
        $newPage->created_by = auth()->id();
        $newPage->save();

        return $newPage;
    }
}
