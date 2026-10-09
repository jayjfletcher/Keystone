<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Category\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use RefactorCircus\Foundation\Mcp\Tool;
use RefactorCircus\Keystone\Domains\Category\Mcp\Requests\ListCategoriesMcpRequest;

#[Description('List categories: the trees (roots), the children of a category, or a whole branch. Cursor paginated.')]
final class ListCategoriesTool extends Tool
{
    public function handle(ListCategoriesMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'roots' => $schema->boolean()->description('Only tree roots: the list of category trees.'),
            'parent' => $schema->string()->description('Only direct children of this category code.'),
            'under' => $schema->string()->description('Everything beneath this category code, at any depth.'),
            'search' => $schema->string()->description('Match against the code or any label.'),
            'cursor' => $schema->string()->description('Cursor from a previous page (next_cursor).'),
            'per_page' => $schema->integer()->description('Results per page.')->min(1),
        ];
    }
}
