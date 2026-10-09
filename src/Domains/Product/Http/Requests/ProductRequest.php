<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Product\Http\Requests;

use RefactorCircus\Keystone\Http\Requests\Request;
use RefactorCircus\Showroom\Domains\Product\Models\ProductModel;

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
