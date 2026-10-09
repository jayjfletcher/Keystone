<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Product\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Product\Actions\UpdateProductAction;
use RefactorCircus\Keystone\Domains\Product\Resources\ProductResource;

final class UpdateProductRequest extends ProductRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->product());
    }

    public function rules(): array
    {
        return UpdateProductAction::rules();
    }

    public function persist(): JsonResponse
    {
        $product = app(UpdateProductAction::class)->execute($this->product(), $this->validated());

        return (new ProductResource($product))->response();
    }
}
