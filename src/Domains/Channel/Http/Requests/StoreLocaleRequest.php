<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Channel\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Channel\Actions\CreateLocaleAction;
use RefactorCircus\Keystone\Domains\Channel\Models\LocaleModel;
use RefactorCircus\Keystone\Domains\Channel\Resources\LocaleResource;

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
