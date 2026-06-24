<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrCodeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Sistema de Control de Asistencia (Panel Admin)
|--------------------------------------------------------------------------
*/

// Redirigir la raíz al dashboard (si autenticado) o al login
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard — solo admins autenticados
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/qrcodes', [QrCodeController::class, 'index'])->name('qrcodes');
});

// Perfil de usuario
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
