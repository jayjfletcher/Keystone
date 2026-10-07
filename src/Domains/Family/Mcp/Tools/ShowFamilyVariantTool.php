<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Family\Mcp\Requests\ShowFamilyVariantMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Show a family variant with its levels, axes, the attributes set at each level, and the common attributes set on root models.')]
final class ShowFamilyVariantTool extends Tool
{
    public function handle(ShowFamilyVariantMcpRequest $request): Response|ResponseFactory
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
