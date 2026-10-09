<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Showroom\Domains\Attribute\Http\Requests\DeleteAttributeRequest;
use RefactorCircus\Showroom\Domains\Attribute\Http\Requests\IndexAttributesRequest;
use RefactorCircus\Showroom\Domains\Attribute\Http\Requests\ShowAttributeRequest;
use RefactorCircus\Showroom\Domains\Attribute\Http\Requests\StoreAttributeRequest;
use RefactorCircus\Showroom\Domains\Attribute\Http\Requests\UpdateAttributeRequest;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeModel;

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
