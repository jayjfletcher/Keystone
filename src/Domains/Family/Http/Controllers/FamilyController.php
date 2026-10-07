<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Family\Http\Requests\DeleteFamilyRequest;
use JayI\Keystone\Domains\Family\Http\Requests\IndexFamiliesRequest;
use JayI\Keystone\Domains\Family\Http\Requests\ShowFamilyRequest;
use JayI\Keystone\Domains\Family\Http\Requests\StoreFamilyRequest;
use JayI\Keystone\Domains\Family\Http\Requests\UpdateFamilyRequest;
use JayI\Keystone\Domains\Family\Models\FamilyModel;

final class FamilyController
{
    public function index(IndexFamiliesRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function store(StoreFamilyRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function show(ShowFamilyRequest $request, FamilyModel $family): JsonResponse
    {
        return $request->persist();
    }

    public function update(UpdateFamilyRequest $request, FamilyModel $family): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DeleteFamilyRequest $request, FamilyModel $family): JsonResponse
    {
        return $request->persist();
    }
}
