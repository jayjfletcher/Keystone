<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Channel\Actions\ListLocalesAction;
use JayI\Keystone\Domains\Channel\Models\LocaleModel;
use JayI\Keystone\Domains\Channel\Resources\LocaleResource;

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
