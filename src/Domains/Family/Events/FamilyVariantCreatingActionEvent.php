<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;

/**
 * A family variant is about to be created.
 */
final class FamilyVariantCreatingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public array $data,
    ) {}
}
