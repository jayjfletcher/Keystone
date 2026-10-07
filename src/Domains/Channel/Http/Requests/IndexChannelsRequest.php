<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\Channel\Actions\ListChannelsAction;
use JayI\Keystone\Domains\Channel\Models\ChannelModel;
use JayI\Keystone\Domains\Channel\Resources\ChannelResource;

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
