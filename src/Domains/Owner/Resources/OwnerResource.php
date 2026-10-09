<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Owner\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RefactorCircus\Keystone\Domains\Asset\Resources\LinkedAssets;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerModel;

/**
 * @mixin OwnerModel
 */
final class OwnerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->whenLoaded('type', fn (): string => $this->type->code),
            'labels' => $this->labels ?? [],
            'parent' => $this->whenLoaded('parent', fn (): ?string => $this->parent?->code),
            'depth' => $this->depth,
            'assets' => $this->when($this->relationLoaded('assets'), fn (): array => LinkedAssets::present($this->assets)),
            // Root first, this owner last.
            'chain' => $this->whenLoaded('chain', fn (): array => $this->resource->getRelation('chain')->pluck('code')->all()),
            'children' => $this->whenLoaded('children', fn (): array => $this->children->pluck('code')->all()),
            'children_count' => $this->whenCounted('children'),
            'products_count' => $this->whenCounted('products'),
            'product_models_count' => $this->whenCounted('productModels'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
