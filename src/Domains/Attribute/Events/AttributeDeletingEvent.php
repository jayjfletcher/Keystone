<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ModelLifecycleEvent;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeModel;

/**
 * The Attribute `deleting` Eloquent event.
 */
final class AttributeDeletingEvent implements ModelLifecycleEvent
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
        return 'deleting';
    }
}
