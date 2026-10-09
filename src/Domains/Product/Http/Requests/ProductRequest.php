<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Product\Http\Requests;

use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;

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
