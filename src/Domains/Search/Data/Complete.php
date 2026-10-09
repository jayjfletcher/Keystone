<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Search\Data;

use RefactorCircus\Showroom\Domains\Channel\Models\ChannelModel;

/**
 * A completeness condition: at least `min` percent on a channel — in one
 * locale, or in every locale the channel publishes in.
 */
final readonly class Complete
{
    public function __construct(
        public string $scope,
        public ?string $locale = null,
        public int $min = 100,
    ) {}

    /**
     * @param  array<string, mixed>  $input
     */
    public static function fromArray(array $input): self
    {
        return new self(
            scope: is_string($input['scope'] ?? null) ? $input['scope'] : '',
            locale: is_string($input['locale'] ?? null) && $input['locale'] !== '' ? $input['locale'] : null,
            min: isset($input['min']) ? (int) $input['min'] : 100,
        );
    }

    /**
     * The locales the condition covers.
     *
     * @return array<int, string>
     */
    public function locales(): array
    {
        if ($this->locale !== null) {
            return [$this->locale];
        }

        $channel = ChannelModel::query()->with('locales')->where('code', $this->scope)->first();

        return $channel?->locales->pluck('code')->all() ?? [];
    }
}
