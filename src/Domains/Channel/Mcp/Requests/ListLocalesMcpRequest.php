<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Channel\Actions\ListLocalesAction;
use JayI\Keystone\Domains\Channel\Models\LocaleModel;
use JayI\Keystone\Domains\Channel\Resources\LocaleResource;
use Laravel\Mcp\ResponseFactory;

final class ListLocalesMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', LocaleModel::class);
    }

    protected function rules(): array
    {
        return ListLocalesAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $locales = app(ListLocalesAction::class)->execute($validated);

        return $this->structuredCollection(
            LocaleResource::collection($locales)->resolve(),
            ['next_cursor' => $locales->nextCursor()?->encode()],
        );
    }
}
