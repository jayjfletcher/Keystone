<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\ProductModel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Showroom\Domains\ProductModel\Http\Requests\DeleteProductModelRequest;
use RefactorCircus\Showroom\Domains\ProductModel\Http\Requests\IndexProductModelsRequest;
use RefactorCircus\Showroom\Domains\ProductModel\Http\Requests\ShowProductModelRequest;
use RefactorCircus\Showroom\Domains\ProductModel\Http\Requests\StoreProductModelRequest;
use RefactorCircus\Showroom\Domains\ProductModel\Http\Requests\UpdateProductModelRequest;
use RefactorCircus\Showroom\Domains\ProductModel\Models\ProductModelModel;

final class ProductModelController
{
    public function index(IndexProductModelsRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function store(StoreProductModelRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function show(ShowProductModelRequest $request, ProductModelModel $productModel): JsonResponse
    {
        return $request->persist();
    }

    public function update(UpdateProductModelRequest $request, ProductModelModel $productModel): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DeleteProductModelRequest $request, ProductModelModel $productModel): JsonResponse
    {
        return $request->persist();
    }
}
