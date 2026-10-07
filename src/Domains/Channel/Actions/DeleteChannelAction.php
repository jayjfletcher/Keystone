<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Actions;

use JayI\Keystone\Domains\Channel\Events\ChannelDeletedActionEvent;
use JayI\Keystone\Domains\Channel\Events\ChannelDeletingActionEvent;
use JayI\Keystone\Domains\Channel\Models\ChannelModel;
use JayI\Keystone\Jobs\PurgeValueSlots;

final class DeleteChannelAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(ChannelModel $channel): ChannelModel
    {
        ChannelDeletingActionEvent::dispatch($channel);

        $result = $this->perform($channel);

        ChannelDeletedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * The values scoped to the channel are purged by a queued job.
     */
    private function perform(ChannelModel $channel): ChannelModel
    {
        $channel->delete();

        PurgeValueSlots::dispatch(channel: $channel->code)->afterCommit();

        return $channel;
    }
}
