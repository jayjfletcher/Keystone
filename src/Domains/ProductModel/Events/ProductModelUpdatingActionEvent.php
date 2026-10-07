<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\ProductModel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;

/**
 * A product model is about to be updated.
 */
final class ProductModelUpdatingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public ProductModelModel $productModel,
        public array $data,
    ) {}
}
