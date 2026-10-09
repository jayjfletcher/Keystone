<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ModelLifecycleEvent;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeGroupModel;

/**
 * The AttributeGroup `updating` Eloquent event.
 */
final class AttributeGroupUpdatingEvent implements ModelLifecycleEvent
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
        return 'updating';
    }
}
