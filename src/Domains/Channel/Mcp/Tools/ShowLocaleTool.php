<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Channel\Mcp\Requests\ShowLocaleMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Show a locale and the channels that publish in it.')]
final class ShowLocaleTool extends Tool
{
    public function handle(ShowLocaleMcpRequest $request): Response|ResponseFactory
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
