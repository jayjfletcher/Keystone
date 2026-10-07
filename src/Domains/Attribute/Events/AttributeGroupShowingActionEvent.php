<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;

/**
 * An attribute group is about to be shown.
 */
final class AttributeGroupShowingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public AttributeGroupModel $group,
    ) {}
}
