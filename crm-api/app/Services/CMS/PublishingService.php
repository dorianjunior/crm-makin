<?php

namespace App\Services\CMS;

use App\Enums\ContentStatus;
use App\Events\CMS\ApprovalApproved;
use App\Events\CMS\ApprovalRejected;
use App\Events\CMS\ApprovalRequested;
use App\Events\CMS\ContentPublished;
use App\Models\CMS\ContentApproval;
use App\Models\CMS\Page;
use App\Models\CMS\Post;
use App\Models\User;
use App\Notifications\CMS\ApprovalDecisionNotification;
use App\Notifications\CMS\ApprovalRequestedNotification;
use App\Notifications\CMS\ContentPublishedNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

class PublishingService
{
    public function publish(Model $content, int $userId): bool
    {
        if (! $this->canPublish($content)) {
            return false;
        }

        $content->update([
            'status' => ContentStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        // Dispatch event
        $contentType = class_basename($content);
        event(new ContentPublished($content, $contentType));

        // Notify content creator
        if ($content->creator) {
            $content->creator->notify(new ContentPublishedNotification($content, $contentType));
        }

        return true;
    }

    public function unpublish(Model $content): bool
    {
        $content->update([
            'status' => ContentStatus::DRAFT,
        ]);

        return true;
    }

    public function requestApproval(Model $content, int $userId, ?string $message = null): ContentApproval
    {
        // Check if there's already a pending approval
        $existingApproval = $content->approvals()
            ->pending()
            ->first();

        if ($existingApproval) {
            return $existingApproval;
        }

        // Update content status
        $content->update([
            'status' => ContentStatus::PENDING,
        ]);

        // Create approval request
        $approval = ContentApproval::create([
            'approvable_type' => get_class($content),
            'approvable_id' => $content->id,
            'requested_by' => $userId,
            'status' => 'pending',
            'message' => $message,
        ]);

        // Dispatch event
        $contentType = class_basename($content);
        event(new ApprovalRequested($content, $contentType, $approval));

        // Notify managers (users with permission to approve content)
        $managers = User::whereHas('roles', function ($query) {
            $query->whereHas('permissions', function ($q) {
                $q->where('name', 'approve_content');
            });
        })->get();

        if ($managers->isNotEmpty()) {
            Notification::send($managers, new ApprovalRequestedNotification($content, $contentType, $approval));
        }

        return $approval;
    }

    public function approve(ContentApproval $approval, int $userId): bool
    {
        $approval->approve($userId);

        // Auto-publish approved content
        $content = $approval->approvable;
        $this->publish($content, $userId);

        // Dispatch event
        $contentType = class_basename($content);
        event(new ApprovalApproved($content, $contentType, $approval));

        // Notify requester
        if ($approval->requestedBy) {
            $approval->requestedBy->notify(
                new ApprovalDecisionNotification($content, $contentType, $approval, true)
            );
        }

        return true;
    }

    public function reject(ContentApproval $approval, int $userId, string $reason): bool
    {
        $approval->reject($userId, $reason);

        // Set content back to draft
        $content = $approval->approvable;
        $content->update([
            'status' => ContentStatus::DRAFT,
        ]);

        // Dispatch event
        $contentType = class_basename($content);
        event(new ApprovalRejected($content, $contentType, $approval));

        // Notify requester
        if ($approval->requestedBy) {
            $approval->requestedBy->notify(
                new ApprovalDecisionNotification($content, $contentType, $approval, false)
            );
        }

        return true;
    }

    public function getPendingApprovals(int $siteId): array
    {
        $pages = Page::where('site_id', $siteId)
            ->pending()
            ->with(['approvals' => function ($query) {
                $query->pending();
            }])
            ->get();

        $posts = Post::where('site_id', $siteId)
            ->pending()
            ->with(['approvals' => function ($query) {
                $query->pending();
            }])
            ->get();

        return [
            'pages' => $pages,
            'posts' => $posts,
        ];
    }

    public function schedulePublish(Model $content, \DateTime $publishAt): bool
    {
        $content->update([
            'status' => ContentStatus::PENDING,
            'published_at' => $publishAt,
        ]);

        return true;
    }

    protected function canPublish(Model $content): bool
    {
        // Add business logic here
        // For example: check user permissions, content completeness, etc.
        return true;
    }
}
