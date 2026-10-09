<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Channel\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Keystone\Domains\Channel\Actions\ShowChannelAction;
use RefactorCircus\Keystone\Domains\Channel\Resources\ChannelResource;

final class ShowChannelRequest extends ChannelRequest
{
    public function authorize(): bool
    {
        return $this->allows('view', $this->channel());
    }

    public function rules(): array
    {
        return ShowChannelAction::rules();
    }

    public function persist(): JsonResponse
    {
        $channel = app(ShowChannelAction::class)->execute($this->channel());

        return (new ChannelResource($channel))->response();
    }
}
