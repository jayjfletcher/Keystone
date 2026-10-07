<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Product\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Product\Models\ProductModel;

abstract class ProductRequest extends Request
{
    protected function product(): ProductModel
    {
        return ProductModel::query()->where('identifier', $this->get('product'))->firstOrFail();
    }
}
