<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Channel\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Domains\Channel\Actions\ShowLocaleAction;
use RefactorCircus\Keystone\Domains\Channel\Resources\LocaleResource;

final class ShowLocaleMcpRequest extends LocaleRequest
{
    protected function authorize(): bool
    {
        return $this->allows('view', $this->locale());
    }

    protected function rules(): array
    {
        return ShowLocaleAction::rules() + [
            'locale' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $locale = app(ShowLocaleAction::class)->execute($this->locale());

        return Response::structured((new LocaleResource($locale))->resolve());
    }
}
