<?php

declare(strict_types=1);

namespace JayI\Keystone\Atrium\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JayI\Atrium\Http\Controllers\Concerns\AuthorizesScreens;
use JayI\Keystone\Atrium\Support\Labels;
use JayI\Keystone\Domains\Owner\Actions\CreateOwnerAction;
use JayI\Keystone\Domains\Owner\Actions\DeleteOwnerAction;
use JayI\Keystone\Domains\Owner\Actions\ListOwnersAction;
use JayI\Keystone\Domains\Owner\Actions\ShowOwnerAction;
use JayI\Keystone\Domains\Owner\Actions\UpdateOwnerAction;
use JayI\Keystone\Domains\Owner\Models\OwnerModel;
use JayI\Keystone\Domains\Owner\Models\OwnerTypeModel;
use JayI\Keystone\Exceptions\KeystoneException;

final class OwnerUiController
{
    use AuthorizesScreens;

    public function index(Request $request): View
    {
        $this->authorizeScreen('viewAny', OwnerModel::class);

        $filters = $request->validate(ListOwnersAction::rules());

        /** @var view-string $view */
        $view = 'keystone::ui.owners.index';

        return view($view, [
            'owners' => app(ListOwnersAction::class)->execute($filters)->withQueryString(),
            'filters' => $filters,
            'types' => OwnerTypeModel::query()->orderBy('sort_order')->orderBy('code')->pluck('code', 'code')->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeScreen('create', OwnerModel::class);

        $owner = app(CreateOwnerAction::class)->execute(Labels::fromForm($request->validate(CreateOwnerAction::rules())));

        return redirect()
            ->route('atrium.keystone.owners.show', $owner)
            ->with('status', __('keystone::keystone.owner_created'));
    }

    public function show(OwnerModel $owner): View
    {
        $this->authorizeScreen('view', $owner);

        /** @var view-string $view */
        $view = 'keystone::ui.owners.show';

        return view($view, [
            'owner' => app(ShowOwnerAction::class)->execute($owner),
            'types' => OwnerTypeModel::query()->orderBy('code')->pluck('code', 'code')->all(),
        ]);
    }

    public function update(Request $request, OwnerModel $owner): RedirectResponse
    {
        $this->authorizeScreen('update', $owner);

        $data = Labels::fromForm($request->validate(UpdateOwnerAction::rules()), $owner->labels);

        app(UpdateOwnerAction::class)->execute($owner, $data);

        return redirect()
            ->route('atrium.keystone.owners.show', $owner)
            ->with('status', __('keystone::keystone.owner_updated'));
    }

    public function destroy(OwnerModel $owner): RedirectResponse
    {
        $this->authorizeScreen('delete', $owner);

        $parent = $owner->parent;

        try {
            app(DeleteOwnerAction::class)->execute($owner);
        } catch (KeystoneException $e) {
            return back()->withErrors(['owner' => $e->getMessage()]);
        }

        return ($parent !== null
            ? redirect()->route('atrium.keystone.owners.show', $parent)
            : redirect()->route('atrium.keystone.owners.index'))
            ->with('status', __('keystone::keystone.owner_deleted'));
    }
}
