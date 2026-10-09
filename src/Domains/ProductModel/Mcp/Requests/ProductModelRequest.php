<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\ProductModel\Mcp\Requests;

use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\ProductModel\Models\ProductModelModel;

abstract class ProductModelRequest extends Request
{
    protected function productModel(): ProductModelModel
    {
        return ProductModelModel::query()->where('code', $this->get('product_model'))->firstOrFail();
    }
}
