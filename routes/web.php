<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\DeploymentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/katalog', CatalogController::class)->name('catalog.index');
Route::get('/katalog/{object:slug}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/aktualnosci', [NewsController::class, 'index'])->name('news.index');
Route::get('/aktualnosci/{slug}', [NewsController::class, 'show'])->name('news.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('deployment', [DeploymentController::class, 'index'])->name('deployment.index');
    Route::post('deployment', [DeploymentController::class, 'store'])
        ->middleware(['throttle:6,1', RequirePassword::class])
        ->name('deployment.store');
});

require __DIR__.'/settings.php';
