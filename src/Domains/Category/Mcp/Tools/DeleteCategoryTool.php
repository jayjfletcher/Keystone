<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Category\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Category\Mcp\Requests\DeleteCategoryMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Delete a category that has no children. Products filed in it are unassigned from it.')]
final class DeleteCategoryTool extends Tool
{
    public function handle(DeleteCategoryMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'category' => $schema->string()->description('The category code.')->required(),
        ];
    }
}
