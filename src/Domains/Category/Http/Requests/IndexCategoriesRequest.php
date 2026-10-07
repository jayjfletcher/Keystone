<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Category\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Category\Actions\ListCategoriesAction;
use JayI\Keystone\Domains\Category\Models\CategoryModel;
use JayI\Keystone\Domains\Category\Resources\CategoryResource;

final class IndexCategoriesRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', CategoryModel::class);
    }

    public function rules(): array
    {
        return ListCategoriesAction::rules();
    }

    public function persist(): JsonResponse
    {
        $categories = app(ListCategoriesAction::class)->execute($this->validated());

        return CategoryResource::collection($categories)->response();
    }
}
