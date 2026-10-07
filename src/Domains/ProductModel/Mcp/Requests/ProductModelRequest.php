<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\ProductModel\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;

abstract class ProductModelRequest extends Request
{
    protected function productModel(): ProductModelModel
    {
        return ProductModelModel::query()->where('code', $this->get('product_model'))->firstOrFail();
    }
}
