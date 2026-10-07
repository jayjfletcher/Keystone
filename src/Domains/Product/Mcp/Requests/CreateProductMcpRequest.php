<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Product\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Product\Actions\CreateProductAction;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Product\Resources\ProductResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class CreateProductMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', ProductModel::class);
    }

    protected function rules(): array
    {
        return CreateProductAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $product = app(CreateProductAction::class)->execute($validated);

        return Response::structured((new ProductResource($product))->resolve());
    }
}
