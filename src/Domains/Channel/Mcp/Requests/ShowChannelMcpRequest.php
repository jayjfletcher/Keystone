<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Channel\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Domains\Channel\Actions\ShowChannelAction;
use RefactorCircus\Keystone\Domains\Channel\Resources\ChannelResource;

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
