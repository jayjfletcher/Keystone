<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\ProductModel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;

/**
 * A product model was updated.
 */
final class ProductModelUpdatedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ProductModelModel $productModel,
    ) {}
}
