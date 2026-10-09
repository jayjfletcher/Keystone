<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Transfer\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Impex\Domains\Run\Models\RunModel;
use RefactorCircus\Keystone\Contracts\ActionFinishedEvent;

/**
 * A product import started.
 */
final class ImportStartedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public RunModel $run,
    ) {}
}
