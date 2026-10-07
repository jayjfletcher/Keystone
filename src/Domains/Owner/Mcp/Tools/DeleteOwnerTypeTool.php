<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Owner\Mcp\Requests\DeleteOwnerTypeMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Delete an owner type that has no owners.')]
final class DeleteOwnerTypeTool extends Tool
{
    public function handle(DeleteOwnerTypeMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'owner_type' => $schema->string()->description('The owner type code.')->required(),
        ];
    }
}
