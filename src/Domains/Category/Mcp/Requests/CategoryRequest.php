<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Category\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Category\Models\CategoryModel;

abstract class CategoryRequest extends Request
{
    protected function category(): CategoryModel
    {
        return CategoryModel::query()->where('code', $this->get('category'))->firstOrFail();
    }
}
