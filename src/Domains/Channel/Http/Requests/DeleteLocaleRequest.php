<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Channel\Actions\DeleteLocaleAction;

final class DeleteLocaleRequest extends LocaleRequest
{
    public function authorize(): bool
    {
        return $this->allows('delete', $this->locale());
    }

    public function rules(): array
    {
        return DeleteLocaleAction::rules();
    }

    public function persist(): JsonResponse
    {
        app(DeleteLocaleAction::class)->execute($this->locale());

        return new JsonResponse(null, 204);
    }
}
