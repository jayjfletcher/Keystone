<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Workflow\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Domains\Product\Mcp\Requests\ProductRequest;
use RefactorCircus\Keystone\Domains\Workflow\Actions\ShowProductVersionAction;
use RefactorCircus\Keystone\Domains\Workflow\Resources\VersionResource;

final class ShowProductVersionMcpRequest extends ProductRequest
{
    protected function authorize(): bool
    {
        return $this->allows('view', $this->product());
    }

    protected function rules(): array
    {
        return ShowProductVersionAction::rules() + [
            'product' => ['required', 'string', 'max:191'],
            'version' => ['required', 'string', 'max:20'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $version = app(ShowProductVersionAction::class)->execute($this->product(), (string) $validated['version']);

        return Response::structured((new VersionResource($version))->resolve());
    }
}
