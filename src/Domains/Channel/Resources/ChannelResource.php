<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Channel\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RefactorCircus\Showroom\Domains\Channel\Models\ChannelModel;

/**
 * @mixin ChannelModel
 */
final class ChannelResource extends JsonResource
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
            'locales' => $this->whenLoaded('locales', fn (): array => $this->locales->pluck('code')->all()),
            'currencies' => $this->currencies ?? [],
            'category_tree' => $this->whenLoaded('categoryTree', fn (): ?string => $this->categoryTree?->code),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
