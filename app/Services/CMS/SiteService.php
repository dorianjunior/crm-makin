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
}
