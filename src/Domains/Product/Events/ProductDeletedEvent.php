<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Product\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ModelLifecycleEvent;
use RefactorCircus\Showroom\Domains\Product\Models\ProductModel;

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
