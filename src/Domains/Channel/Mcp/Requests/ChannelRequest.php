<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Channel\Models\ChannelModel;

abstract class ChannelRequest extends Request
{
    protected function channel(): ChannelModel
    {
        return ChannelModel::query()->where('code', $this->get('channel'))->firstOrFail();
    }
}
