<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Channel\Actions;

use RefactorCircus\Showroom\Domains\Channel\Events\LocaleCreatedActionEvent;
use RefactorCircus\Showroom\Domains\Channel\Events\LocaleCreatingActionEvent;
use RefactorCircus\Showroom\Domains\Channel\Models\LocaleModel;

final class CreateLocaleAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            // A language, optionally with a region or script: en, en_US, zh-Hant.
            'code' => ['required', 'string', 'max:20', 'regex:/^[a-z]{2,3}([_-][A-Za-z0-9]{2,8})*$/', 'unique:showroom_locales,code'],
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): LocaleModel
    {
        LocaleCreatingActionEvent::dispatch($data);

        $result = $this->perform($data);

        LocaleCreatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(array $data): LocaleModel
    {
        return LocaleModel::query()->create([
            'code' => $data['code'],
            'labels' => $data['labels'] ?? [],
        ]);
    }
}
