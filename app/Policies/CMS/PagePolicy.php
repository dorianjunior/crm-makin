<?php

declare(strict_types=1);

namespace App\Policies\CMS;

use App\Models\CMS\Page;
use App\Models\Admin\User;

class PagePolicy
{
    /**
     * Determine whether the user can view any pages.
     */
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    /**
     * Determine whether the user can view the page.
     */
    public function view(User $user, Page $page): bool
    {
        return $user->company_id === $page->site->company_id;
    }

    /**
     * Determine whether the user can create pages.
     */
    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    /**
     * Determine whether the user can update the page.
     */
    public function update(User $user, Page $page): bool
    {
        return $user->company_id === $page->site->company_id;
    }

    /**
     * Determine whether the user can delete the page.
     */
    public function delete(User $user, Page $page): bool
    {
        return $user->company_id === $page->site->company_id;
    }

    /**
     * Determine whether the user can restore the page.
     */
    public function restore(User $user, Page $page): bool
    {
        return $user->company_id === $page->site->company_id;
    }

    /**
     * Determine whether the user can permanently delete the page.
     */
    public function forceDelete(User $user, Page $page): bool
    {
        return $user->company_id === $page->site->company_id;
    }

    /**
     * Determine whether the user can publish the page.
     */
    public function publish(User $user, Page $page): bool
    {
        return $user->company_id === $page->site->company_id;
    }

    /**
     * Determine whether the user can unpublish the page.
     */
    public function unpublish(User $user, Page $page): bool
    {
        return $user->company_id === $page->site->company_id;
    }

    /**
     * Determine whether the user can duplicate the page.
     */
    public function duplicate(User $user, Page $page): bool
    {
        return $user->company_id === $page->site->company_id;
    }
}
