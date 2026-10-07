<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Product\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;

/**
 * Products are about to be listed.
 */
final class ProductsListingActionEvent implements ActionStartingEvent
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
