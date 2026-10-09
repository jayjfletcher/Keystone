<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Association\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RefactorCircus\Showroom\Domains\Association\Models\AssociationTypeModel;

/**
 * @mixin AssociationTypeModel
 */
final class AssociationTypeResource extends JsonResource
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
            'is_two_way' => $this->is_two_way,
            'is_quantified' => $this->is_quantified,
            'associations_count' => $this->whenCounted('associations'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
