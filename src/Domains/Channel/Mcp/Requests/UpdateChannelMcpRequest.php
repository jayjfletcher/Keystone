<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Channel\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Domains\Channel\Actions\UpdateChannelAction;
use RefactorCircus\Keystone\Domains\Channel\Resources\ChannelResource;

final class UpdateChannelMcpRequest extends ChannelRequest
{
    protected function authorize(): bool
    {
        return $this->allows('update', $this->channel());
    }

    protected function rules(): array
    {
        return UpdateChannelAction::rules() + [
            'channel' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        unset($validated['channel']);

        $channel = app(UpdateChannelAction::class)->execute($this->channel(), $validated);

        return Response::structured((new ChannelResource($channel))->resolve());
    }
}
