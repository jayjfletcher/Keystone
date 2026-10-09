<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\ProductModel\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\ProductModel\Actions\UpdateProductModelAction;
use RefactorCircus\Keystone\Domains\ProductModel\Resources\ProductModelResource;

final class UpdateProductModelRequest extends ProductModelRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->productModel());
    }

    public function rules(): array
    {
        return UpdateProductModelAction::rules();
    }

    public function persist(): JsonResponse
    {
        $productModel = app(UpdateProductModelAction::class)->execute($this->productModel(), $this->validated());

        return (new ProductModelResource($productModel))->response();
    }
}
