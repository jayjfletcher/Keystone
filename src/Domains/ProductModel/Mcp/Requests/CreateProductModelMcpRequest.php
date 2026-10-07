<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\ProductModel\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\ProductModel\Actions\CreateProductModelAction;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;
use JayI\Keystone\Domains\ProductModel\Resources\ProductModelResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class CreateProductModelMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', ProductModelModel::class);
    }

    protected function rules(): array
    {
        return CreateProductModelAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $productModel = app(CreateProductModelAction::class)->execute($validated);

        return Response::structured((new ProductModelResource($productModel))->resolve());
    }
}
