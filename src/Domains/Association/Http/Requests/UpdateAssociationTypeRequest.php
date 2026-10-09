<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Association\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Showroom\Domains\Association\Actions\UpdateAssociationTypeAction;
use RefactorCircus\Showroom\Domains\Association\Resources\AssociationTypeResource;

final class UpdateAssociationTypeRequest extends AssociationTypeRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->associationType());
    }

    public function rules(): array
    {
        return UpdateAssociationTypeAction::rules();
    }

    public function persist(): JsonResponse
    {
        $associationType = app(UpdateAssociationTypeAction::class)->execute($this->associationType(), $this->validated());

        return (new AssociationTypeResource($associationType))->response();
    }
}
