<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\ProductModel\Http\Requests;

use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Showroom\Domains\ProductModel\Models\ProductModelModel;

abstract class ProductModelRequest extends Request
{
    protected function productModel(): ProductModelModel
    {
        $productModel = $this->route('productModel');

        if (! $productModel instanceof ProductModelModel) {
            abort(404);
        }

        return $productModel;
    }
}
