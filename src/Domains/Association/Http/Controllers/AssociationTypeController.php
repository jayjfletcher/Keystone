<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Association\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Association\Http\Requests\DeleteAssociationTypeRequest;
use JayI\Keystone\Domains\Association\Http\Requests\IndexAssociationTypesRequest;
use JayI\Keystone\Domains\Association\Http\Requests\ShowAssociationTypeRequest;
use JayI\Keystone\Domains\Association\Http\Requests\StoreAssociationTypeRequest;
use JayI\Keystone\Domains\Association\Http\Requests\UpdateAssociationTypeRequest;
use JayI\Keystone\Domains\Association\Models\AssociationTypeModel;

final class AssociationTypeController
{
    public function index(IndexAssociationTypesRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function store(StoreAssociationTypeRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function show(ShowAssociationTypeRequest $request, AssociationTypeModel $associationType): JsonResponse
    {
        return $request->persist();
    }

    public function update(UpdateAssociationTypeRequest $request, AssociationTypeModel $associationType): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DeleteAssociationTypeRequest $request, AssociationTypeModel $associationType): JsonResponse
    {
        return $request->persist();
    }
}
