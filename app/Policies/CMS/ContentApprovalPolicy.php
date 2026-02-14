<?php

declare(strict_types=1);

namespace App\Policies\CMS;

use App\Enums\ContentStatus;
use App\Models\CMS\ContentApproval;
use App\Models\Admin\User;

class ContentApprovalPolicy
{
    /**
     * Determine whether the user can view any content approvals.
     */
    public function viewAny(User $user): bool
    {
        return $user->company_id !== null;
    }

    /**
     * Determine whether the user can view the content approval.
     */
    public function view(User $user, ContentApproval $approval): bool
    {
        $content = $approval->approvable;

        return $user->company_id === $content->site->company_id;
    }

    /**
     * Determine whether the user can create content approvals.
     */
    public function create(User $user): bool
    {
        return $user->company_id !== null;
    }

    /**
     * Determine whether the user can update the content approval.
     */
    public function update(User $user, ContentApproval $approval): bool
    {
        $content = $approval->approvable;

        return $user->company_id === $content->site->company_id;
    }

    /**
     * Determine whether the user can delete the content approval.
     */
    public function delete(User $user, ContentApproval $approval): bool
    {
        $content = $approval->approvable;

        return $user->company_id === $content->site->company_id;
    }

    /**
     * Determine whether the user can approve content.
     * This typically requires manager or admin role.
     */
    public function approve(User $user, ContentApproval $approval): bool
    {
        $content = $approval->approvable;

        // User must be from the same company
        if ($user->company_id !== $content->site->company_id) {
            return false;
        }

        // Only pending approvals can be approved
        if ($approval->status !== ContentStatus::PENDING) {
            return false;
        }

        // TODO: Add role check when role system is implemented
        // Example: return $user->hasRole(['admin', 'manager']);

        return true;
    }

    /**
     * Determine whether the user can reject content.
     * This typically requires manager or admin role.
     */
    public function reject(User $user, ContentApproval $approval): bool
    {
        $content = $approval->approvable;

        // User must be from the same company
        if ($user->company_id !== $content->site->company_id) {
            return false;
        }

        // Only pending approvals can be rejected
        if ($approval->status !== ContentStatus::PENDING) {
            return false;
        }

        // TODO: Add role check when role system is implemented
        // Example: return $user->hasRole(['admin', 'manager']);

        return true;
    }

    /**
     * Determine whether the user can request approval for content.
     */
    public function requestApproval(User $user, ContentApproval $approval): bool
    {
        $content = $approval->approvable;

        return $user->company_id === $content->site->company_id;
    }
}
