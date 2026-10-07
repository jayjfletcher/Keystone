<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\ProductModel\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ModelLifecycleEvent;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;

/**
 * The ProductModel `deleting` Eloquent event.
 */
final class ProductModelDeletingEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public ProductModelModel $productModel) {}

    public function model(): Model
    {
        return $this->productModel;
    }

    public function hook(): string
    {
        return 'deleting';
    }
}
