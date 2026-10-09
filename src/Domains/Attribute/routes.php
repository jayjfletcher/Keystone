<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use RefactorCircus\Keystone\Domains\Attribute\Http\Controllers\AttributeController;
use RefactorCircus\Keystone\Domains\Attribute\Http\Controllers\AttributeGroupController;
use RefactorCircus\Keystone\Domains\Attribute\Http\Controllers\AttributeOptionController;

Route::get('attribute-groups', [AttributeGroupController::class, 'index'])->name('attribute-groups.index');
Route::post('attribute-groups', [AttributeGroupController::class, 'store'])->name('attribute-groups.store');
Route::get('attribute-groups/{group:code}', [AttributeGroupController::class, 'show'])->name('attribute-groups.show');
Route::patch('attribute-groups/{group:code}', [AttributeGroupController::class, 'update'])->name('attribute-groups.update');
Route::delete('attribute-groups/{group:code}', [AttributeGroupController::class, 'destroy'])->name('attribute-groups.destroy');

Route::get('attributes', [AttributeController::class, 'index'])->name('attributes.index');
Route::post('attributes', [AttributeController::class, 'store'])->name('attributes.store');
Route::get('attributes/{attribute:code}', [AttributeController::class, 'show'])->name('attributes.show');
Route::patch('attributes/{attribute:code}', [AttributeController::class, 'update'])->name('attributes.update');
Route::delete('attributes/{attribute:code}', [AttributeController::class, 'destroy'])->name('attributes.destroy');

Route::scopeBindings()->group(function (): void {
    Route::get('attributes/{attribute:code}/options', [AttributeOptionController::class, 'index'])->name('attributes.options.index');
    Route::post('attributes/{attribute:code}/options', [AttributeOptionController::class, 'store'])->name('attributes.options.store');
    Route::patch('attributes/{attribute:code}/options/{option:code}', [AttributeOptionController::class, 'update'])->name('attributes.options.update');
    Route::delete('attributes/{attribute:code}/options/{option:code}', [AttributeOptionController::class, 'destroy'])->name('attributes.options.destroy');
});
