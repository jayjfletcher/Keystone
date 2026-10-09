<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Family\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ActionStartingEvent;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyModel;

/**
 * A family is about to be deleted.
 */
final class FamilyDeletingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public FamilyModel $family,
    ) {}
}
