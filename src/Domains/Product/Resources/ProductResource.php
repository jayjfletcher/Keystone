<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Product\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RefactorCircus\Keystone\Domains\Asset\Resources\LinkedAssets;
use RefactorCircus\Keystone\Domains\Association\Services\Associations;
use RefactorCircus\Keystone\Domains\Attribute\Services\Values;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Domains\Workflow\Models\CompletenessModel;

/**
 * @mixin ProductModel
 */
final class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $associations = $this->relationLoaded('associations') ? app(Associations::class)->present($this->resource) : null;

        return [
            'id' => $this->id,
            'identifier' => $this->identifier,
            'family' => $this->whenLoaded('family', fn (): ?string => $this->family?->code),
            'parent' => $this->whenLoaded('parent', fn (): ?string => $this->parent?->code),
            // A variant's owner is its root model's.
            'owner' => $this->resource->effectiveOwner()?->code,
            // Own and inherited, as category codes.
            'categories' => $this->resource->allCategories()->pluck('code')->all(),
            'assets' => $this->when($this->relationLoaded('assets'), fn (): array => LinkedAssets::present(
                $this->assets,
                $this->resource->parent?->allAssets() ?? [],
            )),
            'enabled' => $this->enabled,
            'status' => $this->status->value,
            // The version storefronts read, or null when unpublished.
            'published_version' => $this->published_version,
            'published_at' => $this->published_at?->toIso8601String(),
            'completeness' => $this->whenLoaded('completeness', fn (): array => $this->completeness
                ->map(fn (CompletenessModel $score): array => [
                    'scope' => $score->channel->code,
                    'locale' => $score->locale->code,
                    'required' => $score->required,
                    'missing' => $score->missing,
                    'ratio' => $score->ratio,
                    'missing_attributes' => $score->missing_attributes ?? [],
                ])
                ->sortBy(fn (array $score): string => $score['scope'].'/'.$score['locale'])
                ->values()
                ->all()),
            // Own values plus everything inherited from product models.
            'values' => (object) Values::toStandard($this->resource->presentedValues()),
            // Own and inherited from product models.
            'associations' => $this->when($associations !== null, fn (): array => $associations['associations'] ?? []),
            'quantified_associations' => $this->when($associations !== null, fn (): array => $associations['quantified_associations'] ?? []),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            // When anything the product shows last changed, inherited
            // changes included. What `updated_since` filters on.
            'changed_at' => $this->changed_at?->toIso8601String(),
        ];
    }
}
