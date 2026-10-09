<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Channel\Mcp\Requests;

use RefactorCircus\Keystone\Mcp\Requests\Request;
use RefactorCircus\Showroom\Domains\Channel\Models\ChannelModel;

abstract class ChannelRequest extends Request
{
    protected function channel(): ChannelModel
    {
        return ChannelModel::query()->where('code', $this->get('channel'))->firstOrFail();
    }
}
