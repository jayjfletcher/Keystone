<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Product\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\Product\Actions\CreateProductAction;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Domains\Product\Resources\ProductResource;

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
