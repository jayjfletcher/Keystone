<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Channel\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Showroom\Domains\Channel\Actions\UpdateLocaleAction;
use RefactorCircus\Showroom\Domains\Channel\Resources\LocaleResource;

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
