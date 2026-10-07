<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Channel\Mcp\Requests\ShowChannelMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Show a channel with its locales, currencies and category tree.')]
final class ShowChannelTool extends Tool
{
    public function handle(ShowChannelMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'channel' => $schema->string()->description('The channel code.')->required(),
        ];
    }
}
