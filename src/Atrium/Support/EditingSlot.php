<?php

declare(strict_types=1);

namespace RefactorCircus\Keystone\Atrium\Support;

use Illuminate\Http\Request;
use RefactorCircus\Keystone\Domains\Channel\Models\ChannelModel;
use RefactorCircus\Keystone\Domains\Channel\Models\LocaleModel;

/**
 * Which locale and channel a dashboard value form edits, picked with
 * `?locale=` and `?channel=` and carried through the form as hidden fields.
 */
final readonly class EditingSlot
{
    /**
     * @param  array<string, string>  $locales  Every locale code => label.
     * @param  array<string, string>  $channels  Every channel code => label.
     */
    public function __construct(
        public ?string $locale,
        public ?string $channel,
        public array $locales,
        public array $channels,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $locales = LocaleModel::query()->orderBy('code')->get()->mapWithKeys(fn (LocaleModel $locale): array => [$locale->code => $locale->label()])->all();
        $channels = ChannelModel::query()->orderBy('code')->get()->mapWithKeys(fn (ChannelModel $channel): array => [$channel->code => $channel->label()])->all();

        $locale = $request->input('locale');
        $channel = $request->input('channel');

        return new self(
            locale: is_string($locale) && isset($locales[$locale])
                ? $locale
                : (isset($locales[app()->getLocale()]) ? app()->getLocale() : (array_key_first($locales) ?? null)),
            channel: is_string($channel) && isset($channels[$channel]) ? $channel : (array_key_first($channels) ?? null),
            locales: $locales,
            channels: $channels,
        );
    }

    /**
     * The query string that keeps this slot selected.
     *
     * @return array<string, string>
     */
    public function query(): array
    {
        return array_filter(['locale' => $this->locale, 'channel' => $this->channel], fn (?string $value): bool => $value !== null);
    }
}
