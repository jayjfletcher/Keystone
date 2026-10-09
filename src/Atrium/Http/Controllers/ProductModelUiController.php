<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Atrium\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RefactorCircus\Atrium\Http\Controllers\Concerns\AuthorizesScreens;
use RefactorCircus\Keystone\Atrium\Support\CategoryCodes;
use RefactorCircus\Keystone\Atrium\Support\EditingSlot;
use RefactorCircus\Keystone\Atrium\Support\ValueForm;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyVariantModel;
use RefactorCircus\Keystone\Domains\ProductModel\Actions\CreateProductModelAction;
use RefactorCircus\Keystone\Domains\ProductModel\Actions\DeleteProductModelAction;
use RefactorCircus\Keystone\Domains\ProductModel\Actions\ListProductModelsAction;
use RefactorCircus\Keystone\Domains\ProductModel\Actions\ShowProductModelAction;
use RefactorCircus\Keystone\Domains\ProductModel\Actions\UpdateProductModelAction;
use RefactorCircus\Keystone\Domains\ProductModel\Models\ProductModelModel;

final class ProductModelUiController
{
    use AuthorizesScreens;

    public function index(Request $request): View
    {
        $this->authorizeScreen('viewAny', ProductModelModel::class);

        $filters = $request->validate(ListProductModelsAction::rules());

        /** @var view-string $view */
        $view = 'keystone::ui.product-models.index';

        return view($view, [
            'models' => app(ListProductModelsAction::class)->execute($filters + ['roots' => true])->withQueryString(),
            'filters' => $filters,
            'variants' => FamilyVariantModel::query()->orderBy('code')->pluck('code', 'code')->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeScreen('create', ProductModelModel::class);

        $model = app(CreateProductModelAction::class)->execute($request->validate(CreateProductModelAction::rules()));

        return redirect()
            ->route('atrium.keystone.product-models.show', $model)
            ->with('status', __('keystone::keystone.product_model_created'));
    }

    public function show(Request $request, ProductModelModel $productModel): View
    {
        $this->authorizeScreen('view', $productModel);

        $model = app(ShowProductModelAction::class)->execute($productModel);

        /** @var view-string $view */
        $view = 'keystone::ui.product-models.show';

        return view($view, [
            'model' => $model,
            'attributes' => $model->settableAttributes()->load('options'),
            'slot' => EditingSlot::fromRequest($request),
            'inherited' => $model->inheritedValues(),
        ]);
    }

    public function update(Request $request, ProductModelModel $productModel): RedirectResponse
    {
        $this->authorizeScreen('update', $productModel);

        /** @var array<string, mixed> $input */
        $input = (array) $request->input('v', []);

        $slot = EditingSlot::fromRequest($request);
        $data = ['values' => ValueForm::toValues($input, $productModel->settableAttributes(), $slot)];

        if ($request->has('categories')) {
            $data['categories'] = CategoryCodes::fromForm($request->input('categories'));
        }

        if ($productModel->parent_id === null && $request->has('owner')) {
            $data['owner'] = $request->filled('owner') ? $request->string('owner')->toString() : null;
        }

        // The form builds the payload, so it is held to the Action's rules too.
        app(UpdateProductModelAction::class)->execute($productModel, Validator::validate($data, UpdateProductModelAction::rules()));

        return redirect()
            ->route('atrium.keystone.product-models.show', ['productModel' => $productModel, ...$slot->query()])
            ->with('status', __('keystone::keystone.product_model_updated'));
    }

    public function destroy(ProductModelModel $productModel): RedirectResponse
    {
        $this->authorizeScreen('delete', $productModel);

        $parent = $productModel->parent;

        app(DeleteProductModelAction::class)->execute($productModel);

        return $parent !== null
            ? redirect()->route('atrium.keystone.product-models.show', $parent)->with('status', __('keystone::keystone.product_model_deleted'))
            : redirect()->route('atrium.keystone.product-models.index')->with('status', __('keystone::keystone.product_model_deleted'));
    }
}
