<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Actions;

use JayI\Keystone\Domains\Channel\Events\LocaleUpdatedActionEvent;
use JayI\Keystone\Domains\Channel\Events\LocaleUpdatingActionEvent;
use JayI\Keystone\Domains\Channel\Models\LocaleModel;

final class UpdateLocaleAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            // Stored values are keyed by locale code; it never changes.
            'code' => ['prohibited'],
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(LocaleModel $locale, array $data): LocaleModel
    {
        LocaleUpdatingActionEvent::dispatch($locale, $data);

        $result = $this->perform($locale, $data);

        LocaleUpdatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(LocaleModel $locale, array $data): LocaleModel
    {
        if (array_key_exists('labels', $data)) {
            $locale->labels = is_array($data['labels']) ? $data['labels'] : [];
            $locale->save();
        }

        return $locale;
    }
}
