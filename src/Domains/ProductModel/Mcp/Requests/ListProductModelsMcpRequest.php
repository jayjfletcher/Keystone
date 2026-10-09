<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\ProductModel\Mcp\Requests;

use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\ProductModel\Actions\ListProductModelsAction;
use RefactorCircus\Keystone\Domains\ProductModel\Models\ProductModelModel;
use RefactorCircus\Keystone\Domains\ProductModel\Resources\ProductModelResource;

final class ListProductModelsMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', ProductModelModel::class);
    }

    protected function rules(): array
    {
        return ListProductModelsAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $productModels = app(ListProductModelsAction::class)->execute($validated);

        return $this->structuredCollection(
            ProductModelResource::collection($productModels)->resolve(),
            ['next_cursor' => $productModels->nextCursor()?->encode()],
        );
    }
}
