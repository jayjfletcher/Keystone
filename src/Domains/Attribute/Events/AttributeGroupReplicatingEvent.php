<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ModelLifecycleEvent;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeGroupModel;

/**
 * The AttributeGroup `replicating` Eloquent event.
 */
final class AttributeGroupReplicatingEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public AttributeGroupModel $group) {}

    public function model(): Model
    {
        return $this->group;
    }

    public function hook(): string
    {
        return 'replicating';
    }
}
