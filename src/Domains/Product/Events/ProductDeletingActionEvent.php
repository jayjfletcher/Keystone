<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Product\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ActionStartingEvent;
use RefactorCircus\Showroom\Domains\Product\Models\ProductModel;

/**
 * A product is about to be deleted.
 */
final class ProductDeletingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ProductModel $product,
    ) {}
}
