<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Channel\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Channel\Actions\CreateChannelAction;
use RefactorCircus\Keystone\Domains\Channel\Models\ChannelModel;
use RefactorCircus\Keystone\Domains\Channel\Resources\ChannelResource;

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
