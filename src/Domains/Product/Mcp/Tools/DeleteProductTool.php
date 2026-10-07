<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Product\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Product\Mcp\Requests\DeleteProductMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Delete a product.')]
final class DeleteProductTool extends Tool
{
    public function handle(DeleteProductMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'product' => $schema->string()->description('The product identifier.')->required(),
        ];
    }
}
