<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;
use JayI\Keystone\Domains\Family\Models\FamilyVariantModel;

/**
 * A family variant is about to be updated.
 */
final class FamilyVariantUpdatingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public FamilyVariantModel $familyVariant,
        public array $data,
    ) {}
}
