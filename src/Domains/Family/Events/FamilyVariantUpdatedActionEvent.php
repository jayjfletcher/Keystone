<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Keystone\Domains\Family\Models\FamilyVariantModel;

/**
 * A family variant was updated.
 */
final class FamilyVariantUpdatedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public FamilyVariantModel $familyVariant,
    ) {}
}
