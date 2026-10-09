<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Impex\Actions;

use Illuminate\Support\Facades\Validator;
use RefactorCircus\Keystone\Domains\Product\Actions\CreateProductAction;
use RefactorCircus\Keystone\Domains\Product\Actions\UpdateProductAction;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RuntimeException;

/**
 * Writes one imported product through the same Actions as the API, so every
 * rule — validation, uniqueness, variants, versions, completeness, the
 * search index — applies to an import exactly as to a single request.
 */
final class ImportProductRow
{
    /**
     * @param  array{mode?: string, record?: array<string, mixed>}  $item
     * @return array{identifier: string, result: string}
     */
    public function execute(array $item): array
    {
        $record = $item['record'] ?? [];
        $mode = $item['mode'] ?? 'upsert';

        if (array_key_exists('_invalid', $record)) {
            throw new RuntimeException('The line is not a JSON object.');
        }

        $identifier = is_string($record['identifier'] ?? null) ? $record['identifier'] : '';
        $product = $identifier === '' ? null : ProductModel::query()->where('identifier', $identifier)->first();

        if ($product === null) {
            if ($mode === 'update') {
                return ['identifier' => $identifier, 'result' => 'skipped'];
            }

            app(CreateProductAction::class)->execute($this->validate($record, CreateProductAction::rules()));

            return ['identifier' => $identifier, 'result' => 'created'];
        }

        if ($mode === 'create') {
            return ['identifier' => $identifier, 'result' => 'skipped'];
        }

        // Identity is fixed once created; only what may change is sent.
        unset($record['identifier'], $record['parent']);

        if ($product->isVariant()) {
            unset($record['family'], $record['owner']);
        }

        app(UpdateProductAction::class)->execute($product, $this->validate($record, UpdateProductAction::rules()));

        return ['identifier' => $identifier, 'result' => 'updated'];
    }

    /**
     * @param  array<string, mixed>  $record
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    private function validate(array $record, array $rules): array
    {
        $validator = Validator::make($record, $rules);

        // A batch item keeps a message, not a response: name the fields.
        if ($validator->fails()) {
            throw new RuntimeException(collect($validator->errors()->toArray())
                ->map(fn (array $messages, string $field): string => $field.': '.implode(' ', $messages))
                ->implode('; '));
        }

        return $validator->validated();
    }
}
