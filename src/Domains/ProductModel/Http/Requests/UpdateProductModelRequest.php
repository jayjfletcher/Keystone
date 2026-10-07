<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\ProductModel\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\ProductModel\Actions\UpdateProductModelAction;
use JayI\Keystone\Domains\ProductModel\Resources\ProductModelResource;

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
