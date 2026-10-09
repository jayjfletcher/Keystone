<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Attribute\Http\Requests\DeleteAttributeRequest;
use RefactorCircus\Keystone\Domains\Attribute\Http\Requests\IndexAttributesRequest;
use RefactorCircus\Keystone\Domains\Attribute\Http\Requests\ShowAttributeRequest;
use RefactorCircus\Keystone\Domains\Attribute\Http\Requests\StoreAttributeRequest;
use RefactorCircus\Keystone\Domains\Attribute\Http\Requests\UpdateAttributeRequest;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;

final class AttributeController
{
    public function index(IndexAttributesRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function store(StoreAttributeRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function show(ShowAttributeRequest $request, AttributeModel $attribute): JsonResponse
    {
        return $request->persist();
    }

    public function update(UpdateAttributeRequest $request, AttributeModel $attribute): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DeleteAttributeRequest $request, AttributeModel $attribute): JsonResponse
    {
        return $request->persist();
    }
}
