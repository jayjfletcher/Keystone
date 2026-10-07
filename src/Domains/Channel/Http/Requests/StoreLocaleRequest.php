<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Channel\Actions\CreateLocaleAction;
use JayI\Keystone\Domains\Channel\Models\LocaleModel;
use JayI\Keystone\Domains\Channel\Resources\LocaleResource;

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
