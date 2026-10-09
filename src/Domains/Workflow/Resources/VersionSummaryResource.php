<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Workflow\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RefactorCircus\Showroom\Domains\Workflow\Models\VersionModel;

/**
 * One entry of a product's history: what happened, who did it, what changed.
 *
 * @mixin VersionModel
 */
final class VersionSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'version' => $this->version,
            'action' => $this->action,
            'comment' => $this->comment,
            'author' => $this->author_type === null ? null : ['type' => $this->author_type, 'id' => $this->author_id],
            'changes' => $this->changes ?? [],
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
