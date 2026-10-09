<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Association\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ActionStartingEvent;
use RefactorCircus\Keystone\Domains\Association\Models\AssociationTypeModel;

/**
 * An association type is about to be updated.
 */
final class AssociationTypeUpdatingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public AssociationTypeModel $associationType,
        public array $data,
    ) {}
}
