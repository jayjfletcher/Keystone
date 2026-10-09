<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\ProductModel\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Keystone\Domains\ProductModel\Actions\CreateProductModelAction;
use RefactorCircus\Keystone\Domains\ProductModel\Models\ProductModelModel;
use RefactorCircus\Keystone\Domains\ProductModel\Resources\ProductModelResource;

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
