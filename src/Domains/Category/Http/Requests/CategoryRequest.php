<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Category\Http\Requests;

use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Category\Models\CategoryModel;

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
