<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Channel\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use RefactorCircus\Foundation\Mcp\Tool;
use RefactorCircus\Keystone\Domains\Channel\Mcp\Requests\ListChannelsMcpRequest;

#[Description('List channels (storefronts, print, marketplaces) with their locales, currencies and category tree. Cursor paginated.')]
final class ListChannelsTool extends Tool
{
    public function handle(ListChannelsMcpRequest $request): Response|ResponseFactory
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
