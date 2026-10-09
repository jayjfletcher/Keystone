<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Family\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ModelLifecycleEvent;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyModel;

/**
 * The Family `saving` Eloquent event.
 */
final class FamilySavingEvent implements ModelLifecycleEvent
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
        return 'saving';
    }
}
