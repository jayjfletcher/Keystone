<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Channel\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Channel\Actions\ShowLocaleAction;
use RefactorCircus\Keystone\Domains\Channel\Resources\LocaleResource;

final class ShowLocaleRequest extends LocaleRequest
{
    public function authorize(): bool
    {
        return $this->allows('view', $this->locale());
    }

    public function rules(): array
    {
        return ShowLocaleAction::rules();
    }

    public function persist(): JsonResponse
    {
        $locale = app(ShowLocaleAction::class)->execute($this->locale());

        return (new LocaleResource($locale))->response();
    }
}
