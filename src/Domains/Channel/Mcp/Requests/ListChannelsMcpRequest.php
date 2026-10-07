<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Mcp\Requests;

use JayI\Foundation\Mcp\Requests\Request;
use JayI\Keystone\Domains\Channel\Actions\ListChannelsAction;
use JayI\Keystone\Domains\Channel\Models\ChannelModel;
use JayI\Keystone\Domains\Channel\Resources\ChannelResource;
use Laravel\Mcp\ResponseFactory;

final class ListChannelsMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', ChannelModel::class);
    }

    protected function rules(): array
    {
        return ListChannelsAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $channels = app(ListChannelsAction::class)->execute($validated);

        return $this->structuredCollection(
            ChannelResource::collection($channels)->resolve(),
            ['next_cursor' => $channels->nextCursor()?->encode()],
        );
    }
}
