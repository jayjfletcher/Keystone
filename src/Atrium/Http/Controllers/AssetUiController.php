<?php

declare(strict_types=1);

namespace JayI\Keystone\Atrium\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JayI\Atrium\Http\Controllers\Concerns\AuthorizesScreens;
use JayI\Keystone\Atrium\Support\Labels;
use JayI\Keystone\Domains\Asset\Actions\AttachAssetAction;
use JayI\Keystone\Domains\Asset\Actions\CreateAssetAction;
use JayI\Keystone\Domains\Asset\Actions\DeleteAssetAction;
use JayI\Keystone\Domains\Asset\Actions\DetachAssetAction;
use JayI\Keystone\Domains\Asset\Actions\ListAssetsAction;
use JayI\Keystone\Domains\Asset\Actions\ShowAssetAction;
use JayI\Keystone\Domains\Asset\Actions\UpdateAssetAction;
use JayI\Keystone\Domains\Asset\Models\AssetModel;
use JayI\Keystone\Domains\Asset\Services\AssetLinks;

final class AssetUiController
{
    use AuthorizesScreens;

    public function index(Request $request): View
    {
        $this->authorizeScreen('viewAny', AssetModel::class);

        $filters = $request->validate(ListAssetsAction::rules());

        /** @var view-string $view */
        $view = 'keystone::ui.assets.index';

        return view($view, [
            'assets' => app(ListAssetsAction::class)->execute($filters)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeScreen('create', AssetModel::class);

        $asset = app(CreateAssetAction::class)->execute(Labels::fromForm($request->validate(CreateAssetAction::rules())));

        return redirect()
            ->route('atrium.keystone.assets.show', $asset)
            ->with('status', __('keystone::keystone.asset_created'));
    }

    /**
     * Upload a file and link it to the record whose page it came from, in
     * one step.
     */
    public function upload(Request $request): RedirectResponse
    {
        $this->authorizeScreen('create', AssetModel::class);

        $link = $request->validate(AttachAssetAction::rules());
        $source = $request->validate(CreateAssetAction::rules());

        DB::transaction(function () use ($source, $link): void {
            $asset = app(CreateAssetAction::class)->execute($source);

            // Linking is an update of the new asset, as the API's attach
            // call asks; refusing it rolls the upload back.
            $this->authorizeScreen('update', $asset);

            app(AttachAssetAction::class)->execute($asset, $link);
        });

        return back()->with('status', __('keystone::keystone.asset_attached'));
    }

    public function show(AssetModel $asset): View
    {
        $this->authorizeScreen('view', $asset);

        /** @var view-string $view */
        $view = 'keystone::ui.assets.show';

        return view($view, [
            'asset' => app(ShowAssetAction::class)->execute($asset),
            'types' => array_combine(array_keys(AssetLinks::TYPES), array_keys(AssetLinks::TYPES)),
        ]);
    }

    public function update(Request $request, AssetModel $asset): RedirectResponse
    {
        $this->authorizeScreen('update', $asset);

        app(UpdateAssetAction::class)->execute($asset, Labels::fromForm($request->validate(UpdateAssetAction::rules()), $asset->labels));

        return redirect()
            ->route('atrium.keystone.assets.show', $asset)
            ->with('status', __('keystone::keystone.asset_updated'));
    }

    public function destroy(AssetModel $asset): RedirectResponse
    {
        $this->authorizeScreen('delete', $asset);

        app(DeleteAssetAction::class)->execute($asset);

        return redirect()
            ->route('atrium.keystone.assets.index')
            ->with('status', __('keystone::keystone.asset_deleted'));
    }

    public function attach(Request $request, AssetModel $asset): RedirectResponse
    {
        $this->authorizeScreen('update', $asset);

        app(AttachAssetAction::class)->execute($asset, $request->validate(AttachAssetAction::rules()));

        return back()->with('status', __('keystone::keystone.asset_attached'));
    }

    public function detach(Request $request, AssetModel $asset): RedirectResponse
    {
        $this->authorizeScreen('update', $asset);

        app(DetachAssetAction::class)->execute($asset, $request->validate(DetachAssetAction::rules()));

        return back()->with('status', __('keystone::keystone.asset_detached'));
    }
}
