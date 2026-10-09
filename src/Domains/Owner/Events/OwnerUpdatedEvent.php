<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ModelLifecycleEvent;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerModel;

/**
 * The Owner `updated` Eloquent event.
 */
final class OwnerUpdatedEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public OwnerModel $owner) {}

    public function model(): Model
    {
        return $this->owner;
    }

    public function hook(): string
    {
        return 'updated';
    }
}
