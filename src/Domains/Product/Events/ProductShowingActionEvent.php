<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Product\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;
use JayI\Keystone\Domains\Product\Models\ProductModel;

/**
 * A product is about to be shown.
 */
final class ProductShowingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ProductModel $product,
    ) {}
}
