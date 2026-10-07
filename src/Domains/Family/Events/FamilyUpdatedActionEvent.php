<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Keystone\Domains\Family\Models\FamilyModel;

/**
 * A family was updated.
 */
final class FamilyUpdatedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public FamilyModel $family,
    ) {}
}
