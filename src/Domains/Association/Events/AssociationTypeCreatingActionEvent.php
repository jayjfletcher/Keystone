<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Association\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use RefactorCircus\Keystone\Contracts\ActionStartingEvent;

/**
 * An association type is about to be created.
 */
final class AssociationTypeCreatingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public array $data,
    ) {}
}
