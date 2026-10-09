<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Family\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;

/**
 * An attribute as a member of a family, with its membership settings.
 *
 * @mixin AttributeModel
 */
final class FamilyAttributeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $pivot = $this->resource->getRelation('pivot');
        $channels = $pivot?->getAttribute('required_channels');

        return [
            'attribute' => $this->code,
            'type' => $this->type->value,
            'group' => $this->whenLoaded('group', fn (): ?string => $this->group?->code),
            'is_required' => (bool) $pivot?->getAttribute('is_required'),
            // Null: required on every channel.
            'required_channels' => is_string($channels) ? json_decode($channels, true) : $channels,
            'sort_order' => (int) $pivot?->getAttribute('sort_order'),
        ];
    }
}
