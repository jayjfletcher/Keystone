<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Category\Mcp\Requests;

use JayI\Keystone\Domains\Category\Actions\ShowCategoryAction;
use JayI\Keystone\Domains\Category\Resources\CategoryResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class ShowCategoryMcpRequest extends CategoryRequest
{
    protected function authorize(): bool
    {
        return $this->allows('view', $this->category());
    }

    protected function rules(): array
    {
        return ShowCategoryAction::rules() + [
            'category' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $category = app(ShowCategoryAction::class)->execute($this->category());

        return Response::structured((new CategoryResource($category))->resolve());
    }
}
