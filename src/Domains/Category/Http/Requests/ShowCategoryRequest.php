<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Category\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Category\Actions\ShowCategoryAction;
use JayI\Keystone\Domains\Category\Resources\CategoryResource;

final class ShowCategoryRequest extends CategoryRequest
{
    public function authorize(): bool
    {
        return $this->allows('view', $this->category());
    }

    public function rules(): array
    {
        return ShowCategoryAction::rules();
    }

    public function persist(): JsonResponse
    {
        $category = app(ShowCategoryAction::class)->execute($this->category());

        return (new CategoryResource($category))->response();
    }
}
