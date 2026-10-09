<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Category\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Category\Actions\UpdateCategoryAction;
use RefactorCircus\Keystone\Domains\Category\Resources\CategoryResource;

final class UpdateCategoryRequest extends CategoryRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->category());
    }

    public function rules(): array
    {
        return UpdateCategoryAction::rules();
    }

    public function persist(): JsonResponse
    {
        $category = app(UpdateCategoryAction::class)->execute($this->category(), $this->validated());

        return (new CategoryResource($category))->response();
    }
}
