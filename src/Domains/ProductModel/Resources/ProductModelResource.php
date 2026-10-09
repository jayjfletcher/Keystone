<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\ProductModel\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RefactorCircus\Keystone\Domains\Asset\Resources\LinkedAssets;
use RefactorCircus\Keystone\Domains\Association\Services\Associations;
use RefactorCircus\Keystone\Domains\Attribute\Services\Values;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Domains\ProductModel\Models\ProductModelModel;

/**
 * @mixin ProductModelModel
 */
final class ProductModelResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $associations = $this->relationLoaded('associations') ? app(Associations::class)->present($this->resource) : null;

        return [
            'id' => $this->id,
            'code' => $this->code,
            'family_variant' => $this->whenLoaded('familyVariant', fn (): string => $this->familyVariant->code),
            'parent' => $this->whenLoaded('parent', fn (): ?string => $this->parent?->code),
            'owner' => $this->resource->rootModel()->owner?->code,
            'categories' => $this->resource->allCategories()->pluck('code')->all(),
            'assets' => $this->when($this->relationLoaded('assets'), fn (): array => LinkedAssets::present(
                $this->assets,
                $this->resource->parent?->allAssets() ?? [],
            )),
            'level' => $this->level(),
            // Own values plus the root model's, for a sub-model.
            'values' => (object) Values::toStandard($this->resource->presentedValues()),
            'children' => $this->whenLoaded('children', fn (): array => $this->children->map(fn (ProductModelModel $child): string => $child->code)->all()),
            'products' => $this->whenLoaded('products', fn (): array => $this->products->map(fn (ProductModel $product): string => $product->identifier)->all()),
            // Own and inherited from product models.
            'associations' => $this->when($associations !== null, fn (): array => $associations['associations'] ?? []),
            'quantified_associations' => $this->when($associations !== null, fn (): array => $associations['quantified_associations'] ?? []),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
