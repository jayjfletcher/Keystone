<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Family\Mcp\Requests\DeleteFamilyMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Delete a family. Its attributes are not deleted.')]
final class DeleteFamilyTool extends Tool
{
    public function handle(DeleteFamilyMcpRequest $request): Response|ResponseFactory
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
