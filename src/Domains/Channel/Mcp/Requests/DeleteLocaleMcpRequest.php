<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Mcp\Requests;

use JayI\Keystone\Domains\Channel\Actions\DeleteLocaleAction;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class DeleteLocaleMcpRequest extends LocaleRequest
{
    protected function authorize(): bool
    {
        return $this->allows('delete', $this->locale());
    }

    protected function rules(): array
    {
        return DeleteLocaleAction::rules() + [
            'locale' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $locale = app(DeleteLocaleAction::class)->execute($this->locale());

        return Response::structured(['deleted' => true, 'code' => $locale->code]);
    }
}
