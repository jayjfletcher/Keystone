<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Association\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Association\Mcp\Requests\DeleteAssociationTypeMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

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
