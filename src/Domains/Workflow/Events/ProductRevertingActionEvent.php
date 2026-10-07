<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;
use JayI\Keystone\Domains\Product\Models\ProductModel;

/**
 * A product is about to be reverted to an earlier version.
 */
final class ProductRevertingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public ProductModel $product,
        public array $data,
    ) {}
}
