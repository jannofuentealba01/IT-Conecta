<?php

use App\Http\Controllers\CarbonCalculatorController;
use App\Http\Controllers\AdminTeacherApprovalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\StudentMissionController;
use App\Http\Controllers\StudentImpostorController;
use App\Http\Controllers\StudentSessionController;
use App\Http\Controllers\TeacherActivityController;
use App\Http\Controllers\TeacherCourseController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\TeacherMissionController;
use App\Http\Controllers\TeacherImpostorController;
use App\Http\Controllers\TeacherReportController;
use App\Http\Controllers\TeacherRoomController;
use Illuminate\Support\Facades\Route;

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
Route::post('/join-room', [RoomController::class, 'join'])->middleware('throttle:20,1')->name('room.join');
Route::get('/room/{code}', [RoomController::class, 'showJoinForm'])->name('room.form');
Route::post('/room/{code}/enter', [RoomController::class, 'enter'])->middleware('throttle:30,1')->name('room.enter');

// Salir de la sala actual (limpiar sesión)
Route::post('/exit-room', [StudentSessionController::class, 'destroy'])->name('room.exit');

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS PARA ESTUDIANTES (MIDDLEWARE PARTICIPANT)
|--------------------------------------------------------------------------
*/
// Entrada compatible: envía a cada persona a su área correspondiente.
Route::get('/dashboard', function () {
    if (auth()->check()) {
        return auth()->user()->rol === 'admin'
            ? redirect()->route('admin.teachers.index')
            : redirect()->route('teacher.dashboard');
    }

    if (session()->has('participant_id')) {
        return redirect()->route('student.dashboard');
    }

    return redirect()->route('home');
})->name('dashboard');

Route::middleware(['participant'])->prefix('student')->group(function () {

    // Dashboard principal del estudiante
    Route::get('/dashboard', StudentDashboardController::class)->name('student.dashboard');

    // Calculadora de Huella de Carbono
    Route::get('/calcular-huella', [CarbonCalculatorController::class, 'showForm'])->name('carbon.form');
    Route::post('/calcular-huella', [CarbonCalculatorController::class, 'calculate'])->name('carbon.calculate');

    // Actividades
    Route::get('/activities', [StudentMissionController::class, 'index'])->name('activities.index');
    Route::get('/missions/{token}', [StudentMissionController::class, 'show'])
        ->middleware('throttle:60,1')
        ->name('student.missions.show');
    Route::post('/missions/{token}/complete', [StudentMissionController::class, 'complete'])
        ->middleware('throttle:20,1')
        ->name('student.missions.complete');

    Route::get('/impostor', [StudentImpostorController::class, 'lobby'])->name('student.impostor.lobby');
    Route::get('/impostor/{game}', [StudentImpostorController::class, 'show'])->name('student.impostor.show');
    Route::post('/impostor/{game}/clue', [StudentImpostorController::class, 'clue'])->middleware('throttle:10,1')->name('student.impostor.clue');
    Route::post('/impostor/{game}/vote', [StudentImpostorController::class, 'vote'])->middleware('throttle:10,1')->name('student.impostor.vote');
    Route::get('/impostor/{game}/results', [StudentImpostorController::class, 'results'])->name('student.impostor.results');

    // Ranking escolar
    Route::get('/ranking', RankingController::class)->name('ranking');

});

/*
|--------------------------------------------------------------------------
| RUTAS SOLO PARA ADMINISTRACION / PROFESORES (MIDDLEWARE AUTH + ADMIN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'teacher'])->prefix('teacher')->group(function () {

    Route::get('/dashboard', TeacherDashboardController::class)->name('teacher.dashboard');

    Route::get('/courses', [TeacherCourseController::class, 'index'])->name('teacher.courses.index');
    Route::get('/courses/create', [TeacherCourseController::class, 'create'])->name('teacher.courses.create');
    Route::post('/courses', [TeacherCourseController::class, 'store'])->name('teacher.courses.store');
    Route::get('/courses/{course}', [TeacherCourseController::class, 'show'])->name('teacher.courses.show');
    Route::get('/courses/{course}/edit', [TeacherCourseController::class, 'edit'])->name('teacher.courses.edit');
    Route::put('/courses/{course}', [TeacherCourseController::class, 'update'])->name('teacher.courses.update');
    Route::post('/courses/{course}/archive', [TeacherCourseController::class, 'archive'])->name('teacher.courses.archive');

    Route::get('/courses/{course}/sessions/create', [TeacherRoomController::class, 'create'])->name('teacher.sessions.create');
    Route::post('/courses/{course}/sessions', [TeacherRoomController::class, 'store'])->name('teacher.sessions.store');
    Route::get('/sessions/{room}', [TeacherRoomController::class, 'show'])->name('teacher.sessions.show');
    Route::get('/sessions/{room}/report', TeacherReportController::class)->name('teacher.sessions.report');
    Route::post('/sessions/{room}/open', [TeacherRoomController::class, 'open'])->name('teacher.sessions.open');
    Route::post('/sessions/{room}/close', [TeacherRoomController::class, 'close'])->name('teacher.sessions.close');
    Route::post('/sessions/{room}/archive', [TeacherRoomController::class, 'archive'])->name('teacher.sessions.archive');

    Route::get('/activities', [TeacherActivityController::class, 'index'])->name('teacher.activities.index');

    // Gestión de Actividades
    Route::get('/activities/create', [TeacherActivityController::class, 'create'])->name('activities.create');
    Route::post('/activities', [TeacherActivityController::class, 'store'])->name('activities.store');
    Route::get('/activities/{activity}/edit', [TeacherActivityController::class, 'edit'])->name('activities.edit');
    Route::put('/activities/{activity}', [TeacherActivityController::class, 'update'])->name('activities.update');
    Route::delete('/activities/{activity}', [TeacherActivityController::class, 'destroy'])->name('activities.destroy');

    Route::get('/sessions/{room}/missions', [TeacherMissionController::class, 'index'])->name('teacher.missions.index');
    Route::put('/sessions/{room}/missions', [TeacherMissionController::class, 'update'])->name('teacher.missions.update');
    Route::get('/sessions/{room}/missions/{mission}/qr', [TeacherMissionController::class, 'qr'])->name('teacher.missions.qr');

    Route::post('/sessions/{room}/impostor', [TeacherImpostorController::class, 'start'])->name('teacher.impostor.start');
    Route::get('/impostor/{game}', [TeacherImpostorController::class, 'show'])->name('teacher.impostor.show');
    Route::post('/impostor/{game}/voting', [TeacherImpostorController::class, 'voting'])->name('teacher.impostor.voting');
    Route::post('/impostor/{game}/finish', [TeacherImpostorController::class, 'finish'])->name('teacher.impostor.finish');
    Route::get('/impostor/{game}/results', [TeacherImpostorController::class, 'results'])->name('teacher.impostor.results');

    // Gestión de Perfil Administrador
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/teachers', [AdminTeacherApprovalController::class, 'index'])
        ->name('admin.teachers.index');
    Route::post('/teachers/{teacher}/approve', [AdminTeacherApprovalController::class, 'approve'])
        ->name('admin.teachers.approve');
});

require __DIR__.'/auth.php';
