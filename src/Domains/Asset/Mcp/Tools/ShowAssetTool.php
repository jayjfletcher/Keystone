<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Asset\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Asset\Mcp\Requests\ShowAssetMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Show an asset with its URL and every record it is linked to.')]
final class ShowAssetTool extends Tool
{
    public function handle(ShowAssetMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'asset' => $schema->string()->description('The asset code.')->required(),
        ];
    }
}
