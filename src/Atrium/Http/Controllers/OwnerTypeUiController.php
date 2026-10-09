<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Atrium\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RefactorCircus\Atrium\Http\Controllers\Concerns\AuthorizesScreens;
use RefactorCircus\Keystone\Atrium\Support\Labels;
use RefactorCircus\Keystone\Domains\Owner\Actions\CreateOwnerTypeAction;
use RefactorCircus\Keystone\Domains\Owner\Actions\DeleteOwnerTypeAction;
use RefactorCircus\Keystone\Domains\Owner\Actions\ListOwnerTypesAction;
use RefactorCircus\Keystone\Domains\Owner\Actions\ShowOwnerTypeAction;
use RefactorCircus\Keystone\Domains\Owner\Actions\UpdateOwnerTypeAction;
use RefactorCircus\Keystone\Domains\Owner\Models\OwnerTypeModel;
use RefactorCircus\Keystone\Exceptions\KeystoneException;

final class OwnerTypeUiController
{
    use AuthorizesScreens;

    public function index(Request $request): View
    {
        $this->authorizeScreen('viewAny', OwnerTypeModel::class);

        /** @var view-string $view */
        $view = 'keystone::ui.owner-types.index';

        return view($view, [
            'types' => app(ListOwnerTypesAction::class)->execute($request->validate(ListOwnerTypesAction::rules()))->withQueryString(),
            'all' => OwnerTypeModel::query()->orderBy('code')->pluck('code')->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeScreen('create', OwnerTypeModel::class);

        $type = app(CreateOwnerTypeAction::class)->execute(
            Labels::fromForm(Validator::validate($this->fromForm($request), CreateOwnerTypeAction::rules())),
        );

        return redirect()
            ->route('atrium.keystone.owner-types.show', $type)
            ->with('status', __('keystone::keystone.owner_type_created'));
    }

    public function show(OwnerTypeModel $ownerType): View
    {
        $this->authorizeScreen('view', $ownerType);

        /** @var view-string $view */
        $view = 'keystone::ui.owner-types.show';

        return view($view, [
            'type' => app(ShowOwnerTypeAction::class)->execute($ownerType),
            'all' => OwnerTypeModel::query()->orderBy('code')->pluck('code')->all(),
        ]);
    }

    public function update(Request $request, OwnerTypeModel $ownerType): RedirectResponse
    {
        $this->authorizeScreen('update', $ownerType);

        app(UpdateOwnerTypeAction::class)->execute(
            $ownerType,
            Labels::fromForm(Validator::validate($this->fromForm($request), UpdateOwnerTypeAction::rules()), $ownerType->labels),
        );

        return redirect()
            ->route('atrium.keystone.owner-types.show', $ownerType)
            ->with('status', __('keystone::keystone.owner_type_updated'));
    }

    public function destroy(OwnerTypeModel $ownerType): RedirectResponse
    {
        $this->authorizeScreen('delete', $ownerType);

        try {
            app(DeleteOwnerTypeAction::class)->execute($ownerType);
        } catch (KeystoneException $e) {
            return back()->withErrors(['owner_type' => $e->getMessage()]);
        }

        return redirect()
            ->route('atrium.keystone.owner-types.index')
            ->with('status', __('keystone::keystone.owner_type_deleted'));
    }

    /**
     * The form's "any parent" switch stands for `parent_types: null`.
     *
     * @return array<string, mixed>
     */
    private function fromForm(Request $request): array
    {
        $input = $request->except(['_token', '_method', 'any_parent']);

        $input['parent_types'] = $request->boolean('any_parent')
            ? null
            : array_values(array_filter((array) $request->input('parent_types', [])));

        return $input;
    }
}
