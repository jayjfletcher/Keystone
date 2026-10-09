<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Workflow\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Product\Http\Requests\ProductRequest;
use RefactorCircus\Keystone\Domains\Product\Resources\ProductResource;
use RefactorCircus\Keystone\Domains\Workflow\Actions\RevertProductAction;

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
