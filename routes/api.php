<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ServicioController;
use App\Http\Controllers\Api\CupoHorarioController;
use App\Http\Controllers\Api\ReservaController;
use App\Http\Controllers\Api\VehiculoController;
use App\Http\Controllers\Admin\ReservaController as AdminReservaController;
use App\Http\Controllers\Admin\ServicioController as AdminServicioController;
use App\Http\Controllers\Admin\CargoController as AdminCargoController;
use App\Http\Controllers\Admin\EmpleadoController as AdminEmpleadoController;
use App\Http\Controllers\Admin\TipoVehiculoController as AdminTipoVehiculoController;
use App\Http\Controllers\Admin\SucursalController as AdminSucursalController;
use App\Http\Controllers\Admin\HorarioTrabajoController as AdminHorarioTrabajoController;
// 1. IMPORTAMOS EL NUEVO CONTROLADOR ADMIN DE CUPOS
use App\Http\Controllers\Admin\CupoHorarioController as AdminCupoHorarioController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AdminDashboardController;

/*
... (descripción)
*/

// =========================================================================
// == RUTAS PÚBLICAS (No requieren autenticación)
// =========================================================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/servicios', [AdminServicioController::class, 'index']);
Route::get('/servicios/{servicio}', [AdminServicioController::class, 'show']);
Route::get('/cupos-disponibles', [CupoHorarioController::class, 'index']);
Route::get('/tipos-vehiculo', [AdminTipoVehiculoController::class, 'index']);
Route::get('/sucursales', [AdminSucursalController::class, 'index']);


// =========================================================================
// == RUTAS PROTEGIDAS (CLIENTE)
// =========================================================================
Route::middleware('auth:sanctum')->group(function () {
    
    // --- Autenticación (Usuario autenticado) ---
    Route::get('/user', function (Request $request) {
        return $request->user()->load('cliente', 'empleado');
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- Reservas (Cliente) ---
    Route::post('/reservas', [ReservaController::class, 'store']);
    Route::patch('/reservas/{reserva}/estado', [ReservaController::class, 'updateEstado']);
    Route::get('/mis-reservas', [ReservaController::class, 'index']);

    // --- Vehículos (Cliente) ---
    Route::apiResource('/vehiculos', VehiculoController::class);
});


// =========================================================================
// == RUTAS PROTEGIDAS (ADMIN)
// =========================================================================
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {

    // Gestión de Reservas
    Route::apiResource('/reservas', AdminReservaController::class)->only([
        'index', 'show', 'update'
    ]);

    // Gestión de Servicios (CRUD Completo)
    Route::apiResource('/servicios', AdminServicioController::class);

    // Gestión de Cargos
    Route::apiResource('/cargos', AdminCargoController::class);

    // Gestión de Empleados
    Route::apiResource('/empleados', AdminEmpleadoController::class);

    // Gestión de Tipos de Vehículo
    Route::apiResource('/tipos-vehiculo', AdminTipoVehiculoController::class);

    // Gestión de Sucursales
    Route::apiResource('/sucursales', AdminSucursalController::class)->except([
        'index'
    ]);

    // Gestión de Horarios de Trabajo (Plantillas)
    Route::apiResource('/horarios-trabajo', AdminHorarioTrabajoController::class);

    // 2. AÑADIMOS EL CRUD DE CUPOS DE HORARIO (Agenda Real)
    // GET, POST, PUT, DELETE /api/admin/cupos-horarios
    Route::apiResource('/cupos-horarios', AdminCupoHorarioController::class);

    Route::get('/dashboard-stats', [AdminDashboardController::class, 'stats']);

    Route::get('reportes/data', [ReportController::class, 'getReportData']);
});

