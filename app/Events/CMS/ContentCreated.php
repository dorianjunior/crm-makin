<?php

declare(strict_types=1);

namespace App\Events\CMS;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  Model  $content  The content that was created
     * @param  int  $userId  The ID of the user who created it
     */
    public function __construct(
        public Model $content,
        public int $userId
    ) {}
}
