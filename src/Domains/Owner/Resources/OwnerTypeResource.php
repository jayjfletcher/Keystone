<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Owner\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JayI\Keystone\Domains\Owner\Models\OwnerTypeModel;

/**
 * @mixin OwnerTypeModel
 */
final class OwnerTypeResource extends JsonResource
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
            // null: any owner type may be the parent.
            'parent_types' => $this->whenLoaded('parentTypes', fn (): ?array => $this->restricts_parents
                ? $this->parentTypes->pluck('code')->all()
                : null),
            'can_be_root' => $this->can_be_root,
            'owns_products' => $this->owns_products,
            'sort_order' => $this->sort_order,
            'owners_count' => $this->whenCounted('owners'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
