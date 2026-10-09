<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Category\Mcp\Requests;

use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Category\Models\CategoryModel;

abstract class CategoryRequest extends Request
{
    protected function category(): CategoryModel
    {
        return CategoryModel::query()->where('code', $this->get('category'))->firstOrFail();
    }
}
