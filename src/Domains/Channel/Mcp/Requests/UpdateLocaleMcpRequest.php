<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Channel\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Domains\Channel\Actions\UpdateLocaleAction;
use RefactorCircus\Keystone\Domains\Channel\Resources\LocaleResource;

final class UpdateLocaleMcpRequest extends LocaleRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->locale());
    }

    protected function rules(): array
    {
        return UpdateLocaleAction::rules() + [
            'locale' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['locale']);

        $locale = app(UpdateLocaleAction::class)->execute($this->locale(), $validated);

        return Response::structured((new LocaleResource($locale))->resolve());
    }
}
