<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\ProductModel\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ModelLifecycleEvent;
use RefactorCircus\Showroom\Domains\ProductModel\Models\ProductModelModel;

/**
 * The ProductModel `replicating` Eloquent event.
 */
final class ProductModelReplicatingEvent implements ModelLifecycleEvent
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
        return 'replicating';
    }
}
