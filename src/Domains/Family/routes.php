<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use JayI\Keystone\Domains\Family\Http\Controllers\FamilyController;
use JayI\Keystone\Domains\Family\Http\Controllers\FamilyVariantController;

Route::get('families', [FamilyController::class, 'index'])->name('families.index');
Route::post('families', [FamilyController::class, 'store'])->name('families.store');
Route::get('families/{family:code}', [FamilyController::class, 'show'])->name('families.show');
Route::patch('families/{family:code}', [FamilyController::class, 'update'])->name('families.update');
Route::delete('families/{family:code}', [FamilyController::class, 'destroy'])->name('families.destroy');

Route::get('family-variants', [FamilyVariantController::class, 'index'])->name('family-variants.index');
Route::post('family-variants', [FamilyVariantController::class, 'store'])->name('family-variants.store');
Route::get('family-variants/{familyVariant:code}', [FamilyVariantController::class, 'show'])->name('family-variants.show');
Route::patch('family-variants/{familyVariant:code}', [FamilyVariantController::class, 'update'])->name('family-variants.update');
Route::delete('family-variants/{familyVariant:code}', [FamilyVariantController::class, 'destroy'])->name('family-variants.destroy');
