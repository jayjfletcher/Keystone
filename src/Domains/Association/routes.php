<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use RefactorCircus\Keystone\Domains\Association\Http\Controllers\AssociationTypeController;

Route::get('association-types', [AssociationTypeController::class, 'index'])->name('association-types.index');
Route::post('association-types', [AssociationTypeController::class, 'store'])->name('association-types.store');
Route::get('association-types/{associationType:code}', [AssociationTypeController::class, 'show'])->name('association-types.show');
Route::patch('association-types/{associationType:code}', [AssociationTypeController::class, 'update'])->name('association-types.update');
Route::delete('association-types/{associationType:code}', [AssociationTypeController::class, 'destroy'])->name('association-types.destroy');
