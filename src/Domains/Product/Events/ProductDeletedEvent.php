<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Product\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ModelLifecycleEvent;
use JayI\Keystone\Domains\Product\Models\ProductModel;

/**
 * The Product `deleted` Eloquent event.
 */
final class ProductDeletedEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public ProductModel $product) {}

    public function model(): Model
    {
        return $this->product;
    }

    public function hook(): string
    {
        return 'deleted';
    }
}
