<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use RefactorCircus\Showroom\Domains\Category\Http\Controllers\CategoryController;

Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
Route::get('categories/{category:code}', [CategoryController::class, 'show'])->name('categories.show');
Route::patch('categories/{category:code}', [CategoryController::class, 'update'])->name('categories.update');
Route::delete('categories/{category:code}', [CategoryController::class, 'destroy'])->name('categories.destroy');
