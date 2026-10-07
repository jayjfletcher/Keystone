<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Attribute\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;

/**
 * @mixin AttributeGroupModel
 */
final class AttributeGroupResource extends JsonResource
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
            'sort_order' => $this->sort_order,
            'attributes_count' => $this->whenCounted('groupedAttributes'),
            'attributes' => AttributeResource::collection($this->whenLoaded('groupedAttributes')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
