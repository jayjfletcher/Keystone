<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Product\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Showroom\Domains\Product\Http\Requests\DeleteProductRequest;
use RefactorCircus\Showroom\Domains\Product\Http\Requests\IndexProductsRequest;
use RefactorCircus\Showroom\Domains\Product\Http\Requests\ShowProductRequest;
use RefactorCircus\Showroom\Domains\Product\Http\Requests\StoreProductRequest;
use RefactorCircus\Showroom\Domains\Product\Http\Requests\UpdateProductRequest;
use RefactorCircus\Showroom\Domains\Product\Models\ProductModel;

final class ProductController
{
    public function index(IndexProductsRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function show(ShowProductRequest $request, ProductModel $product): JsonResponse
    {
        return $request->persist();
    }

    public function update(UpdateProductRequest $request, ProductModel $product): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DeleteProductRequest $request, ProductModel $product): JsonResponse
    {
        return $request->persist();
    }
}
