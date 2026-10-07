<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Foundation\Mcp\Tool;
use JayI\Keystone\Domains\Workflow\Mcp\Requests\TransitionProductMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Move a product through the workflow: submit (draft to in review), approve, reject (back to draft), publish (make the current version the one storefronts read; needs approval by default), unpublish, archive, restore.')]
final class TransitionProductTool extends Tool
{
    public function handle(TransitionProductMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'product' => $schema->string()->description('The product identifier.')->required(),
            'transition' => $schema->string()->description('submit, approve, reject, publish, unpublish, archive or restore.')->required(),
            'comment' => $schema->string()->description('Why, recorded in the history; useful when rejecting.'),
        ];
    }
}
