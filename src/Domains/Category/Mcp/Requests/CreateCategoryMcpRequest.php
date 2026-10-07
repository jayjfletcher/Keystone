<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Category\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Category\Actions\CreateCategoryAction;
use JayI\Keystone\Domains\Category\Models\CategoryModel;
use JayI\Keystone\Domains\Category\Resources\CategoryResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

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
