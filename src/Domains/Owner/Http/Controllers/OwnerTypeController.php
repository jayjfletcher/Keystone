<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Owner\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Showroom\Domains\Owner\Http\Requests\DeleteOwnerTypeRequest;
use RefactorCircus\Showroom\Domains\Owner\Http\Requests\IndexOwnerTypesRequest;
use RefactorCircus\Showroom\Domains\Owner\Http\Requests\ShowOwnerTypeRequest;
use RefactorCircus\Showroom\Domains\Owner\Http\Requests\StoreOwnerTypeRequest;
use RefactorCircus\Showroom\Domains\Owner\Http\Requests\UpdateOwnerTypeRequest;
use RefactorCircus\Showroom\Domains\Owner\Models\OwnerTypeModel;

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
