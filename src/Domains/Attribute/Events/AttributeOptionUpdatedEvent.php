<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ModelLifecycleEvent;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeOptionModel;

/**
 * The AttributeOption `updated` Eloquent event.
 */
final class AttributeOptionUpdatedEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public AttributeOptionModel $option) {}

    public function model(): Model
    {
        return $this->option;
    }

    public function hook(): string
    {
        return 'updated';
    }
}
