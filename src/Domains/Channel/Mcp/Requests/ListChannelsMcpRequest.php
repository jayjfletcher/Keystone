<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Channel\Mcp\Requests;

use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Keystone\Mcp\Requests\Request;
use RefactorCircus\Showroom\Domains\Channel\Actions\ListChannelsAction;
use RefactorCircus\Showroom\Domains\Channel\Models\ChannelModel;
use RefactorCircus\Showroom\Domains\Channel\Resources\ChannelResource;

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
