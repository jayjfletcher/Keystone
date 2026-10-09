<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Atrium\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RefactorCircus\Atrium\Http\Controllers\Concerns\AuthorizesScreens;
use RefactorCircus\Keystone\Atrium\Support\Labels;
use RefactorCircus\Keystone\Domains\Category\Actions\CreateCategoryAction;
use RefactorCircus\Keystone\Domains\Category\Actions\DeleteCategoryAction;
use RefactorCircus\Keystone\Domains\Category\Actions\ListCategoriesAction;
use RefactorCircus\Keystone\Domains\Category\Actions\ShowCategoryAction;
use RefactorCircus\Keystone\Domains\Category\Actions\UpdateCategoryAction;
use RefactorCircus\Keystone\Domains\Category\Models\CategoryModel;
use RefactorCircus\Keystone\Exceptions\KeystoneException;

final class CategoryUiController
{
    use AuthorizesScreens;

    public function index(Request $request): View
    {
        $this->authorizeScreen('viewAny', CategoryModel::class);

        /** @var view-string $view */
        $view = 'keystone::ui.categories.index';

        return view($view, [
            'trees' => app(ListCategoriesAction::class)->execute(['roots' => true] + $request->validate(ListCategoriesAction::rules()))->withQueryString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeScreen('create', CategoryModel::class);

        $category = app(CreateCategoryAction::class)->execute(Labels::fromForm($request->validate(CreateCategoryAction::rules())));

        return redirect()
            ->route('atrium.keystone.categories.show', $category->parent ?? $category)
            ->with('status', __('keystone::keystone.category_created'));
    }

    public function show(CategoryModel $category): View
    {
        $this->authorizeScreen('view', $category);

        $category = app(ShowCategoryAction::class)->execute($category);

        /** @var view-string $view */
        $view = 'keystone::ui.categories.show';

        return view($view, [
            'category' => $category,
            // The whole branch beneath, grouped by parent for a nested list.
            'branch' => CategoryModel::query()
                ->subtreeOf($category)
                ->whereKeyNot($category->getKey())
                ->orderBy('sort_order')
                ->orderBy('code')
                ->get()
                ->groupBy('parent_id'),
        ]);
    }

    public function update(Request $request, CategoryModel $category): RedirectResponse
    {
        $this->authorizeScreen('update', $category);

        $data = Labels::fromForm($request->validate(UpdateCategoryAction::rules()), $category->labels);

        app(UpdateCategoryAction::class)->execute($category, $data);

        return redirect()
            ->route('atrium.keystone.categories.show', $category)
            ->with('status', __('keystone::keystone.category_updated'));
    }

    public function destroy(CategoryModel $category): RedirectResponse
    {
        $this->authorizeScreen('delete', $category);

        $parent = $category->parent;

        try {
            app(DeleteCategoryAction::class)->execute($category);
        } catch (KeystoneException $e) {
            return back()->withErrors(['category' => $e->getMessage()]);
        }

        return ($parent !== null
            ? redirect()->route('atrium.keystone.categories.show', $parent)
            : redirect()->route('atrium.keystone.categories.index'))
            ->with('status', __('keystone::keystone.category_deleted'));
    }
}
