<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ModelLifecycleEvent;
use JayI\Keystone\Domains\Attribute\Models\AttributeOptionModel;

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
