<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Channel\Http\Requests;

use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Channel\Models\ChannelModel;

abstract class ChannelRequest extends Request
{
    protected function channel(): ChannelModel
    {
        $channel = $this->route('channel');

        if (! $channel instanceof ChannelModel) {
            abort(404);
        }

        return $channel;
    }
}
