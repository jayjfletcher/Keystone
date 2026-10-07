<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Workflow\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use JayI\Keystone\Domains\Channel\Models\ChannelModel;
use JayI\Keystone\Domains\Channel\Models\LocaleModel;
use JayI\Keystone\Domains\Product\Models\ProductModel;
use JayI\Keystone\Domains\Search\Services\ProductIndex;
use JayI\Keystone\Domains\Workflow\Enums\Transition;
use JayI\Keystone\Domains\Workflow\Events\ProductTransitionedActionEvent;
use JayI\Keystone\Domains\Workflow\Events\ProductTransitioningActionEvent;
use JayI\Keystone\Domains\Workflow\Exceptions\InvalidTransitionException;
use JayI\Keystone\Domains\Workflow\Services\CompletenessCalculator;
use JayI\Keystone\Domains\Workflow\Services\Versions;

final class TransitionProductAction
{
    /**
     * `publish` makes the product's current version the one storefronts
     * read; the working copy stays editable.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'transition' => ['required', 'string', Rule::enum(Transition::class)],
            'comment' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }

    public function __construct(
        private readonly Versions $versions,
        private readonly ProductIndex $index,
        private readonly CompletenessCalculator $completeness,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws InvalidTransitionException
     */
    public function execute(ProductModel $product, array $data): ProductModel
    {
        ProductTransitioningActionEvent::dispatch($product, $data);

        $result = $this->perform($product, $data);

        ProductTransitionedActionEvent::dispatch($result, $data);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(ProductModel $product, array $data): ProductModel
    {
        $transition = Transition::from((string) $data['transition']);
        $comment = is_string($data['comment'] ?? null) ? $data['comment'] : null;
        $from = $transition->startsFrom(config('keystone.workflow.require_approval', true) !== false);

        if (! in_array($product->status, $from, true)) {
            throw InvalidTransitionException::from($product->identifier, $transition, $product->status, $from);
        }

        if ($transition === Transition::Submit && config('keystone.workflow.require_complete', false) === true) {
            $this->checkComplete($product);
        }

        return DB::transaction(function () use ($product, $transition, $comment): ProductModel {
            $product->status = $transition->to() ?? $product->status;

            if ($transition === Transition::Unpublish || $transition === Transition::Archive) {
                $product->published_version = null;
                $product->published_at = null;
            }

            $product->save();

            $version = $this->versions->record($product, $transition->action(), $comment);

            if ($transition === Transition::Publish) {
                $product->published_version = $version->version;
                $product->published_at = now();
                $product->save();
            }

            $this->index->queue([$product->id]);

            return $product;
        });
    }

    /**
     * Every channel and locale must be at 100%, freshly computed.
     *
     * @throws InvalidTransitionException
     */
    private function checkComplete(ProductModel $product): void
    {
        $product->loadMissing(['family', 'parent.parent']);
        $rows = $this->completeness->compute($product);

        $channels = ChannelModel::query()->pluck('code', 'id');
        $locales = LocaleModel::query()->pluck('code', 'id');

        $incomplete = [];

        foreach ($rows as $row) {
            if ($row['ratio'] < 100) {
                $incomplete[] = $channels->get($row['channel_id']).' / '.$locales->get($row['locale_id']);
            }
        }

        if ($rows === [] || $incomplete !== []) {
            throw InvalidTransitionException::incomplete($product->identifier, $incomplete);
        }
    }
}
