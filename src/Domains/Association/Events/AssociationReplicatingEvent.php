<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Association\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ModelLifecycleEvent;
use RefactorCircus\Showroom\Domains\Association\Models\AssociationModel;

/**
 * The Association `replicating` Eloquent event.
 */
final class AssociationReplicatingEvent implements ModelLifecycleEvent
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
        return 'replicating';
    }
}
