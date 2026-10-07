<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\ProductModel\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\ProductModel\Actions\ListProductModelsAction;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;
use JayI\Keystone\Domains\ProductModel\Resources\ProductModelResource;
use Laravel\Mcp\ResponseFactory;

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
