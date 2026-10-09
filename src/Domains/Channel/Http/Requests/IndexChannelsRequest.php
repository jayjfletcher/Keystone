<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Channel\Http\Requests;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Foundation\Http\Requests\Request;
use RefactorCircus\Keystone\Domains\Channel\Actions\ListChannelsAction;
use RefactorCircus\Keystone\Domains\Channel\Models\ChannelModel;
use RefactorCircus\Keystone\Domains\Channel\Resources\ChannelResource;

final class IndexChannelsRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', ChannelModel::class);
    }

    public function rules(): array
    {
        return ListChannelsAction::rules();
    }

    public function persist(): JsonResponse
    {
        $channels = app(ListChannelsAction::class)->execute($this->validated());

        return ChannelResource::collection($channels)->response();
    }
}
