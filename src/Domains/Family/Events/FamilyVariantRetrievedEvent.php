<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Family\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ModelLifecycleEvent;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyVariantModel;

/**
 * The FamilyVariant `retrieved` Eloquent event.
 */
final class FamilyVariantRetrievedEvent implements ModelLifecycleEvent
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
        return 'retrieved';
    }
}
