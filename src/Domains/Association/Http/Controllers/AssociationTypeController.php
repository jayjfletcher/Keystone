<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Association\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Association\Http\Requests\DeleteAssociationTypeRequest;
use RefactorCircus\Keystone\Domains\Association\Http\Requests\IndexAssociationTypesRequest;
use RefactorCircus\Keystone\Domains\Association\Http\Requests\ShowAssociationTypeRequest;
use RefactorCircus\Keystone\Domains\Association\Http\Requests\StoreAssociationTypeRequest;
use RefactorCircus\Keystone\Domains\Association\Http\Requests\UpdateAssociationTypeRequest;
use RefactorCircus\Keystone\Domains\Association\Models\AssociationTypeModel;

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
