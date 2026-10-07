<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ModelLifecycleEvent;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;

/**
 * The AttributeGroup `deleted` Eloquent event.
 */
final class AttributeGroupDeletedEvent implements ModelLifecycleEvent
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
        return 'deleted';
    }
}
