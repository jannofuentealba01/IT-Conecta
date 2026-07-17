<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\RankingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Funciones para todos los usuarios autenticados
    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
    Route::get('/activities/{id}', [ActivityController::class, 'show'])->name('activities.show');

    Route::post('/activities/{id}/do', [ActivityController::class, 'do'])
        ->name('activities.do');

    Route::post('/activities/{activity}/complete', [RankingController::class, 'complete'])
        ->name('activities.complete');

    Route::get('/activities/{id}/qr', [ActivityController::class, 'scan'])
        ->name('activities.qr');

    Route::get('/activities/{id}/qr-view', [ActivityController::class, 'showQr'])
        ->name('activities.qr.show');

    Route::get('/ranking', [RankingController::class, 'ranking'])
        ->name('ranking');
});

// Solo administradores
Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/activities/create', [ActivityController::class, 'create'])
        ->name('activities.create');

    Route::post('/activities', [ActivityController::class, 'store'])
        ->name('activities.store');

    Route::get('/activities/{activity}/edit', [ActivityController::class, 'edit'])
        ->name('activities.edit');

    Route::put('/activities/{activity}', [ActivityController::class, 'update'])
        ->name('activities.update');

    Route::delete('/activities/{activity}', [ActivityController::class, 'destroy'])
        ->name('activities.destroy');
});

require __DIR__.'/auth.php';