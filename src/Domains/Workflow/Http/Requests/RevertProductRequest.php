<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Product\Http\Requests\ProductRequest;
use JayI\Keystone\Domains\Product\Resources\ProductResource;
use JayI\Keystone\Domains\Workflow\Actions\RevertProductAction;

final class RevertProductRequest extends ProductRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->product());
    }

    public function rules(): array
    {
        return RevertProductAction::rules();
    }

    public function persist(): JsonResponse
    {
        $product = app(RevertProductAction::class)->execute($this->product(), $this->validated());

        return (new ProductResource($product))->response();
    }
}
