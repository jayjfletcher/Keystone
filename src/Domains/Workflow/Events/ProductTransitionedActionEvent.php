<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Workflow\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ActionFinishedEvent;
use RefactorCircus\Showroom\Domains\Product\Models\ProductModel;

/**
 * A product moved through the workflow.
 */
final class ProductTransitionedActionEvent implements ActionFinishedEvent
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
