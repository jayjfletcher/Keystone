<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Association\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ActionFinishedEvent;
use RefactorCircus\Keystone\Domains\Association\Models\AssociationTypeModel;

/**
 * An association type was deleted.
 */
final class AssociationTypeDeletedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public AssociationTypeModel $associationType,
    ) {}
}
