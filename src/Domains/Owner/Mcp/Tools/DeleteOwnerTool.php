<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Owner\Mcp\Requests\DeleteOwnerMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Delete an owner with no child owners, products or product models.')]
final class DeleteOwnerTool extends Tool
{
    public function handle(DeleteOwnerMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'owner' => $schema->string()->description('The owner code.')->required(),
        ];
    }
}
