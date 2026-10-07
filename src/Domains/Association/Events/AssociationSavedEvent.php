<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Association\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ModelLifecycleEvent;
use JayI\Keystone\Domains\Association\Models\AssociationModel;

/**
 * The Association `saved` Eloquent event.
 */
final class AssociationSavedEvent implements ModelLifecycleEvent
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
        return 'saved';
    }
}
