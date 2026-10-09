<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Owner\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ModelLifecycleEvent;
use RefactorCircus\Showroom\Domains\Owner\Models\OwnerTypeModel;

/**
 * The OwnerType `replicating` Eloquent event.
 */
final class OwnerTypeReplicatingEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public OwnerTypeModel $ownerType) {}

    public function model(): Model
    {
        return $this->ownerType;
    }

    public function hook(): string
    {
        return 'replicating';
    }
}
