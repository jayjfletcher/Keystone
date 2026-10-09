<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Association\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Domains\Association\Actions\ShowAssociationTypeAction;
use RefactorCircus\Keystone\Domains\Association\Resources\AssociationTypeResource;

final class ShowAssociationTypeMcpRequest extends AssociationTypeRequest
{
    protected function authorize(): bool
    {
        return $this->allows('view', $this->associationType());
    }

    protected function rules(): array
    {
        return ShowAssociationTypeAction::rules() + [
            'association_type' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $associationType = app(ShowAssociationTypeAction::class)->execute($this->associationType());

        return Response::structured((new AssociationTypeResource($associationType))->resolve());
    }
}
