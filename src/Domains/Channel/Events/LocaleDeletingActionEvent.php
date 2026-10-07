<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;
use JayI\Keystone\Domains\Channel\Models\LocaleModel;

/**
 * A locale is about to be deleted.
 */
final class LocaleDeletingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public LocaleModel $locale,
    ) {}
}
