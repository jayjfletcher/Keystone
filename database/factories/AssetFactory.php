<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use RefactorCircus\Keystone\Domains\Asset\Models\AssetModel;

/**
 * An asset record. The file itself is not written; use CreateAssetAction.
 *
 * @extends Factory<AssetModel>
 */
final class AssetFactory extends Factory
{
    protected $model = AssetModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = 'asset-'.fake()->unique()->lexify('??????');

        return [
            'code' => $code,
            'labels' => [],
            'disk' => (string) (config('keystone.media.disk') ?? config('filesystems.default')),
            'path' => 'keystone/assets/'.$code.'.jpg',
            'filename' => $code.'.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'checksum' => hash('sha256', $code),
        ];
    }
}
