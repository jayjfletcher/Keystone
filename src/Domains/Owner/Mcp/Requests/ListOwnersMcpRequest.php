<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Owner\Actions\ListOwnersAction;
use JayI\Keystone\Domains\Owner\Models\OwnerModel;
use JayI\Keystone\Domains\Owner\Resources\OwnerResource;
use Laravel\Mcp\ResponseFactory;

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
