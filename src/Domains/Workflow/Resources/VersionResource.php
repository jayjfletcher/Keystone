<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Workflow\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use RefactorCircus\Keystone\Domains\Workflow\Models\VersionModel;

/**
 * One version of a product, with its full snapshot.
 *
 * @mixin VersionModel
 */
final class VersionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return (new VersionSummaryResource($this->resource))->resolve($request) + [
            'snapshot' => $this->snapshot,
        ];
    }
}
