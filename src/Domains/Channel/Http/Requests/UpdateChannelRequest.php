<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Channel\Actions\UpdateChannelAction;
use JayI\Keystone\Domains\Channel\Resources\ChannelResource;

final class UpdateChannelRequest extends ChannelRequest
{
    public function authorize(): bool
    {
        return $this->allows('update', $this->channel());
    }

    public function rules(): array
    {
        return UpdateChannelAction::rules();
    }

    public function persist(): JsonResponse
    {
        $channel = app(UpdateChannelAction::class)->execute($this->channel(), $this->validated());

        return (new ChannelResource($channel))->response();
    }
}
