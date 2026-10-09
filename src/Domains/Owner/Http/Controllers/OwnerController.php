<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Owner\Http\Requests\DeleteOwnerRequest;
use RefactorCircus\Keystone\Domains\Owner\Http\Requests\IndexOwnersRequest;
use RefactorCircus\Keystone\Domains\Owner\Http\Requests\ShowOwnerRequest;
use RefactorCircus\Keystone\Domains\Owner\Http\Requests\StoreOwnerRequest;
use RefactorCircus\Keystone\Domains\Owner\Http\Requests\UpdateOwnerRequest;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerModel;

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
