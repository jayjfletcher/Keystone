<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Mcp\Requests;

use JayI\Keystone\Domains\Channel\Actions\DeleteChannelAction;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class DeleteChannelMcpRequest extends ChannelRequest
{
    protected function authorize(): bool
    {
        return $this->allows('delete', $this->channel());
    }

    protected function rules(): array
    {
        return DeleteChannelAction::rules() + [
            'channel' => ['required', 'string', 'max:100'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $channel = app(DeleteChannelAction::class)->execute($this->channel());

        return Response::structured(['deleted' => true, 'code' => $channel->code]);
    }
}
