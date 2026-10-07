<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Attribute\Mcp\Requests\DeleteAttributeOptionMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Delete an option from a select or multiselect attribute.')]
final class DeleteAttributeOptionTool extends Tool
{
    public function handle(DeleteAttributeOptionMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'attribute' => $schema->string()->description('The attribute code.')->required(),
            'option' => $schema->string()->description('The option code.')->required(),
        ];
    }
}
