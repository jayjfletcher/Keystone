<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Association\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Domains\Association\Actions\UpdateAssociationTypeAction;
use RefactorCircus\Keystone\Domains\Association\Resources\AssociationTypeResource;

final class UpdateAssociationTypeMcpRequest extends AssociationTypeRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->associationType());
    }

    protected function rules(): array
    {
        return UpdateAssociationTypeAction::rules() + [
            'association_type' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['association_type']);

        $associationType = app(UpdateAssociationTypeAction::class)->execute($this->associationType(), $validated);

        return Response::structured((new AssociationTypeResource($associationType))->resolve());
    }
}
