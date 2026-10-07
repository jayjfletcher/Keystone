<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Family\Mcp\Requests\ShowFamilyMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Show a family with its attributes, which of them are required, and its label attribute.')]
final class ShowFamilyTool extends Tool
{
    public function handle(ShowFamilyMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'family' => $schema->string()->description('The family code.')->required(),
        ];
    }
}
