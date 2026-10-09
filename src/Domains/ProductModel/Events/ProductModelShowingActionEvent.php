<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\ProductModel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ActionStartingEvent;
use RefactorCircus\Keystone\Domains\ProductModel\Models\ProductModelModel;

/**
 * A product model is about to be shown.
 */
final class ProductModelShowingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ProductModelModel $productModel,
    ) {}
}
