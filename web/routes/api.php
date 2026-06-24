<?php

use App\Http\Controllers\AttendanceApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Estas rutas son consumidas por la aplicación móvil (Modelo Quiosco).
|
*/

// Login del Administrador
Route::post('/auth/login', [AttendanceApiController::class, 'login']);

// Ruta Pública: Empleado escanea su QR (que contiene su ID)
Route::post('/attendance', [AttendanceApiController::class, 'registerAttendance']);

// Rutas protegidas (Requieren token del Administrador)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/employees', [AttendanceApiController::class, 'registerEmployee']);
    Route::get('/employees', [AttendanceApiController::class, 'listEmployees']);
});
