<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Actions;

use JayI\Keystone\Domains\Channel\Events\LocaleDeletedActionEvent;
use JayI\Keystone\Domains\Channel\Events\LocaleDeletingActionEvent;
use JayI\Keystone\Domains\Channel\Models\LocaleModel;
use JayI\Keystone\Exceptions\ModelInUseException;
use JayI\Keystone\Jobs\PurgeValueSlots;

final class DeleteLocaleAction
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
        LocaleDeletingActionEvent::dispatch($locale);

        $result = $this->perform($locale);

        LocaleDeletedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * Refused while a channel publishes in the locale; otherwise the values
     * written in it are purged by a queued job.
     */
    private function perform(LocaleModel $locale): LocaleModel
    {
        /** @var array<int, string> $channels */
        $channels = $locale->channels()->pluck('code')->all();

        if ($channels !== []) {
            throw ModelInUseException::localeInChannels($locale->code, $channels);
        }

        $locale->delete();

        PurgeValueSlots::dispatch(locale: $locale->code)->afterCommit();

        return $locale;
    }
}
