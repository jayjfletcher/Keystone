<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ModelLifecycleEvent;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;

/**
 * The Attribute `created` Eloquent event.
 */
final class AttributeCreatedEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public AttributeModel $attribute) {}

    public function model(): Model
    {
        return $this->attribute;
    }

    public function hook(): string
    {
        return 'created';
    }
}
