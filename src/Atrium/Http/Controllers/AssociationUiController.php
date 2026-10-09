<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Atrium\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use RefactorCircus\Atrium\Http\Controllers\Concerns\AuthorizesScreens;
use RefactorCircus\Keystone\Atrium\Support\Labels;
use RefactorCircus\Keystone\Domains\Association\Actions\CreateAssociationTypeAction;
use RefactorCircus\Keystone\Domains\Association\Actions\DeleteAssociationTypeAction;
use RefactorCircus\Keystone\Domains\Association\Actions\ListAssociationTypesAction;
use RefactorCircus\Keystone\Domains\Association\Models\AssociationTypeModel;
use RefactorCircus\Keystone\Domains\Product\Actions\UpdateProductAction;
use RefactorCircus\Keystone\Domains\Product\Models\ProductModel;
use RefactorCircus\Keystone\Domains\ProductModel\Actions\UpdateProductModelAction;
use RefactorCircus\Keystone\Domains\ProductModel\Models\ProductModelModel;
use RefactorCircus\Keystone\Exceptions\KeystoneException;

/**
 * Association types, and adding or removing one association at a time from
 * a product or product model page. The Actions take a type's whole list, so
 * each change rebuilds that list from what the record holds.
 */
final class AssociationUiController
{
    use AuthorizesScreens;

    public function index(Request $request): View
    {
        $this->authorizeScreen('viewAny', AssociationTypeModel::class);

        /** @var view-string $view */
        $view = 'keystone::ui.association-types.index';

        return view($view, [
            'types' => app(ListAssociationTypesAction::class)->execute($request->validate(ListAssociationTypesAction::rules()))->withQueryString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeScreen('create', AssociationTypeModel::class);

        app(CreateAssociationTypeAction::class)->execute(Labels::fromForm($request->validate(CreateAssociationTypeAction::rules())));

        return redirect()
            ->route('atrium.keystone.association-types.index')
            ->with('status', __('keystone::keystone.association_type_created'));
    }

    public function destroy(AssociationTypeModel $associationType): RedirectResponse
    {
        $this->authorizeScreen('delete', $associationType);

        try {
            app(DeleteAssociationTypeAction::class)->execute($associationType);
        } catch (KeystoneException $e) {
            return back()->withErrors(['association_type' => $e->getMessage()]);
        }

        return redirect()
            ->route('atrium.keystone.association-types.index')
            ->with('status', __('keystone::keystone.association_type_deleted'));
    }

    /**
     * Add one target to a record's list for a type.
     */
    public function add(Request $request): RedirectResponse
    {
        $input = $request->validate([
            'source_kind' => ['required', Rule::in(['product', 'product_model'])],
            'source' => ['required', 'string'],
            'type' => ['required', 'string', 'exists:keystone_association_types,code'],
            'target_kind' => ['required', Rule::in(['products', 'product_models'])],
            'target' => ['required', 'string'],
            'quantity' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ]);

        $record = $this->source($input['source_kind'], $input['source']);

        // Associations are saved as an update of the record, as the API asks.
        $this->authorizeScreen('update', $record);

        $type = AssociationTypeModel::query()->where('code', $input['type'])->firstOrFail();

        $this->save($record, $type, $this->change(
            $this->ownList($record, $type),
            (string) $input['target_kind'],
            (string) $input['target'],
            (int) ($input['quantity'] ?? 1),
        ));

        return back()->with('status', __('keystone::keystone.association_added'));
    }

    /**
     * Remove one target from a record's list for a type.
     */
    public function remove(Request $request): RedirectResponse
    {
        $input = $request->validate([
            'source_kind' => ['required', Rule::in(['product', 'product_model'])],
            'source' => ['required', 'string'],
            'type' => ['required', 'string', 'exists:keystone_association_types,code'],
            'target_kind' => ['required', Rule::in(['products', 'product_models'])],
            'target' => ['required', 'string'],
        ]);

        $record = $this->source($input['source_kind'], $input['source']);

        // Associations are saved as an update of the record, as the API asks.
        $this->authorizeScreen('update', $record);

        $type = AssociationTypeModel::query()->where('code', $input['type'])->firstOrFail();

        $this->save($record, $type, $this->change(
            $this->ownList($record, $type),
            (string) $input['target_kind'],
            (string) $input['target'],
            null,
        ));

        return back()->with('status', __('keystone::keystone.association_removed'));
    }

    private function source(string $kind, string $key): ProductModel|ProductModelModel
    {
        return $kind === 'product'
            ? ProductModel::query()->where('identifier', $key)->firstOrFail()
            : ProductModelModel::query()->where('code', $key)->firstOrFail();
    }

    /**
     * The record's own targets for a type: kind => identifier => quantity.
     *
     * @return array{products: array<string, int>, product_models: array<string, int>}
     */
    private function ownList(ProductModel|ProductModelModel $record, AssociationTypeModel $type): array
    {
        $list = ['products' => [], 'product_models' => []];

        $associations = $record->associations()->with('target')->where('association_type_id', $type->id)->get();

        foreach ($associations as $association) {
            $target = $association->target;

            if ($target instanceof ProductModel) {
                $list['products'][$target->identifier] = (int) ($association->quantity ?? 1);
            } elseif ($target instanceof ProductModelModel) {
                $list['product_models'][$target->code] = (int) ($association->quantity ?? 1);
            }
        }

        return $list;
    }

    /**
     * Add a target with a quantity, or remove it when the quantity is null.
     *
     * @param  array{products: array<string, int>, product_models: array<string, int>}  $list
     * @return array{products: array<string, int>, product_models: array<string, int>}
     */
    private function change(array $list, string $kind, string $target, ?int $quantity): array
    {
        $items = $kind === 'products' ? $list['products'] : $list['product_models'];

        if ($quantity === null) {
            unset($items[$target]);
        } else {
            $items[$target] = $quantity;
        }

        return $kind === 'products'
            ? ['products' => $items, 'product_models' => $list['product_models']]
            : ['products' => $list['products'], 'product_models' => $items];
    }

    /**
     * @param  array{products: array<string, int>, product_models: array<string, int>}  $list
     */
    private function save(ProductModel|ProductModelModel $record, AssociationTypeModel $type, array $list): void
    {
        $payload = $type->is_quantified
            ? ['quantified_associations' => [$type->code => array_map(
                fn (array $items): array => array_map(
                    fn (string $identifier, int $quantity): array => ['identifier' => $identifier, 'quantity' => $quantity],
                    array_map('strval', array_keys($items)),
                    array_values($items),
                ),
                $list,
            )]]
            : ['associations' => [$type->code => array_map(fn (array $items): array => array_map('strval', array_keys($items)), $list)]];

        // Built by the page, so held to the Action's rules too.
        $record instanceof ProductModel
            ? app(UpdateProductAction::class)->execute($record, Validator::validate($payload, UpdateProductAction::rules()))
            : app(UpdateProductModelAction::class)->execute($record, Validator::validate($payload, UpdateProductModelAction::rules()));
    }
}
