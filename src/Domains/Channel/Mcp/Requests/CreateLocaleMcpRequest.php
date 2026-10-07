<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Channel\Actions\CreateLocaleAction;
use JayI\Keystone\Domains\Channel\Models\LocaleModel;
use JayI\Keystone\Domains\Channel\Resources\LocaleResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class CreateLocaleMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', LocaleModel::class);
    }

    protected function rules(): array
    {
        return CreateLocaleAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $locale = app(CreateLocaleAction::class)->execute($validated);

        return Response::structured((new LocaleResource($locale))->resolve());
    }
}
