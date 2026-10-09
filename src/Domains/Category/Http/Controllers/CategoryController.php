<?php

declare(strict_types=1);

namespace RefactorCircus\Showroom\Domains\Category\Http\Controllers;

use Illuminate\Http\JsonResponse;
use RefactorCircus\Showroom\Domains\Category\Http\Requests\DeleteCategoryRequest;
use RefactorCircus\Showroom\Domains\Category\Http\Requests\IndexCategoriesRequest;
use RefactorCircus\Showroom\Domains\Category\Http\Requests\ShowCategoryRequest;
use RefactorCircus\Showroom\Domains\Category\Http\Requests\StoreCategoryRequest;
use RefactorCircus\Showroom\Domains\Category\Http\Requests\UpdateCategoryRequest;
use RefactorCircus\Showroom\Domains\Category\Models\CategoryModel;

final class CategoryController
{
    public function index(IndexCategoriesRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function show(ShowCategoryRequest $request, CategoryModel $category): JsonResponse
    {
        return $request->persist();
    }

    public function update(UpdateCategoryRequest $request, CategoryModel $category): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DeleteCategoryRequest $request, CategoryModel $category): JsonResponse
    {
        return $request->persist();
    }
}
