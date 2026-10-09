<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use RefactorCircus\Keystone\Domains\Owner\Http\Controllers\OwnerController;
use RefactorCircus\Keystone\Domains\Owner\Http\Controllers\OwnerTypeController;

Route::get('owner-types', [OwnerTypeController::class, 'index'])->name('owner-types.index');
Route::post('owner-types', [OwnerTypeController::class, 'store'])->name('owner-types.store');
Route::get('owner-types/{ownerType:code}', [OwnerTypeController::class, 'show'])->name('owner-types.show');
Route::patch('owner-types/{ownerType:code}', [OwnerTypeController::class, 'update'])->name('owner-types.update');
Route::delete('owner-types/{ownerType:code}', [OwnerTypeController::class, 'destroy'])->name('owner-types.destroy');

Route::get('owners', [OwnerController::class, 'index'])->name('owners.index');
Route::post('owners', [OwnerController::class, 'store'])->name('owners.store');
Route::get('owners/{owner:code}', [OwnerController::class, 'show'])->name('owners.show');
Route::patch('owners/{owner:code}', [OwnerController::class, 'update'])->name('owners.update');
Route::delete('owners/{owner:code}', [OwnerController::class, 'destroy'])->name('owners.destroy');
