<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Association\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Association\Actions\CreateAssociationTypeAction;
use RefactorCircus\Keystone\Domains\Association\Models\AssociationTypeModel;
use RefactorCircus\Keystone\Domains\Association\Resources\AssociationTypeResource;

final class StoreAssociationTypeRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', AssociationTypeModel::class);
    }

    public function rules(): array
    {
        return CreateAssociationTypeAction::rules();
    }

    public function persist(): JsonResponse
    {
        $associationType = app(CreateAssociationTypeAction::class)->execute($this->validated());

        return (new AssociationTypeResource($associationType))->response()->setStatusCode(201);
    }
}
