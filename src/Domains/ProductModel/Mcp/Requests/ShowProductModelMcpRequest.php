<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\ProductModel\Mcp\Requests;

use JayI\Keystone\Domains\ProductModel\Actions\ShowProductModelAction;
use JayI\Keystone\Domains\ProductModel\Resources\ProductModelResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class ShowProductModelMcpRequest extends ProductModelRequest
{
    protected function authorize(): bool
    {
        return $this->allows('view', $this->productModel());
    }

    protected function rules(): array
    {
        return ShowProductModelAction::rules() + [
            'product_model' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $productModel = app(ShowProductModelAction::class)->execute($this->productModel(), $validated);

        return Response::structured((new ProductModelResource($productModel))->resolve());
    }
}
