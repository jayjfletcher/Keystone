<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Product\Http\Requests;

use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Product\Models\ProductModel;

abstract class ProductRequest extends Request
{
    protected function product(): ProductModel
    {
        $product = $this->route('product');

        if (! $product instanceof ProductModel) {
            abort(404);
        }

        return $product;
    }
}
