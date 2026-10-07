<?php

declare(strict_types=1);

namespace JayI\Keystone\Atrium\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use JayI\Atrium\Http\Controllers\Concerns\AuthorizesScreens;
use JayI\Keystone\Atrium\Support\Labels;
use JayI\Keystone\Domains\Category\Models\CategoryModel;
use JayI\Keystone\Domains\Channel\Actions\CreateChannelAction;
use JayI\Keystone\Domains\Channel\Actions\CreateLocaleAction;
use JayI\Keystone\Domains\Channel\Actions\DeleteChannelAction;
use JayI\Keystone\Domains\Channel\Actions\DeleteLocaleAction;
use JayI\Keystone\Domains\Channel\Actions\ListChannelsAction;
use JayI\Keystone\Domains\Channel\Actions\ListLocalesAction;
use JayI\Keystone\Domains\Channel\Actions\ShowChannelAction;
use JayI\Keystone\Domains\Channel\Actions\UpdateChannelAction;
use JayI\Keystone\Domains\Channel\Models\ChannelModel;
use JayI\Keystone\Domains\Channel\Models\LocaleModel;
use JayI\Keystone\Exceptions\KeystoneException;

/**
 * Channels and the locales they publish in, managed on one page.
 */
final class ChannelUiController
{
    use AuthorizesScreens;

    public function index(): View
    {
        $this->authorizeScreen('viewAny', ChannelModel::class);

        /** @var view-string $view */
        $view = 'keystone::ui.channels.index';

        return view($view, [
            'channels' => app(ListChannelsAction::class)->execute(['per_page' => 100]),
            'locales' => app(ListLocalesAction::class)->execute(['per_page' => 100]),
            'allLocales' => LocaleModel::query()->orderBy('code')->pluck('code')->all(),
            'trees' => $this->trees(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeScreen('create', ChannelModel::class);

        $channel = app(CreateChannelAction::class)->execute(
            Labels::fromForm(Validator::validate($this->fromForm($request), CreateChannelAction::rules())),
        );

        return redirect()
            ->route('atrium.keystone.channels.show', $channel)
            ->with('status', __('keystone::keystone.channel_created'));
    }

    public function show(ChannelModel $channel): View
    {
        $this->authorizeScreen('view', $channel);

        /** @var view-string $view */
        $view = 'keystone::ui.channels.show';

        return view($view, [
            'channel' => app(ShowChannelAction::class)->execute($channel),
            'allLocales' => LocaleModel::query()->orderBy('code')->pluck('code')->all(),
            'trees' => $this->trees(),
        ]);
    }

    public function update(Request $request, ChannelModel $channel): RedirectResponse
    {
        $this->authorizeScreen('update', $channel);

        app(UpdateChannelAction::class)->execute(
            $channel,
            Labels::fromForm(Validator::validate($this->fromForm($request), UpdateChannelAction::rules()), $channel->labels),
        );

        return redirect()
            ->route('atrium.keystone.channels.show', $channel)
            ->with('status', __('keystone::keystone.channel_updated'));
    }

    public function destroy(ChannelModel $channel): RedirectResponse
    {
        $this->authorizeScreen('delete', $channel);

        app(DeleteChannelAction::class)->execute($channel);

        return redirect()
            ->route('atrium.keystone.channels.index')
            ->with('status', __('keystone::keystone.channel_deleted'));
    }

    public function storeLocale(Request $request): RedirectResponse
    {
        $this->authorizeScreen('create', LocaleModel::class);

        app(CreateLocaleAction::class)->execute(Labels::fromForm($request->validate(CreateLocaleAction::rules())));

        return redirect()
            ->route('atrium.keystone.channels.index')
            ->with('status', __('keystone::keystone.locale_created'));
    }

    public function destroyLocale(LocaleModel $locale): RedirectResponse
    {
        $this->authorizeScreen('delete', $locale);

        try {
            app(DeleteLocaleAction::class)->execute($locale);
        } catch (KeystoneException $e) {
            return back()->withErrors(['locale' => $e->getMessage()]);
        }

        return redirect()
            ->route('atrium.keystone.channels.index')
            ->with('status', __('keystone::keystone.locale_deleted'));
    }

    /**
     * Currencies arrive as one comma-separated field; an empty tree select
     * means none.
     *
     * @return array<string, mixed>
     */
    private function fromForm(Request $request): array
    {
        $input = $request->except(['_token', '_method']);

        if (is_string($input['currencies'] ?? null)) {
            $input['currencies'] = array_values(array_filter(array_map(
                fn (string $code): string => strtoupper(trim($code)),
                explode(',', $input['currencies']),
            )));
        }

        if (array_key_exists('locales', $input)) {
            $input['locales'] = array_values(array_filter((array) $input['locales']));
        }

        if (($input['category_tree'] ?? null) === '') {
            $input['category_tree'] = null;
        }

        return $input;
    }

    /**
     * @return array<string, string>
     */
    private function trees(): array
    {
        return CategoryModel::query()
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get()
            ->mapWithKeys(fn (CategoryModel $tree): array => [$tree->code => $tree->label()])
            ->all();
    }
}
