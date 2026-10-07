<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Workflow\Http\Requests\IndexProductVersionsRequest;
use JayI\Keystone\Domains\Workflow\Http\Requests\RevertProductRequest;
use JayI\Keystone\Domains\Workflow\Http\Requests\ShowProductVersionRequest;
use JayI\Keystone\Domains\Workflow\Http\Requests\TransitionProductRequest;

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
