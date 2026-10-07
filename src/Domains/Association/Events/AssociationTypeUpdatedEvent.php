<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Association\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ModelLifecycleEvent;
use JayI\Keystone\Domains\Association\Models\AssociationTypeModel;

/**
 * The AssociationType `updated` Eloquent event.
 */
final class AssociationTypeUpdatedEvent implements ModelLifecycleEvent
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
        return 'updated';
    }
}
