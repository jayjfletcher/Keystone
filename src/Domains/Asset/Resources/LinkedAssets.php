<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Asset\Resources;

use Illuminate\Database\Eloquent\Model;
use RefactorCircus\Keystone\Domains\Asset\Models\AssetModel;

/**
 * The assets of a product, product model or owner, as its resource shows them.
 */
final class LinkedAssets
{
    /**
     * @param  iterable<int, AssetModel>  $own
     * @param  iterable<int, AssetModel>  $inherited
     * @return array<int, array{code: string, role: mixed, sort_order: int, url: string, mime_type: string|null, inherited: bool}>
     */
    public static function present(iterable $own, iterable $inherited = []): array
    {
        $assets = [];

        foreach ([[$own, false], [$inherited, true]] as [$list, $isInherited]) {
            foreach ($list as $asset) {
                $pivot = $asset->getRelation('pivot');

                $assets[] = [
                    'code' => $asset->code,
                    'role' => $pivot instanceof Model ? $pivot->getAttribute('role') : null,
                    'sort_order' => $pivot instanceof Model ? (int) $pivot->getAttribute('sort_order') : 0,
                    'url' => $asset->url(),
                    'mime_type' => $asset->mime_type,
                    'inherited' => $isInherited,
                ];
            }
        }

        return $assets;
    }
}
