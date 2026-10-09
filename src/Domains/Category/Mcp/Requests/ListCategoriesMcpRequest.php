<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Category\Mcp\Requests;

use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Mcp\Requests\Request;
use RefactorCircus\Showroom\Domains\Category\Actions\ListCategoriesAction;
use RefactorCircus\Showroom\Domains\Category\Models\CategoryModel;
use RefactorCircus\Showroom\Domains\Category\Resources\CategoryResource;

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
