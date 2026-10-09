<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Attribute\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;

/**
 * @mixin AttributeModel
 */
final class AttributeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type->value,
            'group' => $this->whenLoaded('group', fn (): ?string => $this->group?->code),
            'labels' => $this->labels ?? [],
            'is_unique' => $this->is_unique,
            'is_localizable' => $this->is_localizable,
            'is_scopable' => $this->is_scopable,
            'settings' => $this->settings ?? [],
            'sort_order' => $this->sort_order,
            'options' => AttributeOptionResource::collection($this->whenLoaded('options')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
