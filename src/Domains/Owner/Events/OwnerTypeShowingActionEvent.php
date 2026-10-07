<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;
use JayI\Keystone\Domains\Owner\Models\OwnerTypeModel;

/**
 * An owner type is about to be shown.
 */
final class OwnerTypeShowingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public OwnerTypeModel $ownerType,
    ) {}
}
