<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Domains\Association\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use RefactorCircus\Keystone\Domains\Association\Models\AssociationModel;
use RefactorCircus\Keystone\Domains\Association\Models\AssociationTypeModel;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Domains\ProductModel\Models\ProductModelModel;

/**
 * Writes and reads the associations of products and product models.
 *
 * The API shape, per type code:
 *
 *     associations:            {"cross_sell": {"products": ["SKU-2"], "product_models": ["tee"]}}
 *     quantified_associations: {"bundle": {"products": [{"identifier": "SKU-3", "quantity": 2}]}}
 */
final class Associations
{
    private const array KINDS = ['products', 'product_models'];

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'associations' => ['sometimes', 'array'],
            'associations.*' => ['array'],
            'associations.*.products' => ['sometimes', 'array', 'list'],
            'associations.*.products.*' => ['string', 'distinct'],
            'associations.*.product_models' => ['sometimes', 'array', 'list'],
            'associations.*.product_models.*' => ['string', 'distinct'],
            'quantified_associations' => ['sometimes', 'array'],
            'quantified_associations.*' => ['array'],
            'quantified_associations.*.products' => ['sometimes', 'array', 'list'],
            'quantified_associations.*.products.*.identifier' => ['required', 'string', 'distinct'],
            'quantified_associations.*.products.*.quantity' => ['required', 'integer', 'min:1'],
            'quantified_associations.*.product_models' => ['sometimes', 'array', 'list'],
            'quantified_associations.*.product_models.*.identifier' => ['required', 'string', 'distinct'],
            'quantified_associations.*.product_models.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Replace the targets of every type the data names; other types stay.
     *
     * @param  array<string, mixed>  $data  Validated against rules().
     *
     * @throws ValidationException
     */
    public function write(ProductModel|ProductModelModel $source, array $data): void
    {
        foreach (['associations' => false, 'quantified_associations' => true] as $key => $quantified) {
            if (! is_array($data[$key] ?? null)) {
                continue;
            }

            foreach ($data[$key] as $code => $groups) {
                $type = $this->type((string) $code, $key, $quantified);
                $targets = $this->targets($source, $key.'.'.$code, is_array($groups) ? $groups : [], $quantified);

                $this->replace($source, $type, $targets);
            }
        }
    }

    /**
     * A record's associations in API shape, merged with those it inherits
     * from its product models unless `$inherit` is off. Needs
     * `associations.type` and `associations.target` loaded, and the same on
     * each parent.
     *
     * @return array{associations: array<string, array{products: array<int, string>, product_models: array<int, string>}>, quantified_associations: array<string, array{products: array<int, array{identifier: string, quantity: int}>, product_models: array<int, array{identifier: string, quantity: int}>}>}
     */
    public function present(ProductModel|ProductModelModel $record, bool $inherit = true): array
    {
        $plain = [];
        $quantified = [];

        // Ancestors first, so the record's own quantities win.
        foreach (array_reverse($inherit ? $this->lineage($record) : [$record]) as $node) {
            foreach ($node->associations as $association) {
                $target = $association->target;

                if (! $target instanceof ProductModel && ! $target instanceof ProductModelModel) {
                    continue;
                }

                $code = $association->type->code;
                $kind = $target instanceof ProductModel ? 'products' : 'product_models';
                $identifier = $target instanceof ProductModel ? $target->identifier : $target->code;

                if ($association->type->is_quantified) {
                    $quantified[$code] ??= ['products' => [], 'product_models' => []];
                    $quantified[$code][$kind][$identifier] = ['identifier' => $identifier, 'quantity' => (int) $association->quantity];
                } else {
                    $plain[$code] ??= ['products' => [], 'product_models' => []];
                    $plain[$code][$kind][$identifier] = $identifier;
                }
            }
        }

        ksort($plain);
        ksort($quantified);

        return [
            'associations' => array_map(fn (array $groups): array => array_map('array_values', $groups), $plain),
            'quantified_associations' => array_map(fn (array $groups): array => array_map('array_values', $groups), $quantified),
        ];
    }

    /**
     * Relations to eager load before present(), for a record up to two
     * model levels deep.
     *
     * @return array<int, string>
     */
    public static function eagerLoads(): array
    {
        return [
            'associations.type', 'associations.target',
            'parent.associations.type', 'parent.associations.target',
            'parent.parent.associations.type', 'parent.parent.associations.target',
        ];
    }

    /**
     * Remove every association from or to records of one kind.
     *
     * @param  class-string<Model>  $class
     * @param  array<int, string>  $ids
     */
    public function forget(string $class, array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $morph = (new $class)->getMorphClass();

        AssociationModel::query()
            ->where(fn (Builder $query): Builder => $query->where('source_type', $morph)->whereIn('source_id', $ids))
            ->orWhere(fn (Builder $query): Builder => $query->where('target_type', $morph)->whereIn('target_id', $ids))
            ->delete();
    }

