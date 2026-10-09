<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Channel\Actions;

use RefactorCircus\Showroom\Domains\Channel\Events\ChannelDeletedActionEvent;
use RefactorCircus\Showroom\Domains\Channel\Events\ChannelDeletingActionEvent;
use RefactorCircus\Showroom\Domains\Channel\Models\ChannelModel;
use RefactorCircus\Showroom\Jobs\PurgeValueSlots;

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
