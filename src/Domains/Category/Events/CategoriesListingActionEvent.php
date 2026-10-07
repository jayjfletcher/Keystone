<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Category\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;

/**
 * Categories are about to be listed.
 */
final class CategoriesListingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public array $filters,
    ) {}
}
