<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Category\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Category\Actions\ListCategoriesAction;
use JayI\Keystone\Domains\Category\Models\CategoryModel;
use JayI\Keystone\Domains\Category\Resources\CategoryResource;
use Laravel\Mcp\ResponseFactory;

final class ListCategoriesMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', CategoryModel::class);
    }

    protected function rules(): array
    {
        return ListCategoriesAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $categories = app(ListCategoriesAction::class)->execute($validated);

        return $this->structuredCollection(
            CategoryResource::collection($categories)->resolve(),
            ['next_cursor' => $categories->nextCursor()?->encode()],
        );
    }
}
