<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Workflow\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Showroom\Domains\Product\Models\ProductModel;
use RefactorCircus\Showroom\Domains\Workflow\Http\Requests\IndexProductVersionsRequest;
use RefactorCircus\Showroom\Domains\Workflow\Http\Requests\RevertProductRequest;
use RefactorCircus\Showroom\Domains\Workflow\Http\Requests\ShowProductVersionRequest;
use RefactorCircus\Showroom\Domains\Workflow\Http\Requests\TransitionProductRequest;

final class ProductWorkflowController
{
    public function transition(TransitionProductRequest $request, ProductModel $product): JsonResponse
    {
        return $request->persist();
    }

    public function versions(IndexProductVersionsRequest $request, ProductModel $product): JsonResponse
    {
        return $request->persist();
    }

    public function version(ShowProductVersionRequest $request, ProductModel $product, string $version): JsonResponse
    {
        return $request->persist();
    }

    public function revert(RevertProductRequest $request, ProductModel $product): JsonResponse
    {
        return $request->persist();
    }
}
