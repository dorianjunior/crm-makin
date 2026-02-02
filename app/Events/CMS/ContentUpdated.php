<?php

declare(strict_types=1);

namespace App\Events\CMS;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentUpdated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  Model  $content  The content that was updated
     * @param  int  $userId  The ID of the user who updated it
     * @param  array  $changes  The changes made
     */
    public function __construct(
        public Model $content,
        public int $userId,
        public array $changes = []
    ) {}
}
