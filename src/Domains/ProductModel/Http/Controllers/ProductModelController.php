<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\ProductModel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\ProductModel\Http\Requests\DeleteProductModelRequest;
use JayI\Keystone\Domains\ProductModel\Http\Requests\IndexProductModelsRequest;
use JayI\Keystone\Domains\ProductModel\Http\Requests\ShowProductModelRequest;
use JayI\Keystone\Domains\ProductModel\Http\Requests\StoreProductModelRequest;
use JayI\Keystone\Domains\ProductModel\Http\Requests\UpdateProductModelRequest;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;

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
