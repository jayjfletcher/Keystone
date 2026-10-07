<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use JayI\Keystone\Domains\Workflow\Http\Controllers\ProductWorkflowController;

Route::post('products/{product:identifier}/transitions', [ProductWorkflowController::class, 'transition'])->name('products.transitions.store');
Route::get('products/{product:identifier}/versions', [ProductWorkflowController::class, 'versions'])->name('products.versions.index');
// A number, `latest`, or `published` for the version storefronts read.
Route::get('products/{product:identifier}/versions/{version}', [ProductWorkflowController::class, 'version'])->name('products.versions.show');
Route::post('products/{product:identifier}/revert', [ProductWorkflowController::class, 'revert'])->name('products.revert');
