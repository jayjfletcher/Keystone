<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\ProductModel\Http\Requests;

use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;

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
