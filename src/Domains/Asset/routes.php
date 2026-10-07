<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use JayI\Keystone\Domains\Asset\Http\Controllers\AssetController;

Route::get('assets', [AssetController::class, 'index'])->name('assets.index');
Route::post('assets', [AssetController::class, 'store'])->name('assets.store');
Route::get('assets/{asset:code}', [AssetController::class, 'show'])->name('assets.show');
// Replacing the file needs multipart, which PHP only parses on POST:
// send POST with `_method=PATCH`.
Route::patch('assets/{asset:code}', [AssetController::class, 'update'])->name('assets.update');
Route::delete('assets/{asset:code}', [AssetController::class, 'destroy'])->name('assets.destroy');
Route::post('assets/{asset:code}/links', [AssetController::class, 'attach'])->name('assets.links.store');
Route::delete('assets/{asset:code}/links', [AssetController::class, 'detach'])->name('assets.links.destroy');
