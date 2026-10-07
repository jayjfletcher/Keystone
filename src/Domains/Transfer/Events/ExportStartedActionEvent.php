<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Transfer\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionFinishedEvent;
use JayI\Impex\Domains\Run\Models\RunModel;

/**
 * A product export started.
 */
final class ExportStartedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public RunModel $run,
    ) {}
}
