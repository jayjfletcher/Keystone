<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\ProductModel\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use RefactorCircus\Keystone\Mcp\Tool;
use RefactorCircus\Showroom\Domains\ProductModel\Mcp\Requests\ListProductModelsMcpRequest;

#[Description('List product models (the shared parts of variant products), by family variant or parent. Cursor paginated.')]
final class ListProductModelsTool extends Tool
{
    public function handle(ListProductModelsMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'family_variant' => $schema->string()->description('Code of the family variant, for a root model. A sub-model takes its parent\'s.'),
            'parent' => $schema->string()->description('Only models under this parent model code.'),
            'roots' => $schema->boolean()->description('Only root models.'),
            'search' => $schema->string()->description('Match against the code.'),
            'cursor' => $schema->string()->description('Cursor from a previous page (next_cursor).'),
            'per_page' => $schema->integer()->description('Results per page.')->min(1),
        ];
    }
}
