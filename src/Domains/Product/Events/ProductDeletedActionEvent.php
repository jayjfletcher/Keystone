<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Product\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Keystone\Domains\Product\Models\ProductModel;

/**
 * A product was deleted.
 */
final class ProductDeletedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ProductModel $product,
    ) {}
}
