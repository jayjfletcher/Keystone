<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Family\Mcp\Requests\ListFamiliesMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('List families (attribute sets), the kinds of product the catalog describes, in display order. Filter by search or by an attribute they include. Cursor paginated.')]
final class ListFamiliesTool extends Tool
{
    public function handle(ListFamiliesMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Match against the code or any label.'),
            'attribute' => $schema->string()->description('Only families that include this attribute code.'),
            'cursor' => $schema->string()->description('Cursor from a previous page (next_cursor).'),
            'per_page' => $schema->integer()->description('Results per page.')->min(1),
        ];
    }
}
