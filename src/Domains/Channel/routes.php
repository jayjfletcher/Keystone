<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use RefactorCircus\Showroom\Domains\Channel\Http\Controllers\ChannelController;
use RefactorCircus\Showroom\Domains\Channel\Http\Controllers\LocaleController;

Route::get('locales', [LocaleController::class, 'index'])->name('locales.index');
Route::post('locales', [LocaleController::class, 'store'])->name('locales.store');
Route::get('locales/{locale:code}', [LocaleController::class, 'show'])->name('locales.show');
Route::patch('locales/{locale:code}', [LocaleController::class, 'update'])->name('locales.update');
Route::delete('locales/{locale:code}', [LocaleController::class, 'destroy'])->name('locales.destroy');

Route::get('channels', [ChannelController::class, 'index'])->name('channels.index');
Route::post('channels', [ChannelController::class, 'store'])->name('channels.store');
Route::get('channels/{channel:code}', [ChannelController::class, 'show'])->name('channels.show');
Route::patch('channels/{channel:code}', [ChannelController::class, 'update'])->name('channels.update');
Route::delete('channels/{channel:code}', [ChannelController::class, 'destroy'])->name('channels.destroy');
