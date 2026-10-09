<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Channel\Mcp\Requests;

use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use RefactorCircus\Foundation\Mcp\Requests\Request;
use RefactorCircus\Showroom\Domains\Channel\Actions\CreateChannelAction;
use RefactorCircus\Showroom\Domains\Channel\Models\ChannelModel;
use RefactorCircus\Showroom\Domains\Channel\Resources\ChannelResource;

final class CreateChannelMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', ChannelModel::class);
    }

    protected function rules(): array
    {
        return CreateChannelAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $channel = app(CreateChannelAction::class)->execute($validated);

        return Response::structured((new ChannelResource($channel))->resolve());
    }
}
