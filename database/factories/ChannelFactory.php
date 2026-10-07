<?php

declare(strict_types=1);

namespace JayI\Keystone\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JayI\Keystone\Domains\Channel\Models\ChannelModel;

/**
 * A channel without locales. Build complete channels with CreateChannelAction.
 *
 * @extends Factory<ChannelModel>
 */
final class ChannelFactory extends Factory
{
    protected $model = ChannelModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'channel_'.fake()->unique()->lexify('??????'),
            'labels' => [],
            'currencies' => ['USD'],
        ];
    }
}
