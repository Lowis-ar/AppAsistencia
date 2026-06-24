<?php

use App\Http\Controllers\AttendanceApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Sistema de Control de Asistencia
|--------------------------------------------------------------------------
|
| Estas rutas son consumidas por la aplicación móvil.
| La autenticación se maneja via Laravel Sanctum (Bearer Token).
|
*/

// -----------------------------------------------------------------------
// Rutas públicas (sin autenticación)
// -----------------------------------------------------------------------

Route::post('/login', [AttendanceApiController::class, 'login'])
    ->name('api.login');

// -----------------------------------------------------------------------
// Rutas protegidas (requieren Bearer Token de Sanctum)
// -----------------------------------------------------------------------

Route::middleware('auth:sanctum')->group(function () {

    // Información del usuario autenticado
    Route::get('/user', [AttendanceApiController::class, 'me'])
        ->name('api.user');

    // Cerrar sesión (revocar token)
    Route::post('/logout', [AttendanceApiController::class, 'logout'])
        ->name('api.logout');

    // Registro de asistencia
    Route::prefix('attendance')->name('api.attendance.')->group(function () {

        // Registrar check_in o check_out
        Route::post('/register', [AttendanceApiController::class, 'register'])
            ->name('register');

        // Estado actual del usuario (¿está dentro o fuera?)
        Route::get('/status', [AttendanceApiController::class, 'status'])
            ->name('status');
    });
});
