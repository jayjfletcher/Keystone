<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use RefactorCircus\Showroom\Domains\Transfer\Http\Controllers\ImpexController;

// Impex runs: 202 with the run; follow it through Impex's own API.
Route::post('imports', [ImpexController::class, 'import'])->name('imports.store');
Route::post('exports', [ImpexController::class, 'export'])->name('exports.store');
