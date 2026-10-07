<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;

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
