<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Keystone\Domains\Channel\Models\LocaleModel;

/**
 * A locale was shown.
 */
final class LocaleShownActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public LocaleModel $locale,
    ) {}
}
