<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Association\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use RefactorCircus\Foundation\Mcp\Tool;
use RefactorCircus\Keystone\Domains\Association\Mcp\Requests\DeleteAssociationTypeMcpRequest;

#[Description('Delete an association type no association uses.')]
final class DeleteAssociationTypeTool extends Tool
{
    public function handle(DeleteAssociationTypeMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'association_type' => $schema->string()->description('The association type code.')->required(),
        ];
    }
}
