<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Family\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use RefactorCircus\Foundation\Mcp\Tool;
use RefactorCircus\Showroom\Domains\Family\Mcp\Requests\ListFamilyVariantsMcpRequest;

#[Description('List family variants: how the products of a family vary (by color, then size). Cursor paginated.')]
final class ListFamilyVariantsTool extends Tool
{
    public function handle(ListFamilyVariantsMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'family' => $schema->string()->description('Only this family.'),
            'search' => $schema->string()->description('Match against the code.'),
            'cursor' => $schema->string()->description('Cursor from a previous page (next_cursor).'),
            'per_page' => $schema->integer()->description('Results per page.')->min(1),
        ];
    }
}
