<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use RefactorCircus\Keystone\Domains\ProductModel\Http\Controllers\ProductModelController;

Route::get('product-models', [ProductModelController::class, 'index'])->name('product-models.index');
Route::post('product-models', [ProductModelController::class, 'store'])->name('product-models.store');
Route::get('product-models/{productModel:code}', [ProductModelController::class, 'show'])->name('product-models.show');
Route::patch('product-models/{productModel:code}', [ProductModelController::class, 'update'])->name('product-models.update');
Route::delete('product-models/{productModel:code}', [ProductModelController::class, 'destroy'])->name('product-models.destroy');
