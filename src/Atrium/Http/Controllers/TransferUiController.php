<?php

declare(strict_types=1);

namespace JayI\Keystone\Atrium\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JayI\Atrium\Http\Controllers\Concerns\AuthorizesScreens;
use JayI\Impex\Domains\Run\Models\RunModel;
use JayI\Impex\Facades\Impex;
use JayI\Keystone\Atrium\ScreenAccess;
use JayI\Keystone\Domains\Category\Models\CategoryModel;
use JayI\Keystone\Domains\Channel\Models\ChannelModel;
use JayI\Keystone\Domains\Family\Models\FamilyModel;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Transfer\Actions\StartExportAction;
use JayI\Keystone\Domains\Transfer\Actions\StartImportAction;
use JayI\Keystone\Impex\ImpexIntegration;

/**
 * Imports and exports: start one, and follow the latest runs.
 */
final class TransferUiController
{
    use AuthorizesScreens;

    public function index(): View
    {
        abort_unless(ScreenAccess::transfers(), 403);

        $active = app(ImpexIntegration::class)->active();

        /** @var view-string $view */
        $view = 'keystone::ui.transfers.index';

        return view($view, [
            'active' => $active,
            'runs' => $active
                ? RunModel::query()->where('flow', 'like', 'keystone:%')->latest('created_at')->limit(20)->get()
                    ->map(fn (RunModel $run): array => ['run' => $run, 'result' => $run->status->value === 'completed' ? Impex::result($run) : null])
                : collect(),
            'families' => FamilyModel::query()->orderBy('code')->pluck('code', 'code')->all(),
            'channels' => ChannelModel::query()->orderBy('code')->pluck('code', 'code')->all(),
            'trees' => CategoryModel::query()->whereNull('parent_id')->orderBy('code')->pluck('code', 'code')->all(),
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorizeScreen('create', ProductModel::class);

        app(StartImportAction::class)->execute($request->validate(StartImportAction::rules()));

        return back()->with('status', __('keystone::keystone.import_started'));
    }

    public function export(Request $request): RedirectResponse
    {
        $this->authorizeScreen('viewAny', ProductModel::class);

        $data = array_filter($request->validate(StartExportAction::rules()), fn (mixed $value): bool => $value !== null && $value !== '');

        app(StartExportAction::class)->execute($data);

        return back()->with('status', __('keystone::keystone.export_started'));
    }
}
