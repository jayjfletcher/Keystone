<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\ProductModel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ActionFinishedEvent;
use RefactorCircus\Showroom\Domains\ProductModel\Models\ProductModelModel;

/**
 * A product model was created.
 */
final class ProductModelCreatedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ProductModelModel $productModel,
    ) {}
}
