<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Family\Mcp\Requests\DeleteFamilyVariantMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Delete a family variant that no product model uses.')]
final class DeleteFamilyVariantTool extends Tool
{
    public function handle(DeleteFamilyVariantMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'family_variant' => $schema->string()->description('The family variant code.')->required(),
        ];
    }
}
