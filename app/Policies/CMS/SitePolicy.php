<?php

declare(strict_types=1);

namespace App\Policies\CMS;

use App\Models\CMS\Site;
use App\Models\User;

class SitePolicy
{
    /**
     * Determine whether the user can view any sites.
     */
    public function viewAny(User $user): bool
    {
        // Users can only see sites of their company
        return $user->company_id !== null;
    }

    /**
     * Determine whether the user can view the site.
     */
    public function view(User $user, Site $site): bool
    {
        // Users can only view sites from their company
        return $user->company_id === $site->company_id;
    }

    /**
     * Determine whether the user can create sites.
     */
    public function create(User $user): bool
    {
        // Only users with company can create sites
        return $user->company_id !== null;
    }

    /**
     * Determine whether the user can update the site.
     */
    public function update(User $user, Site $site): bool
    {
        // Users can only update sites from their company
        return $user->company_id === $site->company_id;
    }

    /**
     * Determine whether the user can delete the site.
     */
    public function delete(User $user, Site $site): bool
    {
        // Users can only delete sites from their company
        // Check if site doesn't have content
        return $user->company_id === $site->company_id;
    }

    /**
     * Determine whether the user can restore the site.
     */
    public function restore(User $user, Site $site): bool
    {
        return $user->company_id === $site->company_id;
    }

    /**
     * Determine whether the user can permanently delete the site.
     */
    public function forceDelete(User $user, Site $site): bool
    {
        return $user->company_id === $site->company_id;
    }

    /**
     * Determine whether the user can activate/deactivate the site.
     */
    public function toggle(User $user, Site $site): bool
    {
        return $user->company_id === $site->company_id;
    }

    /**
     * Determine whether the user can regenerate API key.
     */
    public function regenerateApiKey(User $user, Site $site): bool
    {
        return $user->company_id === $site->company_id;
    }
}
