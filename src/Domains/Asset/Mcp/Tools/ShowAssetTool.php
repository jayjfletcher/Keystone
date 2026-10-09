<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Asset\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use RefactorCircus\Foundation\Mcp\Tool;
use RefactorCircus\Keystone\Domains\Asset\Mcp\Requests\ShowAssetMcpRequest;

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
