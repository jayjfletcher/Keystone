<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Attribute\Http\Requests\DeleteAttributeGroupRequest;
use JayI\Keystone\Domains\Attribute\Http\Requests\IndexAttributeGroupsRequest;
use JayI\Keystone\Domains\Attribute\Http\Requests\ShowAttributeGroupRequest;
use JayI\Keystone\Domains\Attribute\Http\Requests\StoreAttributeGroupRequest;
use JayI\Keystone\Domains\Attribute\Http\Requests\UpdateAttributeGroupRequest;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;

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
