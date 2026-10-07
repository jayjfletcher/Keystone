<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Keystone\Domains\Channel\Actions\DeleteChannelAction;

final class DeleteChannelRequest extends ChannelRequest
{
    public function authorize(): bool
    {
        return $this->allows('delete', $this->channel());
    }

    public function rules(): array
    {
        return DeleteChannelAction::rules();
    }

    public function persist(): JsonResponse
    {
        app(DeleteChannelAction::class)->execute($this->channel());

        return new JsonResponse(null, 204);
    }
}
