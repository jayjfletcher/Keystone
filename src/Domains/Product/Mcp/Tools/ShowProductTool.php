<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Product\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Product\Mcp\Requests\ShowProductMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Show a product with all its values, including those inherited from its product models.')]
final class ShowProductTool extends Tool
{
    public function handle(ShowProductMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'product' => $schema->string()->description('The product identifier.')->required(),
            'scope' => $schema->string()->description('Return only this channel\'s values (plus channel-independent ones).'),
            'locales' => $schema->array()->description('Return only these locales\' values (plus locale-independent ones), such as ["en_US"].'),
        ];
    }
}
