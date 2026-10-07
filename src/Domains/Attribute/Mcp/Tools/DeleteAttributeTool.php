<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Attribute\Mcp\Requests\DeleteAttributeMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Delete an attribute and its options.')]
final class DeleteAttributeTool extends Tool
{
    public function handle(DeleteAttributeMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'attribute' => $schema->string()->description('The attribute code.')->required(),
        ];
    }
}
