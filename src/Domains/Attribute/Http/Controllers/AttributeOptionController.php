<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Attribute\Http\Requests\DeleteAttributeOptionRequest;
use JayI\Keystone\Domains\Attribute\Http\Requests\IndexAttributeOptionsRequest;
use JayI\Keystone\Domains\Attribute\Http\Requests\StoreAttributeOptionRequest;
use JayI\Keystone\Domains\Attribute\Http\Requests\UpdateAttributeOptionRequest;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;
use JayI\Keystone\Domains\Attribute\Models\AttributeOptionModel;

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
