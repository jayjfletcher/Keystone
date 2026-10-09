<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Channel\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Http\Requests\Request;
use RefactorCircus\Showroom\Domains\Channel\Actions\CreateLocaleAction;
use RefactorCircus\Showroom\Domains\Channel\Models\LocaleModel;
use RefactorCircus\Showroom\Domains\Channel\Resources\LocaleResource;

final class StoreLocaleRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', LocaleModel::class);
    }

    public function rules(): array
    {
        return CreateLocaleAction::rules();
    }

    public function persist(): JsonResponse
    {
        $locale = app(CreateLocaleAction::class)->execute($this->validated());

        return (new LocaleResource($locale))->response()->setStatusCode(201);
    }
}
