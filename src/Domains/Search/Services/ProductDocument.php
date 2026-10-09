<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Search\Services;

use Illuminate\Support\Collection;
use RefactorCircus\Keystone\Domains\Attribute\Enums\AttributeType;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Keystone\Domains\Category\Models\CategoryModel;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Domains\Workflow\Models\CompletenessModel;

/**
 * The document a search index holds for a product: its identity, its own
 * and inherited values, and one block of text for full-text search.
 *
 * Values keep their storage shape — `values.color.<all_channels>.<all_locales>`
 * — with decimal amounts as numbers so ranges compare numerically.
 */
final class ProductDocument
{
    /**
     * @return array<string, mixed>
     */
    public static function from(ProductModel $product): array
    {
        $values = $product->allValues();

        /** @var array<string, AttributeType> $types */
        $types = AttributeModel::query()
            ->whereIn('code', array_keys($values))
            ->get(['code', 'type'])
            ->mapWithKeys(fn (AttributeModel $attribute): array => [$attribute->code => $attribute->type])
            ->all();

        $text = [$product->identifier];

        foreach ($values as $code => $channels) {
            foreach ($channels as $channel => $locales) {
                foreach ($locales as $locale => $data) {
                    $type = $types[$code] ?? null;

                    $values[$code][$channel][$locale] = self::indexable($type, $data);

                    if (in_array($type, [AttributeType::Text, AttributeType::Textarea, AttributeType::Select], true) && is_string($data)) {
                        $text[] = $data;
                    }
                }
            }
        }

        $owner = $product->effectiveOwner();
        $categories = $product->allCategories();

        return [
            'id' => $product->id,
            'identifier' => $product->identifier,
            'family' => $product->family?->code,
            'parent' => $product->parent?->code,
            'owner' => $owner?->code,
            // The whole chain, so a search for a vendor finds its series' products.
            'owners' => $owner?->chain()->pluck('code')->all() ?? [],
            'categories' => $categories->pluck('code')->all(),
            // Assigned categories and all their ancestors, so a branch finds
            // everything filed anywhere beneath it.
            'category_tree' => $categories
                ->flatMap(fn (CategoryModel $category) => $category->chain()->pluck('code'))
                ->unique()
                ->values()
                ->all(),
            'enabled' => $product->enabled,
            'status' => $product->status->value,
            'published' => $product->published_version !== null,
            // {channel: {locale: ratio}}, so ranges filter on completeness.
            'completeness' => $product->completeness
                ->groupBy(fn (CompletenessModel $score): string => $score->channel->code)
                ->map(fn (Collection $scores): array => $scores->mapWithKeys(fn (CompletenessModel $score): array => [$score->locale->code => $score->ratio])->all())
                ->all(),
            'values' => $values,
            'text' => implode(' ', $text),
            'created_at' => $product->created_at?->toIso8601String(),
            'updated_at' => $product->updated_at?->toIso8601String(),
            'changed_at' => $product->changed_at?->toIso8601String(),
        ];
    }

    private static function indexable(?AttributeType $type, mixed $data): mixed
    {
        return match (true) {
            $type === AttributeType::Decimal && is_numeric($data) => (float) $data,
            $type === AttributeType::Metric && is_array($data) => [
                'amount' => is_numeric($data['amount'] ?? null) ? (float) $data['amount'] : null,
                'unit' => $data['unit'] ?? null,
            ],
            $type === AttributeType::Price && is_array($data) => array_map(
                fn (mixed $price): array => [
                    'amount' => is_array($price) && is_numeric($price['amount'] ?? null) ? (float) $price['amount'] : null,
                    'currency' => is_array($price) ? ($price['currency'] ?? null) : null,
                ],
                $data,
            ),
            default => $data,
        };
    }
}
