<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Category\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Domains\Category\Actions\ShowCategoryAction;
use RefactorCircus\Keystone\Domains\Category\Resources\CategoryResource;

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
