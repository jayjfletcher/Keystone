<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ModelLifecycleEvent;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyModel;

/**
 * The Family `created` Eloquent event.
 */
final class FamilyCreatedEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public FamilyModel $family) {}

    public function model(): Model
    {
        return $this->family;
    }

    public function hook(): string
    {
        return 'created';
    }
}
