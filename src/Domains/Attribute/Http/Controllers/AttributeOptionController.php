<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Showroom\Domains\Attribute\Http\Requests\DeleteAttributeOptionRequest;
use RefactorCircus\Showroom\Domains\Attribute\Http\Requests\IndexAttributeOptionsRequest;
use RefactorCircus\Showroom\Domains\Attribute\Http\Requests\StoreAttributeOptionRequest;
use RefactorCircus\Showroom\Domains\Attribute\Http\Requests\UpdateAttributeOptionRequest;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Showroom\Domains\Attribute\Models\AttributeOptionModel;

final class AttributeOptionController
{
    public function index(IndexAttributeOptionsRequest $request, AttributeModel $attribute): JsonResponse
    {
        return $request->persist();
    }

    public function store(StoreAttributeOptionRequest $request, AttributeModel $attribute): JsonResponse
    {
        return $request->persist();
    }

    public function update(UpdateAttributeOptionRequest $request, AttributeModel $attribute, AttributeOptionModel $option): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DeleteAttributeOptionRequest $request, AttributeModel $attribute, AttributeOptionModel $option): JsonResponse
    {
        return $request->persist();
    }
}
