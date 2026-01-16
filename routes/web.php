<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ServicioController;

Route::get('/', function () {
    return view('welcome');
});

// --- Rutas Públicas de Autenticación ---
// Tu app Ionic llamará a estas URLs
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// --- Ruta Pública del Catálogo ---
Route::get('/servicios', [ServicioController::class, 'index']);

// --- Rutas Protegidas ---
// Todas las rutas dentro de este grupo requerirán un Token válido
// (Middleware 'auth:sanctum')
Route::middleware('auth:sanctum')->group(function () {
    
    // Ruta para cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout']);

    // Ruta de prueba para verificar que el token funciona
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // --- Aquí pondremos el resto de tus endpoints ---
    // Ej. GET /api/servicios
    // Ej. GET /api/cupos-disponibles
    // Ej. POST /api/reservas
    // Ej. GET /api/mis-reservas
});