    /**
     * @throws ValidationException
     */
    private function type(string $code, string $key, bool $quantified): AssociationTypeModel
    {
        $type = AssociationTypeModel::query()->where('code', $code)->first();

        if ($type === null) {
            throw ValidationException::withMessages([$key.'.'.$code => sprintf('Association type "%s" does not exist.', $code)]);
        }

        if ($type->is_quantified !== $quantified) {
            throw ValidationException::withMessages([$key.'.'.$code => $quantified
                ? sprintf('Association type "%s" is not quantified; send it under associations.', $code)
                : sprintf('Association type "%s" is quantified; send it under quantified_associations with quantities.', $code)]);
        }

        return $type;
    }

    /**
     * Resolve the targets of one type, in the order sent.
     *
     * @param  array<string, mixed>  $groups
     * @return array<int, array{model: ProductModel|ProductModelModel, quantity: int|null}>
     *
     * @throws ValidationException
     */
    private function targets(ProductModel|ProductModelModel $source, string $path, array $groups, bool $quantified): array
    {
        $targets = [];
        $errors = [];

        foreach (self::KINDS as $kind) {
            foreach (array_values((array) ($groups[$kind] ?? [])) as $index => $item) {
                $identifier = $quantified ? (is_array($item) ? (string) ($item['identifier'] ?? '') : '') : (string) $item;
                $quantity = $quantified && is_array($item) ? (int) ($item['quantity'] ?? 1) : null;

                $model = $kind === 'products'
                    ? ProductModel::query()->where('identifier', $identifier)->first()
                    : ProductModelModel::query()->where('code', $identifier)->first();

                $key = sprintf('%s.%s.%d', $path, $kind, $index);

                if ($model === null) {
                    $errors[$key] = sprintf('No %s "%s" exists.', $kind === 'products' ? 'product' : 'product model', $identifier);
                } elseif ($model->is($source)) {
                    $errors[$key] = 'A record cannot be associated with itself.';
                } else {
                    $targets[] = ['model' => $model, 'quantity' => $quantity];
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $targets;
    }

    /**
     * @param  array<int, array{model: ProductModel|ProductModelModel, quantity: int|null}>  $targets
     */
    private function replace(ProductModel|ProductModelModel $source, AssociationTypeModel $type, array $targets): void
    {
        $existing = $this->outgoing($source, $type);
        $kept = [];

        foreach ($targets as $position => ['model' => $target, 'quantity' => $quantity]) {
            $key = $target->getMorphClass().':'.$target->getKey();
            $kept[$key] = true;

            $this->link($type, $source, $target, $quantity, $position);

            if ($type->is_two_way) {
                $this->link($type, $target, $source, null, null);
            }
        }

        foreach ($existing as $association) {
            if (isset($kept[$association->target_type.':'.$association->target_id])) {
                continue;
            }

            $association->delete();

            if ($type->is_two_way) {
                AssociationModel::query()
                    ->where('association_type_id', $type->id)
                    ->where('source_type', $association->target_type)
                    ->where('source_id', $association->target_id)
                    ->where('target_type', $source->getMorphClass())
                    ->where('target_id', $source->getKey())
                    ->delete();
            }
        }
    }

    /**
     * Create or update one association; a null position keeps an existing
     * one's order (the mirror side of a two-way pair).
     */
    private function link(AssociationTypeModel $type, Model $source, Model $target, ?int $quantity, ?int $position): void
    {
        $association = AssociationModel::query()->firstOrNew([
            'association_type_id' => $type->id,
            'source_type' => $source->getMorphClass(),
            'source_id' => $source->getKey(),
            'target_type' => $target->getMorphClass(),
            'target_id' => $target->getKey(),
        ]);

        $association->quantity = $quantity;

        if ($position !== null || ! $association->exists) {
            $association->sort_order = $position ?? (int) AssociationModel::query()
                ->where('association_type_id', $type->id)
                ->where('source_type', $source->getMorphClass())
                ->where('source_id', $source->getKey())
                ->max('sort_order') + 1;
        }

        $association->save();
    }

    /**
     * @return Collection<int, AssociationModel>
     */
    private function outgoing(Model $source, AssociationTypeModel $type): Collection
    {
        return AssociationModel::query()
            ->where('association_type_id', $type->id)
            ->where('source_type', $source->getMorphClass())
            ->where('source_id', $source->getKey())
            ->get();
    }

    /**
     * The record and its ancestor models, nearest first.
     *
     * @return array<int, ProductModel|ProductModelModel>
     */
    private function lineage(ProductModel|ProductModelModel $record): array
    {
        $lineage = [$record];
        $parent = $record->parent;

        while ($parent instanceof ProductModelModel) {
            $lineage[] = $parent;
            $parent = $parent->parent;
        }

        return $lineage;
    }
}
