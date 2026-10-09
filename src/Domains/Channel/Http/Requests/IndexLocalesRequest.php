<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Channel\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Showroom\Domains\Channel\Actions\ListLocalesAction;
use RefactorCircus\Showroom\Domains\Channel\Models\LocaleModel;
use RefactorCircus\Showroom\Domains\Channel\Resources\LocaleResource;

final class IndexLocalesRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', LocaleModel::class);
    }

    public function rules(): array
    {
        return ListLocalesAction::rules();
    }

    public function persist(): JsonResponse
    {
        $locales = app(ListLocalesAction::class)->execute($this->validated());

        return LocaleResource::collection($locales)->response();
    }
}
