<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Association\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;
use JayI\Keystone\Domains\Association\Models\AssociationTypeModel;

/**
 * An association type is about to be shown.
 */
final class AssociationTypeShowingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public AssociationTypeModel $associationType,
    ) {}
}
