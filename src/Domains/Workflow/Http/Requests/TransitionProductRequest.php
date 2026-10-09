<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Workflow\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Product\Http\Requests\ProductRequest;
use RefactorCircus\Keystone\Domains\Product\Resources\ProductResource;
use RefactorCircus\Keystone\Domains\Workflow\Actions\TransitionProductAction;

final class TransitionProductRequest extends ProductRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->product());
    }

    public function rules(): array
    {
        return TransitionProductAction::rules();
    }

    public function persist(): JsonResponse
    {
        $product = app(TransitionProductAction::class)->execute($this->product(), $this->validated());

        return (new ProductResource($product))->response();
    }
}
