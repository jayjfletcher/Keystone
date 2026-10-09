<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Owner\Mcp\Requests;

use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Mcp\Requests\Request;
use RefactorCircus\Showroom\Domains\Owner\Actions\ListOwnersAction;
use RefactorCircus\Showroom\Domains\Owner\Models\OwnerModel;
use RefactorCircus\Showroom\Domains\Owner\Resources\OwnerResource;

final class ListOwnersMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', OwnerModel::class);
    }

    protected function rules(): array
    {
        return ListOwnersAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $owners = app(ListOwnersAction::class)->execute($validated);

        return $this->structuredCollection(
            OwnerResource::collection($owners)->resolve(),
            ['next_cursor' => $owners->nextCursor()?->encode()],
        );
    }
}
