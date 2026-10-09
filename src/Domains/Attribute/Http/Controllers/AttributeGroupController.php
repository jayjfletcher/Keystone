<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Showroom\Domains\Attribute\Http\Requests\DeleteAttributeGroupRequest;
use RefactorCircus\Showroom\Domains\Attribute\Http\Requests\IndexAttributeGroupsRequest;
use RefactorCircus\Showroom\Domains\Attribute\Http\Requests\ShowAttributeGroupRequest;
use RefactorCircus\Showroom\Domains\Attribute\Http\Requests\StoreAttributeGroupRequest;
use RefactorCircus\Showroom\Domains\Attribute\Http\Requests\UpdateAttributeGroupRequest;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeGroupModel;

final class AttributeGroupController
{
    public function index(IndexAttributeGroupsRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function store(StoreAttributeGroupRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function show(ShowAttributeGroupRequest $request, AttributeGroupModel $group): JsonResponse
    {
        return $request->persist();
    }

    public function update(UpdateAttributeGroupRequest $request, AttributeGroupModel $group): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DeleteAttributeGroupRequest $request, AttributeGroupModel $group): JsonResponse
    {
        return $request->persist();
    }
}
