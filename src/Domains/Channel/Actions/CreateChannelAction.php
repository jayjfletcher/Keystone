<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Channel\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use JayI\Keystone\Domains\Channel\Concerns\WritesChannels;
use JayI\Keystone\Domains\Channel\Events\ChannelCreatedActionEvent;
use JayI\Keystone\Domains\Channel\Events\ChannelCreatingActionEvent;
use JayI\Keystone\Domains\Channel\Models\ChannelModel;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Search\Services\ProductIndex;

final class CreateChannelAction
{
    use WritesChannels;

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/', 'unique:keystone_channels,code'],
        ] + self::channelRules(required: true);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function execute(array $data): ChannelModel
    {
        ChannelCreatingActionEvent::dispatch($data);

        $result = $this->perform($data);

        ChannelCreatedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(array $data): ChannelModel
    {
        return DB::transaction(function () use ($data): ChannelModel {
            $channel = new ChannelModel(['code' => $data['code'], 'labels' => [], 'currencies' => []]);

            $this->writeChannel($channel, $data);

            // Every product's completeness depends on the channels.
            app(ProductIndex::class)->queueQuery(ProductModel::query());

            return $channel->load(['locales', 'categoryTree']);
        });
    }
}
