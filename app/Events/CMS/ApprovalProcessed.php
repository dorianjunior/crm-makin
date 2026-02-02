<?php

declare(strict_types=1);

namespace App\Events\CMS;

use App\Models\CMS\ContentApproval;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApprovalProcessed
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  ContentApproval  $approval  The approval that was processed
     * @param  string  $action  'approved' or 'rejected'
     * @param  int  $reviewerId  The ID of the reviewer
     */
    public function __construct(
        public ContentApproval $approval,
        public string $action,
        public int $reviewerId
    ) {}
}
