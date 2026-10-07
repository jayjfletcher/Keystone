<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Category\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JayI\Keystone\Domains\Category\Models\CategoryModel;

/**
 * @mixin CategoryModel
 */
final class CategoryResource extends JsonResource
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
            'parent' => $this->whenLoaded('parent', fn (): ?string => $this->parent?->code),
            'sort_order' => $this->sort_order,
            'depth' => $this->depth,
            // Tree root first, this category last.
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
