<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Workflow\Mcp\Requests\ListProductVersionsMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('List a product\'s history, newest first: each version\'s action, author, time and what changed. Cursor paginated.')]
final class ListProductVersionsTool extends Tool
{
    public function handle(ListProductVersionsMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'product' => $schema->string()->description('The product identifier.')->required(),
            'action' => $schema->string()->description('Only versions of this action: created, updated, submitted, approved, rejected, published, unpublished, archived, restored, reverted.'),
            'cursor' => $schema->string()->description('Cursor from a previous page (next_cursor).'),
            'per_page' => $schema->integer()->description('Results per page.')->min(1),
        ];
    }
}
