<?php

declare(strict_types=1);

namespace JayI\Keystone\Domains\ProductModel\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Foundation\Http\Requests\Request;
use JayI\Keystone\Domains\ProductModel\Actions\ListProductModelsAction;
use JayI\Keystone\Domains\ProductModel\Models\ProductModelModel;
use JayI\Keystone\Domains\ProductModel\Resources\ProductModelResource;

final class IndexProductModelsRequest extends Request
{
    public function authorize(): bool
    {
        return parent::authorize() && $this->allows('viewAny', ProductModelModel::class);
    }

    public function rules(): array
    {
        return ListProductModelsAction::rules();
    }

    public function persist(): JsonResponse
    {
        $productModels = app(ListProductModelsAction::class)->execute($this->validated());

        return ProductModelResource::collection($productModels)->response();
    }
}
