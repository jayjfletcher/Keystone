<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Product\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Product\Http\Requests\DeleteProductRequest;
use JayI\Keystone\Domains\Product\Http\Requests\IndexProductsRequest;
use JayI\Keystone\Domains\Product\Http\Requests\ShowProductRequest;
use JayI\Keystone\Domains\Product\Http\Requests\StoreProductRequest;
use JayI\Keystone\Domains\Product\Http\Requests\UpdateProductRequest;
use JayI\Keystone\Domains\Product\Models\ProductModel;

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
