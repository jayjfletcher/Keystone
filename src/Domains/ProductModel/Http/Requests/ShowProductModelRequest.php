<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\ProductModel\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\ProductModel\Actions\ShowProductModelAction;
use RefactorCircus\Keystone\Domains\ProductModel\Resources\ProductModelResource;

final class ShowProductModelRequest extends ProductModelRequest
{
    public function authorize(): bool
    {
        return $this->allows('view', $this->productModel());
    }

    public function rules(): array
    {
        return ShowProductModelAction::rules();
    }

    public function persist(): JsonResponse
    {
        $productModel = app(ShowProductModelAction::class)->execute($this->productModel(), $this->validated());

        return (new ProductModelResource($productModel))->response();
    }
}
