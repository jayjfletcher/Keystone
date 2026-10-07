<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\Transfer\Actions;

use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use JayI\Impex\Domains\Run\Models\RunModel;
use JayI\Keystone\Domains\Product\Actions\ListProductsAction;
use JayI\Keystone\Domains\Transfer\Events\ExportStartedActionEvent;
use JayI\Keystone\Domains\Transfer\Events\ExportStartingActionEvent;
use JayI\Keystone\Domains\Transfer\Exceptions\ImpexMissingException;
use JayI\Keystone\Impex\ImpexIntegration;

final class StartExportAction
{
    /**
     * Any product search filter narrows the export; `scope` and `locales`
     * narrow the values written. The file becomes an asset — its code is in
     * the run's result.
     *
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return Arr::except(ListProductsAction::rules(), ['sort', 'page', 'per_page', 'facets', 'facets.*']) + [
            'format' => ['sometimes', Rule::in(['jsonl', 'csv'])],
            'code' => ['sometimes', 'nullable', 'string', 'max:191', 'regex:/^[A-Za-z0-9][A-Za-z0-9_.-]*$/', 'unique:keystone_assets,code'],
            // Write each product's live version, and leave out unpublished ones.
            'published' => ['sometimes', 'boolean'],
        ];
    }

    public function __construct(private readonly ImpexIntegration $impex) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ImpexMissingException
     */
    public function execute(array $data): RunModel
    {
        ExportStartingActionEvent::dispatch($data);

        $result = $this->perform($data);

        ExportStartedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function perform(array $data): RunModel
    {
        $query = Arr::except($data, ['format', 'code', 'published']);

        return $this->impex->start(ImpexIntegration::EXPORT, [
            $query,
            is_string($data['format'] ?? null) ? $data['format'] : 'jsonl',
            is_string($data['code'] ?? null) ? $data['code'] : null,
            (bool) ($data['published'] ?? false),
        ]);
    }
}
