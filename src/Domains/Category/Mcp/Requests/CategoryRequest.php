<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Category\Mcp\Requests;

use RefactorCircus\Keystone\Mcp\Requests\Request;
use RefactorCircus\Showroom\Domains\Category\Models\CategoryModel;

abstract class CategoryRequest extends Request
{
    protected function category(): CategoryModel
    {
        return CategoryModel::query()->where('code', $this->get('category'))->firstOrFail();
    }
}
