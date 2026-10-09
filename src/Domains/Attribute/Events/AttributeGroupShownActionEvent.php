<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ActionFinishedEvent;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeGroupModel;

/**
 * An attribute group was shown.
 */
final class AttributeGroupShownActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public AttributeGroupModel $group,
    ) {}
}
