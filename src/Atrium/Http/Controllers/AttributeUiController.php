<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Atrium\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RefactorCircus\Atrium\Http\Controllers\Concerns\AuthorizesScreens;
use RefactorCircus\Keystone\Atrium\Support\Labels;
use RefactorCircus\Keystone\Domains\Attribute\Actions\CreateAttributeAction;
use RefactorCircus\Keystone\Domains\Attribute\Actions\CreateAttributeOptionAction;
use RefactorCircus\Keystone\Domains\Attribute\Actions\DeleteAttributeAction;
use RefactorCircus\Keystone\Domains\Attribute\Actions\DeleteAttributeOptionAction;
use RefactorCircus\Keystone\Domains\Attribute\Actions\ListAttributesAction;
use RefactorCircus\Keystone\Domains\Attribute\Actions\ShowAttributeAction;
use RefactorCircus\Keystone\Domains\Attribute\Actions\UpdateAttributeAction;
use RefactorCircus\Keystone\Domains\Attribute\Enums\AttributeType;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeGroupModel;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeOptionModel;
use RefactorCircus\Keystone\Exceptions\KeystoneException;

final class AttributeUiController
{
    use AuthorizesScreens;

    public function index(Request $request): View
    {
        $this->authorizeScreen('viewAny', AttributeModel::class);

        // Filters are validated by the Action's own rules, so the page and the
        // JSON API accept exactly the same query.
        $filters = $request->validate(ListAttributesAction::rules());

        /** @var view-string $view */
        $view = 'keystone::ui.attributes.index';

        return view($view, [
            'catalogAttributes' => app(ListAttributesAction::class)->execute($filters)->withQueryString(),
            'filters' => $filters,
            'types' => AttributeType::cases(),
            'groups' => $this->groups(),
        ]);
    }

    public function create(): View
    {
        $this->authorizeScreen('create', AttributeModel::class);

        /** @var view-string $view */
        $view = 'keystone::ui.attributes.create';

        return view($view, [
            'types' => AttributeType::cases(),
            'groups' => $this->groups(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeScreen('create', AttributeModel::class);

        $data = $this->fromForm($request->validate(['settings' => ['sometimes', 'nullable', 'string']] + CreateAttributeAction::rules()));

        $attribute = app(CreateAttributeAction::class)->execute(Labels::fromForm($data));

        return redirect()
            ->route('atrium.keystone.attributes.show', $attribute)
            ->with('status', __('keystone::keystone.attribute_created'));
    }

    public function show(AttributeModel $attribute): View
    {
        $this->authorizeScreen('view', $attribute);

        /** @var view-string $view */
        $view = 'keystone::ui.attributes.show';

        return view($view, [
            'attribute' => app(ShowAttributeAction::class)->execute($attribute),
            'groups' => $this->groups(),
        ]);
    }

    public function update(Request $request, AttributeModel $attribute): RedirectResponse
    {
        $this->authorizeScreen('update', $attribute);

        $data = $this->fromForm($request->validate(['settings' => ['sometimes', 'nullable', 'string']] + UpdateAttributeAction::rules()));

        app(UpdateAttributeAction::class)->execute($attribute, Labels::fromForm($data, $attribute->labels));

        return redirect()
            ->route('atrium.keystone.attributes.show', $attribute)
            ->with('status', __('keystone::keystone.attribute_updated'));
    }

    public function destroy(AttributeModel $attribute): RedirectResponse
    {
        $this->authorizeScreen('delete', $attribute);

        try {
            app(DeleteAttributeAction::class)->execute($attribute);
        } catch (KeystoneException $e) {
            return back()->withErrors(['attribute' => $e->getMessage()]);
        }

        return redirect()
            ->route('atrium.keystone.attributes.index')
            ->with('status', __('keystone::keystone.attribute_deleted'));
    }

    public function storeOption(Request $request, AttributeModel $attribute): RedirectResponse
    {
        $this->authorizeScreen('update', $attribute);
        $this->authorizeScreen('create', AttributeOptionModel::class);

        $data = Labels::fromForm($request->validate(CreateAttributeOptionAction::rules()));

        try {
            app(CreateAttributeOptionAction::class)->execute($attribute, $data);
        } catch (KeystoneException $e) {
            return back()->withErrors(['option' => $e->getMessage()]);
        }

        return redirect()
            ->route('atrium.keystone.attributes.show', $attribute)
            ->with('status', __('keystone::keystone.option_created'));
    }

    public function destroyOption(AttributeModel $attribute, AttributeOptionModel $option): RedirectResponse
    {
        $this->authorizeScreen('delete', $option);

        app(DeleteAttributeOptionAction::class)->execute($option);

        return redirect()
            ->route('atrium.keystone.attributes.show', $attribute)
            ->with('status', __('keystone::keystone.option_deleted'));
    }

    /**
     * @return array<string, string>
     */
    private function groups(): array
    {
        return AttributeGroupModel::query()
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn (AttributeGroupModel $group): array => [$group->code => $group->label()])
            ->all();
    }

    /**
     * Settings arrive from the form as JSON text; an empty group select as
     * an empty string, meaning no group.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function fromForm(array $data): array
    {
        if (is_string($data['settings'] ?? null)) {
            $decoded = $data['settings'] === '' ? [] : json_decode($data['settings'], true);

            if (! is_array($decoded)) {
                throw ValidationException::withMessages([
                    'settings' => __('keystone::keystone.invalid_settings'),
                ]);
            }

            $data['settings'] = $decoded;
        }

        if (array_key_exists('group', $data) && $data['group'] === '') {
            $data['group'] = null;
        }

        return $data;
    }
}
