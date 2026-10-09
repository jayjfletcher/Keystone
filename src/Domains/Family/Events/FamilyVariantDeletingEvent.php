<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ModelLifecycleEvent;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyVariantModel;

/**
 * The FamilyVariant `deleting` Eloquent event.
 */
final class FamilyVariantDeletingEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public FamilyVariantModel $familyVariant) {}

    public function model(): Model
    {
        return $this->familyVariant;
    }

    public function hook(): string
    {
        return 'deleting';
    }
}
