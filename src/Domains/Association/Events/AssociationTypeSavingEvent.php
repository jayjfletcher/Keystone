<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Association\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ModelLifecycleEvent;
use RefactorCircus\Showroom\Domains\Association\Models\AssociationTypeModel;

/**
 * The AssociationType `saving` Eloquent event.
 */
final class AssociationTypeSavingEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public AssociationTypeModel $associationType) {}

    public function model(): Model
    {
        return $this->associationType;
    }

    public function hook(): string
    {
        return 'saving';
    }
}
