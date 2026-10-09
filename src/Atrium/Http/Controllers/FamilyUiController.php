<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Atrium\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RefactorCircus\Atrium\Http\Controllers\Concerns\AuthorizesScreens;
use RefactorCircus\Keystone\Atrium\Support\Labels;
use RefactorCircus\Keystone\Domains\Attribute\Enums\AttributeType;
use RefactorCircus\Keystone\Domains\Attribute\Models\AttributeModel;
use RefactorCircus\Keystone\Domains\Family\Actions\CreateFamilyAction;
use RefactorCircus\Keystone\Domains\Family\Actions\DeleteFamilyAction;
use RefactorCircus\Keystone\Domains\Family\Actions\ListFamiliesAction;
use RefactorCircus\Keystone\Domains\Family\Actions\ShowFamilyAction;
use RefactorCircus\Keystone\Domains\Family\Actions\UpdateFamilyAction;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyModel;

final class FamilyUiController
{
    use AuthorizesScreens;

    public function index(Request $request): View
    {
        $this->authorizeScreen('viewAny', FamilyModel::class);

        // Filters are validated by the Action's own rules, so the page and the
        // JSON API accept exactly the same query.
        $filters = $request->validate(ListFamiliesAction::rules());

        /** @var view-string $view */
        $view = 'keystone::ui.families.index';

        return view($view, [
            'families' => app(ListFamiliesAction::class)->execute($filters)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeScreen('create', FamilyModel::class);

        $data = Labels::fromForm($request->validate(CreateFamilyAction::rules()));

        $family = app(CreateFamilyAction::class)->execute($data);

        return redirect()
            ->route('atrium.keystone.families.show', $family)
            ->with('status', __('keystone::keystone.family_created'));
    }

    public function show(FamilyModel $family): View
    {
        $this->authorizeScreen('view', $family);

        $family = app(ShowFamilyAction::class)->execute($family)->load('variants');

        /** @var view-string $view */
        $view = 'keystone::ui.families.show';

        return view($view, [
            'family' => $family,
            'available' => AttributeModel::query()
                ->whereNotIn('id', $family->familyAttributes->modelKeys())
                ->orderBy('code')
                ->get(),
            'labelCandidates' => $family->familyAttributes
                ->filter(fn (AttributeModel $attribute): bool => $attribute->type === AttributeType::Text)
                ->mapWithKeys(fn (AttributeModel $attribute): array => [$attribute->code => $attribute->label()])
                ->all(),
        ]);
    }

    /**
     * The form posts every member row, each with a remove box, plus one
     * attribute to add; it is folded into the whole list the Action expects.
     */
    public function update(Request $request, FamilyModel $family): RedirectResponse
    {
        $this->authorizeScreen('update', $family);

        $input = $request->all();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = is_array($input['attributes'] ?? null) ? $input['attributes'] : [];

        $members = array_values(array_map(
            // Required channels arrive as one comma-separated field.
            function (array $row): array {
                if (array_key_exists('required_channels', $row)) {
                    $channels = array_values(array_filter(array_map('trim', explode(',', (string) $row['required_channels']))));
                    $row['required_channels'] = $channels === [] ? null : $channels;
                }

                return $row;
            },
            array_filter($rows, fn (array $row): bool => ($row['remove'] ?? '0') !== '1'),
        ));

        if (is_string($input['add_attribute'] ?? null) && $input['add_attribute'] !== '') {
            $members[] = ['attribute' => $input['add_attribute'], 'is_required' => $input['add_required'] ?? '0', 'sort_order' => count($members)];
        }

        $input['attributes'] = $members;

        if (($input['label_attribute'] ?? null) === '') {
            $input['label_attribute'] = null;
        }

        $data = Labels::fromForm(Validator::validate($input, UpdateFamilyAction::rules()), $family->labels);

        app(UpdateFamilyAction::class)->execute($family, $data);

        return redirect()
            ->route('atrium.keystone.families.show', $family)
            ->with('status', __('keystone::keystone.family_updated'));
    }

    public function destroy(FamilyModel $family): RedirectResponse
    {
        $this->authorizeScreen('delete', $family);

        app(DeleteFamilyAction::class)->execute($family);

        return redirect()
            ->route('atrium.keystone.families.index')
            ->with('status', __('keystone::keystone.family_deleted'));
    }
}
