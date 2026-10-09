<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use RefactorCircus\Showroom\Domains\Product\Http\Controllers\ProductController;

Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::post('products', [ProductController::class, 'store'])->name('products.store');
Route::get('products/{product:identifier}', [ProductController::class, 'show'])->name('products.show');
Route::patch('products/{product:identifier}', [ProductController::class, 'update'])->name('products.update');
Route::delete('products/{product:identifier}', [ProductController::class, 'destroy'])->name('products.destroy');
