<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Channel\Concerns;

use Illuminate\Validation\ValidationException;
use RefactorCircus\Showroom\Domains\Category\Models\CategoryModel;
use RefactorCircus\Showroom\Domains\Channel\Models\ChannelModel;
use RefactorCircus\Showroom\Domains\Channel\Models\LocaleModel;

/**
 * Locales, currencies and category tree, shared by creating and updating a channel.
 */
trait WritesChannels
{
    /**
     * @return array<string, mixed>
     */
    private static function channelRules(bool $required): array
    {
        return [
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['nullable', 'string', 'max:255'],
            'locales' => [$required ? 'required' : 'sometimes', 'array', 'list', 'min:1'],
            'locales.*' => ['string', 'distinct', 'exists:showroom_locales,code'],
            'currencies' => ['sometimes', 'nullable', 'array', 'list'],
            'currencies.*' => ['string', 'distinct', 'regex:/^[A-Z]{3}$/'],
            'category_tree' => ['sometimes', 'nullable', 'string', 'exists:showroom_categories,code'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    private function writeChannel(ChannelModel $channel, array $data): void
    {
        if (array_key_exists('labels', $data)) {
            $channel->labels = is_array($data['labels']) ? $data['labels'] : [];
        }

        if (array_key_exists('currencies', $data)) {
            $channel->currencies = is_array($data['currencies']) ? array_values($data['currencies']) : [];
        }

        if (array_key_exists('category_tree', $data)) {
            $tree = is_string($data['category_tree']) ? CategoryModel::query()->where('code', $data['category_tree'])->firstOrFail() : null;

            if ($tree !== null && $tree->parent_id !== null) {
                throw ValidationException::withMessages([
                    'category_tree' => sprintf('Category "%s" is not the root of a tree.', $tree->code),
                ]);
            }

            $channel->categoryTree()->associate($tree);
        }

        $channel->save();

        if (array_key_exists('locales', $data) && is_array($data['locales'])) {
            $channel->locales()->sync(LocaleModel::query()->whereIn('code', $data['locales'])->pluck('id')->all());
        }
    }
}
