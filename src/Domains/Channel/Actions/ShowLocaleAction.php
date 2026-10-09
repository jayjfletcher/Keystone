<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Channel\Actions;

use RefactorCircus\Keystone\Domains\Channel\Events\LocaleShowingActionEvent;
use RefactorCircus\Keystone\Domains\Channel\Events\LocaleShownActionEvent;
use RefactorCircus\Keystone\Domains\Channel\Models\LocaleModel;

final class ShowLocaleAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(LocaleModel $locale): LocaleModel
    {
        LocaleShowingActionEvent::dispatch($locale);

        $result = $this->perform($locale);

        LocaleShownActionEvent::dispatch($result);

        return $result;
    }

    private function perform(LocaleModel $locale): LocaleModel
    {
        return $locale->load('channels');
    }
}
