<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\ProductModel\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Showroom\Domains\ProductModel\Actions\ShowProductModelAction;
use RefactorCircus\Showroom\Domains\ProductModel\Resources\ProductModelResource;

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
