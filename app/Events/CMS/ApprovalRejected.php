<?php

namespace App\Events\CMS;

use App\Models\CMS\ContentApproval;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApprovalRejected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Model $content,
        public string $contentType,
        public ContentApproval $approval
    ) {}
}
