<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Owner\Http\Requests\DeleteOwnerRequest;
use JayI\Keystone\Domains\Owner\Http\Requests\IndexOwnersRequest;
use JayI\Keystone\Domains\Owner\Http\Requests\ShowOwnerRequest;
use JayI\Keystone\Domains\Owner\Http\Requests\StoreOwnerRequest;
use JayI\Keystone\Domains\Owner\Http\Requests\UpdateOwnerRequest;
use JayI\Keystone\Domains\Owner\Models\OwnerModel;

final class OwnerController
{
    public function index(IndexOwnersRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function store(StoreOwnerRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function show(ShowOwnerRequest $request, OwnerModel $owner): JsonResponse
    {
        return $request->persist();
    }

    public function update(UpdateOwnerRequest $request, OwnerModel $owner): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DeleteOwnerRequest $request, OwnerModel $owner): JsonResponse
    {
        return $request->persist();
    }
}
