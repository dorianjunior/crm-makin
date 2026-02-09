<?php

namespace App\Services\CMS;

use App\Models\CMS\Site;
use Illuminate\Support\Collection;

class SiteService
{
    public function create(array $data): Site
    {
        return Site::create($data);
    }

    public function update(Site $site, array $data): Site
    {
        $site->update($data);

        return $site->fresh();
    }

    public function delete(Site $site): bool
    {
        return $site->delete();
    }

    public function getByCompany(int $companyId): Collection
    {
        return Site::where('company_id', $companyId)->get();
    }

    public function getActiveByCompany(int $companyId): Collection
    {
        return Site::where('company_id', $companyId)
            ->active()
            ->get();
    }

    public function findByDomain(string $domain): ?Site
    {
        return Site::where('domain', $domain)->first();
    }

    public function findByApiKey(string $apiKey): ?Site
    {
        return Site::where('api_key', $apiKey)->first();
    }

    public function regenerateApiKey(Site $site): string
    {
        return $site->regenerateApiKey();
    }

    public function activate(Site $site): Site
    {
        $site->update(['active' => true]);

        return $site->fresh();
    }

    public function deactivate(Site $site): Site
    {
        $site->update(['active' => false]);

        return $site->fresh();
    }

    public function updateSettings(Site $site, array $settings): Site
    {
        $currentSettings = $site->settings ?? [];
        $site->update([
            'settings' => array_merge($currentSettings, $settings),
        ]);

        return $site->fresh();
    }

    public function getForIndex(int $companyId, array $filters = [], int $perPage = 15)
    {
        $query = Site::where('company_id', $companyId)
            ->with('company')
            ->withCount(['pages', 'posts'])
            ->orderBy('created_at', 'desc');

        // Search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%");
            });
        }

        // Status filter
        if (! empty($filters['status'])) {
            $isActive = $filters['status'] === 'active';
            $query->where('active', $isActive);
        }

        return $query->paginate($perPage);
    }

    public function getStats(int $companyId): array
    {
        $baseQuery = Site::where('company_id', $companyId);

        return [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->where('active', true)->count(),
            'inactive' => (clone $baseQuery)->where('active', false)->count(),
            'total_pages' => (clone $baseQuery)->withCount('pages')->get()->sum('pages_count'),
            'total_posts' => (clone $baseQuery)->withCount('posts')->get()->sum('posts_count'),
        ];
    }

    public function findByCompany(int $id, int $companyId)
    {
        return Site::where('id', $id)
            ->where('company_id', $companyId)
            ->with(['pages', 'posts', 'menus'])
            ->first();
    }
}
