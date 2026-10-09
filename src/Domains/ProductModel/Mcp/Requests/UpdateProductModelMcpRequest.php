<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\ProductModel\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Showroom\Domains\ProductModel\Actions\UpdateProductModelAction;
use RefactorCircus\Showroom\Domains\ProductModel\Resources\ProductModelResource;

final class UpdateProductModelMcpRequest extends ProductModelRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->productModel());
    }

    protected function rules(): array
    {
        return UpdateProductModelAction::rules() + [
            'product_model' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['product_model']);

        $productModel = app(UpdateProductModelAction::class)->execute($this->productModel(), $validated);

        return Response::structured((new ProductModelResource($productModel))->resolve());
    }
}
