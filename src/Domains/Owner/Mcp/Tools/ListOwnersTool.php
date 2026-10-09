<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Owner\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use RefactorCircus\Keystone\Mcp\Tool;
use RefactorCircus\Showroom\Domains\Owner\Mcp\Requests\ListOwnersMcpRequest;

#[Description('List owners by type, parent, or everything under an owner. Cursor paginated.')]
final class ListOwnersTool extends Tool
{
    public function handle(ListOwnersMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()->description('Only owners of this type.'),
            'parent' => $schema->string()->description('Only direct children of this owner code.'),
            'under' => $schema->string()->description('Everything beneath this owner code, at any depth.'),
            'roots' => $schema->boolean()->description('Only root owners.'),
            'search' => $schema->string()->description('Match against the code or any label.'),
            'cursor' => $schema->string()->description('Cursor from a previous page (next_cursor).'),
            'per_page' => $schema->integer()->description('Results per page.')->min(1),
        ];
    }
}
