<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Channel\Actions\CreateChannelAction;
use JayI\Keystone\Domains\Channel\Models\ChannelModel;
use JayI\Keystone\Domains\Channel\Resources\ChannelResource;

final class StoreChannelRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('create', ChannelModel::class);
    }

    public function rules(): array
    {
        return CreateChannelAction::rules();
    }

    public function persist(): JsonResponse
    {
        $channel = app(CreateChannelAction::class)->execute($this->validated());

        return (new ChannelResource($channel))->response()->setStatusCode(201);
    }
}
