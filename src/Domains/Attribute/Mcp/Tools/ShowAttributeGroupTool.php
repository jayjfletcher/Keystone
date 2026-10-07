<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Attribute\Mcp\Requests\ShowAttributeGroupMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Show an attribute group with the attributes it holds.')]
final class ShowAttributeGroupTool extends Tool
{
    public function handle(ShowAttributeGroupMcpRequest $request): Response|ResponseFactory
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
