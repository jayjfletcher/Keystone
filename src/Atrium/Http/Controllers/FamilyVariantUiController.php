<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Atrium\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RefactorCircus\Atrium\Http\Controllers\Concerns\AuthorizesScreens;
use RefactorCircus\Keystone\Atrium\Support\Labels;
use RefactorCircus\Keystone\Domains\Family\Actions\CreateFamilyVariantAction;
use RefactorCircus\Keystone\Domains\Family\Actions\DeleteFamilyVariantAction;
use RefactorCircus\Keystone\Domains\Family\Actions\ShowFamilyVariantAction;
use RefactorCircus\Keystone\Domains\Family\Models\FamilyVariantModel;
use RefactorCircus\Keystone\Exceptions\KeystoneException;

final class FamilyVariantUiController
{
    use AuthorizesScreens;

    /**
     * The form posts two fixed levels; an empty second level means one.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeScreen('create', FamilyVariantModel::class);

        $input = $request->all();

        /** @var array<int, array<string, mixed>> $levels */
        $levels = is_array($input['levels'] ?? null) ? $input['levels'] : [];

        $input['levels'] = array_values(array_filter(
            array_map(fn (array $level): array => [
                'axes' => array_values(array_filter((array) ($level['axes'] ?? []))),
                'attributes' => array_values(array_filter((array) ($level['attributes'] ?? []))),
            ], $levels),
            fn (array $level): bool => $level['axes'] !== [] || $level['attributes'] !== [],
        ));

        $variant = app(CreateFamilyVariantAction::class)->execute(
            Labels::fromForm(Validator::validate($input, CreateFamilyVariantAction::rules())),
        );

        return redirect()
            ->route('atrium.keystone.family-variants.show', $variant)
            ->with('status', __('keystone::keystone.family_variant_created'));
    }

    public function show(FamilyVariantModel $familyVariant): View
    {
        $this->authorizeScreen('view', $familyVariant);

        /** @var view-string $view */
        $view = 'keystone::ui.family-variants.show';

        return view($view, [
            'variant' => app(ShowFamilyVariantAction::class)->execute($familyVariant),
            'models' => $familyVariant->productModels()->whereNull('parent_id')->orderBy('code')->limit(50)->get(),
        ]);
    }

    public function destroy(FamilyVariantModel $familyVariant): RedirectResponse
    {
        $this->authorizeScreen('delete', $familyVariant);

        $family = $familyVariant->family;

        try {
            app(DeleteFamilyVariantAction::class)->execute($familyVariant);
        } catch (KeystoneException $e) {
            return back()->withErrors(['family_variant' => $e->getMessage()]);
        }

        return redirect()
            ->route('atrium.keystone.families.show', $family)
            ->with('status', __('keystone::keystone.family_variant_deleted'));
    }
}
