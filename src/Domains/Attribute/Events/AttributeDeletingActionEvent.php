<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ActionStartingEvent;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeModel;

/**
 * An attribute is about to be deleted.
 */
final class AttributeDeletingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public AttributeModel $attribute,
    ) {}
}
