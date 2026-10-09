<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Channel\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RefactorCircus\Keystone\Domains\Channel\Models\LocaleModel;

/**
 * @mixin LocaleModel
 */
final class LocaleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'labels' => $this->labels ?? [],
            'channels' => $this->whenLoaded('channels', fn (): array => $this->channels->pluck('code')->all()),
            'channels_count' => $this->whenCounted('channels'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
