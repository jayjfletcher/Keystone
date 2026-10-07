<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Foundation\Contracts\ActionStartingEvent;
use JayI\Keystone\Domains\Channel\Models\ChannelModel;

/**
 * A channel is about to be shown.
 */
final class ChannelShowingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ChannelModel $channel,
    ) {}
}
