<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Channel\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use RefactorCircus\Foundation\Mcp\Tool;
use RefactorCircus\Keystone\Domains\Channel\Mcp\Requests\UpdateLocaleMcpRequest;

#[Description('Update a locale\'s labels. The code cannot change.')]
final class UpdateLocaleTool extends Tool
{
    public function handle(UpdateLocaleMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'locale' => $schema->string()->description('The locale code.')->required(),
            'labels' => $schema->object()->description('Labels keyed by locale. Replaces all labels when given.'),
        ];
    }
}
