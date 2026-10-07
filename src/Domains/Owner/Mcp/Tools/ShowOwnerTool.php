<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Owner\Mcp\Requests\ShowOwnerMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Show an owner with its chain from the root, its children and how many products it owns.')]
final class ShowOwnerTool extends Tool
{
    public function handle(ShowOwnerMcpRequest $request): Response|ResponseFactory
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
