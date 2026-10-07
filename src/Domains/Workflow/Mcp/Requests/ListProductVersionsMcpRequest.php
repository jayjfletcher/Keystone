<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow\Mcp\Requests;

use JayI\Keystone\Domains\Product\Mcp\Requests\ProductRequest;
use JayI\Keystone\Domains\Workflow\Actions\ListProductVersionsAction;
use JayI\Keystone\Domains\Workflow\Resources\VersionSummaryResource;
use Laravel\Mcp\ResponseFactory;

final class ListProductVersionsMcpRequest extends ProductRequest
{
    protected function authorize(): bool
    {
        return $this->allows('view', $this->product());
    }

    protected function rules(): array
    {
        return ListProductVersionsAction::rules() + [
            'product' => ['required', 'string', 'max:191'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['product']);

        $versions = app(ListProductVersionsAction::class)->execute($this->product(), $validated);

        return $this->structuredCollection(
            VersionSummaryResource::collection($versions)->resolve(),
            ['next_cursor' => $versions->nextCursor()?->encode()],
        );
    }
}
