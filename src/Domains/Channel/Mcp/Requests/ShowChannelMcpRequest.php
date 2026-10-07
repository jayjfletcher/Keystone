<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Mcp\Requests;

use JayI\Keystone\Domains\Channel\Actions\ShowChannelAction;
use JayI\Keystone\Domains\Channel\Resources\ChannelResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class ShowChannelMcpRequest extends ChannelRequest
{
    protected function authorize(): bool
    {
        return $this->allows('view', $this->channel());
    }

    protected function rules(): array
    {
        return ShowChannelAction::rules() + [
            'channel' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $channel = app(ShowChannelAction::class)->execute($this->channel());

        return Response::structured((new ChannelResource($channel))->resolve());
    }
}
