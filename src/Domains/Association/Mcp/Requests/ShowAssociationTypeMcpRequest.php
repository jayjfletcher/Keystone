<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Association\Mcp\Requests;

use JayI\Keystone\Domains\Association\Actions\ShowAssociationTypeAction;
use JayI\Keystone\Domains\Association\Resources\AssociationTypeResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

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
