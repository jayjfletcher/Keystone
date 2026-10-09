<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Family\Http\Requests\DeleteFamilyVariantRequest;
use RefactorCircus\Keystone\Domains\Family\Http\Requests\IndexFamilyVariantsRequest;
use RefactorCircus\Keystone\Domains\Family\Http\Requests\ShowFamilyVariantRequest;
use RefactorCircus\Keystone\Domains\Family\Http\Requests\StoreFamilyVariantRequest;
use RefactorCircus\Keystone\Domains\Family\Http\Requests\UpdateFamilyVariantRequest;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyVariantModel;

final class FamilyVariantController
{
    public function index(IndexFamilyVariantsRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function store(StoreFamilyVariantRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function show(ShowFamilyVariantRequest $request, FamilyVariantModel $familyVariant): JsonResponse
    {
        return $request->persist();
    }

    public function update(UpdateFamilyVariantRequest $request, FamilyVariantModel $familyVariant): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DeleteFamilyVariantRequest $request, FamilyVariantModel $familyVariant): JsonResponse
    {
        return $request->persist();
    }
}
