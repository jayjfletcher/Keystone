<?php

declare(strict_types=1);

namespace JayI\Keystone\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;
use JayI\Keystone\Domains\Search\Services\ProductIndex;

/**
 * Remove a deleted attribute's values — or a deleted option's — from every
 * product and product model that stores them.
 */
final class PurgeAttributeValues implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public string $attribute,
        public ?string $option = null,
    ) {}

    public function handle(ProductIndex $index): void
    {
        $affected = [];

        ProductModel::query()->whereNotNull('values->'.$this->attribute)->chunkById(200, function (Collection $products) use (&$affected): void {
            foreach ($products as $product) {
                if ($this->purge($product)) {
                    $affected[] = $product->id;
                }
            }
        });

        ProductModelModel::query()->whereNotNull('values->'.$this->attribute)->chunkById(200, function (Collection $models) use (&$affected, $index): void {
            foreach ($models as $model) {
                if ($this->purge($model)) {
                    $affected = [...$affected, ...$index->productIdsUnder($model)];
                }
            }
        });

        $index->queue($affected);
    }

    /**
     * Whether anything was removed.
     */
    private function purge(ProductModel|ProductModelModel $record): bool
    {
        $values = $record->ownValues();

        if (! isset($values[$this->attribute])) {
            return false;
        }

        if ($this->option === null) {
            unset($values[$this->attribute]);
        } else {
            foreach ($values[$this->attribute] as $channel => $locales) {
                foreach ($locales as $locale => $data) {
                    $kept = is_array($data)
                        ? array_values(array_filter($data, fn (mixed $code): bool => $code !== $this->option))
                        : ($data === $this->option ? null : $data);

                    if ($kept === null || $kept === []) {
                        unset($values[$this->attribute][$channel][$locale]);
                    } else {
                        $values[$this->attribute][$channel][$locale] = $kept;
                    }
                }

                if ($values[$this->attribute][$channel] === []) {
                    unset($values[$this->attribute][$channel]);
                }
            }

            if ($values[$this->attribute] === []) {
                unset($values[$this->attribute]);
            }
        }

        if ($values === $record->ownValues()) {
            return false;
        }

        $record->values = $values;
        $record->save();

        return true;
    }
}
