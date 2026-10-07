<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ModelLifecycleEvent;
use JayI\Keystone\Domains\Family\Models\FamilyVariantModel;

/**
 * The FamilyVariant `updating` Eloquent event.
 */
final class FamilyVariantUpdatingEvent implements ModelLifecycleEvent
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
        return 'updating';
    }
}
