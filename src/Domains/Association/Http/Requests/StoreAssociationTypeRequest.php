<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Association\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Association\Actions\CreateAssociationTypeAction;
use JayI\Keystone\Domains\Association\Models\AssociationTypeModel;
use JayI\Keystone\Domains\Association\Resources\AssociationTypeResource;

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
