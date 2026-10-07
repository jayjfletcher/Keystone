<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Attribute\Mcp\Requests\DeleteAttributeGroupMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Delete an empty attribute group. A group that still holds attributes is refused: move them to another group first.')]
final class DeleteAttributeGroupTool extends Tool
{
    public function handle(DeleteAttributeGroupMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'group' => $schema->string()->description('The attribute group code.')->required(),
        ];
    }
}
