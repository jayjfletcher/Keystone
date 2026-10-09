<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Product\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Product\Actions\CreateProductAction;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Domains\Product\Resources\ProductResource;

final class StoreProductRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', ProductModel::class);
    }

    public function rules(): array
    {
        return CreateProductAction::rules();
    }

    public function persist(): JsonResponse
    {
        $product = app(CreateProductAction::class)->execute($this->validated());

        return (new ProductResource($product))->response()->setStatusCode(201);
    }
}
