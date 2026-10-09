<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use RefactorCircus\Foundation\Mcp\Tool;
use RefactorCircus\Keystone\Domains\Owner\Mcp\Requests\DeleteOwnerTypeMcpRequest;

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
