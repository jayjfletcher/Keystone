<?php

declare(strict_types=1);

namespace JayI\Keystone\Atrium\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JayI\Atrium\Http\Controllers\Concerns\AuthorizesScreens;
use JayI\Keystone\Atrium\Support\Labels;
use JayI\Keystone\Domains\Attribute\Actions\CreateAttributeGroupAction;
use JayI\Keystone\Domains\Attribute\Actions\DeleteAttributeGroupAction;
use JayI\Keystone\Domains\Attribute\Actions\ListAttributeGroupsAction;
use JayI\Keystone\Domains\Attribute\Actions\ShowAttributeGroupAction;
use JayI\Keystone\Domains\Attribute\Actions\UpdateAttributeGroupAction;
use JayI\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use JayI\Keystone\Exceptions\KeystoneException;

final class AttributeGroupUiController
{
    use AuthorizesScreens;

    public function index(Request $request): View
    {
        $this->authorizeScreen('viewAny', AttributeGroupModel::class);

        // Filters are validated by the Action's own rules, so the page and the
        // JSON API accept exactly the same query.
        $filters = $request->validate(ListAttributeGroupsAction::rules());

        /** @var view-string $view */
        $view = 'keystone::ui.attribute-groups.index';

        return view($view, [
            'groups' => app(ListAttributeGroupsAction::class)->execute($filters)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeScreen('create', AttributeGroupModel::class);

        $data = Labels::fromForm($request->validate(CreateAttributeGroupAction::rules()));

        $group = app(CreateAttributeGroupAction::class)->execute($data);

        return redirect()
            ->route('atrium.keystone.attribute-groups.show', $group)
            ->with('status', __('keystone::keystone.group_created'));
    }

    public function show(AttributeGroupModel $group): View
    {
        $this->authorizeScreen('view', $group);

        /** @var view-string $view */
        $view = 'keystone::ui.attribute-groups.show';

        return view($view, [
            'group' => app(ShowAttributeGroupAction::class)->execute($group),
        ]);
    }

    public function update(Request $request, AttributeGroupModel $group): RedirectResponse
    {
        $this->authorizeScreen('update', $group);

        $data = Labels::fromForm($request->validate(UpdateAttributeGroupAction::rules()), $group->labels);

        app(UpdateAttributeGroupAction::class)->execute($group, $data);

        return redirect()
            ->route('atrium.keystone.attribute-groups.show', $group)
            ->with('status', __('keystone::keystone.group_updated'));
    }

    public function destroy(AttributeGroupModel $group): RedirectResponse
    {
        $this->authorizeScreen('delete', $group);

        try {
            app(DeleteAttributeGroupAction::class)->execute($group);
        } catch (KeystoneException $e) {
            return back()->withErrors(['group' => $e->getMessage()]);
        }

        return redirect()
            ->route('atrium.keystone.attribute-groups.index')
            ->with('status', __('keystone::keystone.group_deleted'));
    }
}
