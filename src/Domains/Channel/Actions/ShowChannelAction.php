<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Actions;

use JayI\Keystone\Domains\Channel\Events\ChannelShowingActionEvent;
use JayI\Keystone\Domains\Channel\Events\ChannelShownActionEvent;
use JayI\Keystone\Domains\Channel\Models\ChannelModel;

final class ShowChannelAction
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
        ChannelShowingActionEvent::dispatch($channel);

        $result = $this->perform($channel);

        ChannelShownActionEvent::dispatch($result);

        return $result;
    }

    private function perform(ChannelModel $channel): ChannelModel
    {
        return $channel->load(['locales', 'categoryTree']);
    }
}
