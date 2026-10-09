<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Workflow\Mcp\Requests;

use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Showroom\Domains\Product\Mcp\Requests\ProductRequest;
use RefactorCircus\Showroom\Domains\Workflow\Actions\ListProductVersionsAction;
use RefactorCircus\Showroom\Domains\Workflow\Resources\VersionSummaryResource;

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
