<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Channel\Actions\UpdateLocaleAction;
use JayI\Keystone\Domains\Channel\Resources\LocaleResource;

final class UpdateLocaleRequest extends LocaleRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->locale());
    }

    public function rules(): array
    {
        return UpdateLocaleAction::rules();
    }

    public function persist(): JsonResponse
    {
        $locale = app(UpdateLocaleAction::class)->execute($this->locale(), $this->validated());

        return (new LocaleResource($locale))->response();
    }
}
