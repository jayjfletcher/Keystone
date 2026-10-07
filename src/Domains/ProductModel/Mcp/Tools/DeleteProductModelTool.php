<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\ProductModel\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\ProductModel\Mcp\Requests\DeleteProductModelMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Delete a product model with its sub-models and variant products.')]
final class DeleteProductModelTool extends Tool
{
    public function handle(DeleteProductModelMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'product_model' => $schema->string()->description('The product model code.')->required(),
        ];
    }
}
