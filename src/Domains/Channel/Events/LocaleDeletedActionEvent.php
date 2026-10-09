<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Channel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Foundation\Contracts\ActionFinishedEvent;
use RefactorCircus\Keystone\Domains\Channel\Models\LocaleModel;

/**
 * A locale was deleted.
 */
final class LocaleDeletedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public LocaleModel $locale,
    ) {}
}
