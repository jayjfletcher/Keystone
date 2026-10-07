<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Keystone\Domains\Product\Models\ProductModel;

/**
 * A product was reverted to an earlier version.
 */
final class ProductRevertedActionEvent implements ActionFinishedEvent
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
