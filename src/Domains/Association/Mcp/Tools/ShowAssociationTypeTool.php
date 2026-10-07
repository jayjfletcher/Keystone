<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Association\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Association\Mcp\Requests\ShowAssociationTypeMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Show an association type and how many associations use it.')]
final class ShowAssociationTypeTool extends Tool
{
    public function handle(ShowAssociationTypeMcpRequest $request): Response|ResponseFactory
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
