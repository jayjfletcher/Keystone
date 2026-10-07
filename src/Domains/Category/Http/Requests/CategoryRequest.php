<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Category\Http\Requests;

use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Category\Models\CategoryModel;

abstract class CategoryRequest extends Request
{
    protected function category(): CategoryModel
    {
        $category = $this->route('category');

        if (! $category instanceof CategoryModel) {
            abort(404);
        }

        return $category;
    }
}
