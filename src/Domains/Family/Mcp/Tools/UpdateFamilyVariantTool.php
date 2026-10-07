<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Family\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Family\Mcp\Requests\UpdateFamilyVariantMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Update a family variant\'s labels, or its levels while no product model uses it. The code and family cannot change.')]
final class UpdateFamilyVariantTool extends Tool
{
    public function handle(UpdateFamilyVariantMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'family_variant' => $schema->string()->description('The family variant code.')->required(),
            'labels' => $schema->object()->description('Labels keyed by locale. Replaces all labels when given.'),
            'levels' => $schema->array()->description('Replace the levels, as for create. Only while no product model uses the variant.'),
        ];
    }
}
