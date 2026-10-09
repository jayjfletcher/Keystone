<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Category\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Domains\Category\Actions\UpdateCategoryAction;
use RefactorCircus\Keystone\Domains\Category\Resources\CategoryResource;

final class UpdateCategoryMcpRequest extends CategoryRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->category());
    }

    protected function rules(): array
    {
        return UpdateCategoryAction::rules() + [
            'category' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['category']);

        $category = app(UpdateCategoryAction::class)->execute($this->category(), $validated);

        return Response::structured((new CategoryResource($category))->resolve());
    }
}
