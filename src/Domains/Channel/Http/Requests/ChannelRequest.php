<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Channel\Http\Requests;

use RefactorCircus\Keystone\Http\Requests\Request;
use RefactorCircus\Showroom\Domains\Channel\Models\ChannelModel;

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
