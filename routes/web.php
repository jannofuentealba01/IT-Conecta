<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\CarbonCalculatorController;
use App\Http\Controllers\RoomController;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS Y DE ACCESO A SALAS
|--------------------------------------------------------------------------
*/

// Pantalla principal (Ingreso de código de sala)
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Flujo para unirse a salas tipo Kahoot
Route::post('/join-room', [RoomController::class, 'join'])->name('room.join');
Route::get('/room/{code}', [RoomController::class, 'showJoinForm'])->name('room.form');
Route::post('/room/{code}/enter', [RoomController::class, 'enter'])->name('room.enter');

// Salir de la sala actual (limpiar sesión)
Route::get('/exit-room', function () {
    session()->forget(['participant_id', 'participant_name', 'participant_course', 'room_id', 'room_code']);
    return redirect()->route('home')->with('success', 'Has salido de la sala.');
})->name('room.exit');

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS PARA ESTUDIANTES (MIDDLEWARE PARTICIPANT)
|--------------------------------------------------------------------------
*/
Route::middleware(['participant'])->group(function () {

    // Dashboard principal del estudiante
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Calculadora de Huella de Carbono
    Route::get('/calcular-huella', [CarbonCalculatorController::class, 'showForm'])->name('carbon.form');
    Route::post('/calcular-huella', [CarbonCalculatorController::class, 'calculate'])->name('carbon.calculate');

    // Actividades
    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
    Route::get('/activities/{id}', [ActivityController::class, 'show'])->name('activities.show');
    Route::post('/activities/{id}/do', [ActivityController::class, 'do'])->name('activities.do');
    Route::post('/activities/{activity}/complete', [RankingController::class, 'complete'])->name('activities.complete');
    Route::get('/activities/{id}/qr', [ActivityController::class, 'scan'])->name('activities.qr');
    Route::get('/activities/{id}/qr-view', [ActivityController::class, 'showQr'])->name('activities.qr.show');

    // Ranking escolar
    Route::get('/ranking', [RankingController::class, 'ranking'])->name('ranking');

});

/*
|--------------------------------------------------------------------------
| RUTAS SOLO PARA ADMINISTRACION / PROFESORES (MIDDLEWARE AUTH + ADMIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->group(function () {

    // Gestión de Actividades
    Route::get('/activities/create', [ActivityController::class, 'create'])->name('activities.create');
    Route::post('/activities', [ActivityController::class, 'store'])->name('activities.store');
    Route::get('/activities/{activity}/edit', [ActivityController::class, 'edit'])->name('activities.edit');
    Route::put('/activities/{activity}', [ActivityController::class, 'update'])->name('activities.update');
    Route::delete('/activities/{activity}', [ActivityController::class, 'destroy'])->name('activities.destroy');

    // Gestión de Perfil Administrador
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

/*
|--------------------------------------------------------------------------
| RUTAS DE AUTENTICACION TRADICIONAL (BREEZE / SANCTUM)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';