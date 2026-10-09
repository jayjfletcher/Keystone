<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Category\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Category\Actions\CreateCategoryAction;
use RefactorCircus\Keystone\Domains\Category\Models\CategoryModel;
use RefactorCircus\Keystone\Domains\Category\Resources\CategoryResource;

final class CreateCategoryMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', CategoryModel::class);
    }

    protected function rules(): array
    {
        return CreateCategoryAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $category = app(CreateCategoryAction::class)->execute($validated);

        return Response::structured((new CategoryResource($category))->resolve());
    }
}
