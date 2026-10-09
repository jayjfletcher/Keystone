<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Workflow\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ActionStartingEvent;
use RefactorCircus\Showroom\Domains\Product\Models\ProductModel;

/**
 * A product's versions are about to be listed.
 */
final class ProductVersionsListingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(
        public ProductModel $product,
        public array $filters,
    ) {}
}
