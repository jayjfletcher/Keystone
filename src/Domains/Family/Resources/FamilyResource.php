<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Family\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RefactorCircus\Showroom\Domains\Family\Models\FamilyModel;

/**
 * @mixin FamilyModel
 */
final class FamilyResource extends JsonResource
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
            'label_attribute' => $this->whenLoaded('labelAttribute', fn (): ?string => $this->labelAttribute?->code),
            'sort_order' => $this->sort_order,
            'attributes_count' => $this->whenCounted('familyAttributes'),
            'attributes' => FamilyAttributeResource::collection($this->whenLoaded('familyAttributes')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
