<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Product\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Product\Actions\ShowProductAction;
use JayI\Keystone\Domains\Product\Resources\ProductResource;

final class ShowProductRequest extends ProductRequest
{
    public function authorize(): bool
    {
        return $this->allows('view', $this->product());
    }

    public function rules(): array
    {
        return ShowProductAction::rules();
    }

    public function persist(): JsonResponse
    {
        $product = app(ShowProductAction::class)->execute($this->product(), $this->validated());

        return (new ProductResource($product))->response();
    }
}
