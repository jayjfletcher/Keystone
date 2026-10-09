<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Association\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ModelLifecycleEvent;
use RefactorCircus\Keystone\Domains\Association\Models\AssociationModel;

/**
 * The Association `deleting` Eloquent event.
 */
final class AssociationDeletingEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public AssociationModel $association) {}

    public function model(): Model
    {
        return $this->association;
    }

    public function hook(): string
    {
        return 'deleting';
    }
}
