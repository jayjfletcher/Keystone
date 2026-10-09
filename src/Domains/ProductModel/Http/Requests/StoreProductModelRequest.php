<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\ProductModel\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Showroom\Domains\ProductModel\Actions\CreateProductModelAction;
use RefactorCircus\Showroom\Domains\ProductModel\Models\ProductModelModel;
use RefactorCircus\Showroom\Domains\ProductModel\Resources\ProductModelResource;

final class StoreProductModelRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', ProductModelModel::class);
    }

    public function rules(): array
    {
        return CreateProductModelAction::rules();
    }

    public function persist(): JsonResponse
    {
        $productModel = app(CreateProductModelAction::class)->execute($this->validated());

        return (new ProductModelResource($productModel))->response()->setStatusCode(201);
    }
}
