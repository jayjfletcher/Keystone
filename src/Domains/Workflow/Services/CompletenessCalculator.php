<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use JayI\Keystone\Domains\Attribute\Models\AttributeModel;
use JayI\Keystone\Domains\Attribute\Services\Values;
use JayI\Keystone\Domains\Channel\Models\ChannelModel;
use JayI\Keystone\Domains\Product\Models\ProductModel;

/**
 * Scores how much of what a product's family requires it holds, for every
 * channel and each of the channel's locales.
 */
final class CompletenessCalculator
{
    private const string TABLE = 'keystone_product_completeness';

    /**
     * @var Collection<int, ChannelModel>|null
     */
    private ?Collection $channels = null;

    /**
     * Rewrite the stored scores of these products. Each needs `family` and
     * the `parent` chain loaded.
     *
     * @param  iterable<int, ProductModel>  $products
     */
    public function refresh(iterable $products): void
    {
        $this->channels = null;

        foreach ($products as $product) {
            $rows = $this->compute($product);

            DB::table(self::TABLE)->where('product_id', $product->id)->delete();

            if ($rows !== []) {
                DB::table(self::TABLE)->insert($rows);
            }
        }
    }

    /**
     * @return array<int, array{product_id: string, channel_id: string, locale_id: string, required: int, missing: int, ratio: int, missing_attributes: string, updated_at: Carbon}>
     */
    public function compute(ProductModel $product): array
    {
        $family = $product->family;

        if ($family === null) {
            return [];
        }

        $members = $family->familyAttributes()->get()->filter(fn (AttributeModel $attribute): bool => (bool) $this->pivot($attribute, 'is_required'));
        $values = $product->allValues();
        $rows = [];

        foreach ($this->channels() as $channel) {
            $required = $members->filter(function (AttributeModel $attribute) use ($channel): bool {
                $channels = $this->pivot($attribute, 'required_channels');
                $channels = is_string($channels) ? json_decode($channels, true) : $channels;

                return ! is_array($channels) || in_array($channel->code, $channels, true);
            });

            foreach ($channel->locales as $locale) {
                $missing = $required
                    ->reject(fn (AttributeModel $attribute): bool => $this->filled(Values::get(
                        $values,
                        $attribute->code,
                        $attribute->is_scopable ? $channel->code : null,
                        $attribute->is_localizable ? $locale->code : null,
                    )))
                    ->pluck('code')
                    ->values()
                    ->all();

                $total = $required->count();

                $rows[] = [
                    'product_id' => $product->id,
                    'channel_id' => $channel->id,
                    'locale_id' => $locale->id,
                    'required' => $total,
                    'missing' => count($missing),
                    // Nothing required counts as complete.
                    'ratio' => $total === 0 ? 100 : intdiv(($total - count($missing)) * 100, $total),
                    'missing_attributes' => json_encode($missing, JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ];
            }
        }

        return $rows;
    }

    private function filled(mixed $data): bool
    {
        return $data !== null && $data !== '' && $data !== [];
    }

    private function pivot(AttributeModel $attribute, string $key): mixed
    {
        $pivot = $attribute->getRelation('pivot');

        return $pivot instanceof Model ? $pivot->getAttribute($key) : null;
    }

    /**
     * @return Collection<int, ChannelModel>
     */
    private function channels(): Collection
    {
        return $this->channels ??= ChannelModel::query()->with('locales')->orderBy('code')->get();
    }
}
