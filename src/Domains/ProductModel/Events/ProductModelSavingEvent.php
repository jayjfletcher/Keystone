<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\ProductModel\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ModelLifecycleEvent;
use RefactorCircus\Keystone\Domains\ProductModel\Models\ProductModelModel;

/**
 * The ProductModel `saving` Eloquent event.
 */
final class ProductModelSavingEvent implements ModelLifecycleEvent
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
        return 'saving';
    }
}
