<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Category\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Category\Actions\CreateCategoryAction;
use RefactorCircus\Keystone\Domains\Category\Models\CategoryModel;
use RefactorCircus\Keystone\Domains\Category\Resources\CategoryResource;

final class StoreCategoryRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', CategoryModel::class);
    }

    public function rules(): array
    {
        return CreateCategoryAction::rules();
    }

    public function persist(): JsonResponse
    {
        $category = app(CreateCategoryAction::class)->execute($this->validated());

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }
}
