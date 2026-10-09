<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Channel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ActionFinishedEvent;
use RefactorCircus\Showroom\Domains\Channel\Models\LocaleModel;

/**
 * A locale was updated.
 */
final class LocaleUpdatedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public LocaleModel $locale,
    ) {}
}
