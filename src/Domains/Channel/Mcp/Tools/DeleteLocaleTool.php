<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Channel\Mcp\Requests\DeleteLocaleMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Delete a locale no channel uses. Values written in it are purged.')]
final class DeleteLocaleTool extends Tool
{
    public function handle(DeleteLocaleMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'locale' => $schema->string()->description('The locale code.')->required(),
        ];
    }
}
