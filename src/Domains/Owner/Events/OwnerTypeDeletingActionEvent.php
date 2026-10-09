<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Owner\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ActionStartingEvent;
use RefactorCircus\Showroom\Domains\Owner\Models\OwnerTypeModel;

/**
 * An owner type is about to be deleted.
 */
final class OwnerTypeDeletingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public OwnerTypeModel $ownerType,
    ) {}
}
