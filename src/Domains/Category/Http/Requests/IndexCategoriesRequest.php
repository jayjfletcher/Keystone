<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Category\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Showroom\Domains\Category\Actions\ListCategoriesAction;
use RefactorCircus\Showroom\Domains\Category\Models\CategoryModel;
use RefactorCircus\Showroom\Domains\Category\Resources\CategoryResource;

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
