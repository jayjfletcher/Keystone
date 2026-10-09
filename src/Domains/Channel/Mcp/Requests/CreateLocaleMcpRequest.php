<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Channel\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Showroom\Domains\Channel\Actions\CreateLocaleAction;
use RefactorCircus\Showroom\Domains\Channel\Models\LocaleModel;
use RefactorCircus\Showroom\Domains\Channel\Resources\LocaleResource;

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
