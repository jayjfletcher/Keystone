<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Asset\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RefactorCircus\Keystone\Domains\Asset\Models\AssetModel;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;

/**
 * @mixin AssetModel
 */
final class AssetResource extends JsonResource
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
            'filename' => $this->filename,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'checksum' => $this->checksum,
            'url' => $this->resource->url(),
            'links' => $this->when(
                $this->relationLoaded('products') && $this->relationLoaded('productModels') && $this->relationLoaded('owners'),
                fn (): array => [
                    ...$this->links('product', $this->products),
                    ...$this->links('product_model', $this->productModels),
                    ...$this->links('owner', $this->owners),
                ],
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * @param  iterable<int, Model>  $records
     * @return array<int, array{type: string, target: mixed, role: mixed, sort_order: mixed}>
     */
    private function links(string $type, iterable $records): array
    {
        $links = [];

        foreach ($records as $record) {
            $pivot = $record->getRelation('pivot');

            $links[] = [
                'type' => $type,
                'target' => $record instanceof ProductModel ? $record->identifier : $record->getAttribute('code'),
                'role' => $pivot instanceof Model ? $pivot->getAttribute('role') : null,
                'sort_order' => $pivot instanceof Model ? (int) $pivot->getAttribute('sort_order') : 0,
            ];
        }

        return $links;
    }
}
