<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Product\Mcp\Requests;

use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;

abstract class ProductRequest extends Request
{
    protected function product(): ProductModel
    {
        return ProductModel::query()->where('identifier', $this->get('product'))->firstOrFail();
    }
}
