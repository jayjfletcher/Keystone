<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Transfer\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RefactorCircus\Impex\Domains\Run\Models\RunModel;
use RefactorCircus\Showroom\Domains\Asset\Actions\CreateAssetAction;
use RefactorCircus\Showroom\Domains\Asset\Models\AssetModel;
use RefactorCircus\Showroom\Domains\Transfer\Events\ImportStartedActionEvent;
use RefactorCircus\Showroom\Domains\Transfer\Events\ImportStartingActionEvent;
use RefactorCircus\Showroom\Domains\Transfer\Exceptions\ImpexMissingException;
use RefactorCircus\Showroom\Impex\ImpexIntegration;

final class StartImportAction
{
    /**
     * The file: an existing `asset`, or a `file` upload or `url`, which
     * becomes one first. The run is asynchronous; follow it with Impex.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'asset' => ['required_without_all:file,url', 'nullable', 'string', 'exists:showroom_assets,code'],
            'file' => ['sometimes', 'file'],
            'url' => ['sometimes', 'string', 'url:http,https'],
            'format' => ['sometimes', 'nullable', Rule::in(['csv', 'jsonl'])],
            'mode' => ['sometimes', Rule::in(['create', 'update', 'upsert'])],
        ];
    }

    public function __construct(private readonly ImpexIntegration $impex) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ImpexMissingException|ValidationException
     */
    public function execute(array $data): RunModel
    {
        ImportStartingActionEvent::dispatch($data);

        $result = $this->perform($data);

        ImportStartedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(array $data): RunModel
    {
        if (! $this->impex->active()) {
            throw ImpexMissingException::make();
        }

        $asset = match (true) {
            ($data['file'] ?? null) instanceof UploadedFile => app(CreateAssetAction::class)->execute(['file' => $data['file']]),
            is_string($data['url'] ?? null) => app(CreateAssetAction::class)->execute(['url' => $data['url']]),
            default => AssetModel::query()->where('code', $data['asset'])->firstOrFail(),
        };

        $format = is_string($data['format'] ?? null) ? $data['format'] : $this->formatOf($asset);

        return $this->impex->start(ImpexIntegration::IMPORT, [
            $asset->code,
            $format,
            is_string($data['mode'] ?? null) ? $data['mode'] : 'upsert',
        ]);
    }

    /**
     * @throws ValidationException
     */
    private function formatOf(AssetModel $asset): string
    {
        return match (strtolower(pathinfo($asset->filename, PATHINFO_EXTENSION))) {
            'csv' => 'csv',
            'jsonl', 'ndjson' => 'jsonl',
            default => throw ValidationException::withMessages([
                'format' => sprintf('Cannot tell the format of "%s"; send format csv or jsonl.', $asset->filename),
            ]),
        };
    }
}
