<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Owner\Http\Requests\DeleteOwnerTypeRequest;
use JayI\Keystone\Domains\Owner\Http\Requests\IndexOwnerTypesRequest;
use JayI\Keystone\Domains\Owner\Http\Requests\ShowOwnerTypeRequest;
use JayI\Keystone\Domains\Owner\Http\Requests\StoreOwnerTypeRequest;
use JayI\Keystone\Domains\Owner\Http\Requests\UpdateOwnerTypeRequest;
use JayI\Keystone\Domains\Owner\Models\OwnerTypeModel;

final class OwnerTypeController
{
    public function index(IndexOwnerTypesRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function store(StoreOwnerTypeRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function show(ShowOwnerTypeRequest $request, OwnerTypeModel $ownerType): JsonResponse
    {
        return $request->persist();
    }

    public function update(UpdateOwnerTypeRequest $request, OwnerTypeModel $ownerType): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DeleteOwnerTypeRequest $request, OwnerTypeModel $ownerType): JsonResponse
    {
        return $request->persist();
    }
}
