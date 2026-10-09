<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ModelLifecycleEvent;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeOptionModel;

/**
 * The AttributeOption `saved` Eloquent event.
 */
final class AttributeOptionSavedEvent implements ModelLifecycleEvent
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
        return 'saved';
    }
}
