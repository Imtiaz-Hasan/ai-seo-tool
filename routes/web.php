<?php

use App\Http\Controllers\ContentPieceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
})->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Content pieces + split-screen editor
    Route::get('/pieces', [ContentPieceController::class, 'index'])->name('pieces.index');
    Route::post('/pieces', [ContentPieceController::class, 'store'])->name('pieces.store');
    Route::get('/pieces/{piece}/edit', [ContentPieceController::class, 'edit'])->name('pieces.edit');
    Route::put('/pieces/{piece}', [ContentPieceController::class, 'update'])->name('pieces.update');
    Route::delete('/pieces/{piece}', [ContentPieceController::class, 'destroy'])->name('pieces.destroy');

    // Live SEO scoring (debounced AJAX from the editor). Cheap, but capped to
    // stop scripted abuse; generous enough for fast typing.
    Route::post('/score', ScoreController::class)->middleware('throttle:240,1')->name('score');

    // AI generation (async; editor polls for the result). Tighter limit because
    // a live provider costs tokens.
    Route::post('/generate', [GenerateController::class, 'store'])->middleware('throttle:20,1')->name('generate.store');
    Route::get('/generate/{generation}', [GenerateController::class, 'show'])->name('generate.show');

    // LLM settings (bring your own key / model)
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
