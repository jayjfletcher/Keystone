<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Attribute\Mcp\Requests\ListAttributeGroupsMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('List attribute groups, the sections attributes are organised into, in display order. Cursor paginated.')]
final class ListAttributeGroupsTool extends Tool
{
    public function handle(ListAttributeGroupsMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Match against the code or any label.'),
            'cursor' => $schema->string()->description('Cursor from a previous page (next_cursor).'),
            'per_page' => $schema->integer()->description('Results per page.')->min(1),
        ];
    }
}
