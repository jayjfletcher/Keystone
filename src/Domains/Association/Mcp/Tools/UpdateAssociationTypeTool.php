<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Association\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use RefactorCircus\Foundation\Mcp\Tool;
use RefactorCircus\Showroom\Domains\Association\Mcp\Requests\UpdateAssociationTypeMcpRequest;

#[Description('Update an association type\'s labels. The code and flags cannot change.')]
final class UpdateAssociationTypeTool extends Tool
{
    public function handle(UpdateAssociationTypeMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'association_type' => $schema->string()->description('The association type code.')->required(),
            'labels' => $schema->object()->description('Labels keyed by locale. Replaces all labels when given.'),
        ];
    }
}
