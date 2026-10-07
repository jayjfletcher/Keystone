<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use JayI\Keystone\Domains\Channel\Concerns\WritesChannels;
use JayI\Keystone\Domains\Channel\Events\ChannelUpdatedActionEvent;
use JayI\Keystone\Domains\Channel\Events\ChannelUpdatingActionEvent;
use JayI\Keystone\Domains\Channel\Models\ChannelModel;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Search\Services\ProductIndex;

final class UpdateChannelAction
{
    use WritesChannels;

    /**
     * Values already written in a locale the channel drops stay stored.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            // Scopable values are keyed by channel code; it never changes.
            'code' => ['prohibited'],
        ] + self::channelRules(required: false);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(ChannelModel $channel, array $data): ChannelModel
    {
        ChannelUpdatingActionEvent::dispatch($channel, $data);

        $result = $this->perform($channel, $data);

        ChannelUpdatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(ChannelModel $channel, array $data): ChannelModel
    {
        return DB::transaction(function () use ($channel, $data): ChannelModel {
            $this->writeChannel($channel, $data);

            // Every product's completeness depends on the channels.
            app(ProductIndex::class)->queueQuery(ProductModel::query());

            return $channel->load(['locales', 'categoryTree']);
        });
    }
}
