<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Owner\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ModelLifecycleEvent;
use RefactorCircus\Showroom\Domains\Owner\Models\OwnerModel;

/**
 * The Owner `replicating` Eloquent event.
 */
final class OwnerReplicatingEvent implements ModelLifecycleEvent
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
        return 'replicating';
    }
}
