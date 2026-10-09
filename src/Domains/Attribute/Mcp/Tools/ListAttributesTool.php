<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Attribute\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use RefactorCircus\Keystone\Mcp\Tool;
use RefactorCircus\Showroom\Domains\Attribute\Mcp\Requests\ListAttributesMcpRequest;

#[Description('List attributes, the typed product characteristics of the catalog, in display order. Filter by type, group, or search. Cursor paginated.')]
final class ListAttributesTool extends Tool
{
    public function handle(ListAttributesMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()->description('Only attributes of this type: text, textarea, number, decimal, boolean, date, select, multiselect, price, or metric.'),
            'group' => $schema->string()->description('Only attributes in this attribute group code.'),
            'search' => $schema->string()->description('Match against the code or any label.'),
            'cursor' => $schema->string()->description('Cursor from a previous page (next_cursor).'),
            'per_page' => $schema->integer()->description('Results per page.')->min(1),
        ];
    }
}
