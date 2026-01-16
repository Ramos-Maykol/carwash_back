<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    /**
     * Muestra una lista de todos los servicios con sus precios.
     * Este es un endpoint PÚBLICO.
     */
    public function index()
    {
        // Esta es la magia de las relaciones que definimos en los Modelos.
        // 1. Obtenemos todos los 'Servicios'.
        // 2. Con 'with()', le decimos a Laravel que también traiga
        //    la relación 'preciosServicios' (la tabla pivote).
        // 3. Y de 'preciosServicios', que también traiga la
        //    relación 'tipoVehiculo' (para saber el nombre, ej. "SUV").
        //
        // El resultado es un JSON limpio y listo para que tu app lo muestre.
        
        $servicios = Servicio::with(['preciosServicios.tipoVehiculo'])
            ->get();

        return response()->json($servicios);
    }

    /**
     * Store a newly created resource in storage.
     * (Lo usaremos más adelante para el panel de Admin)
     */
    public function store(Request $request)
    {
        // Lógica para que un admin cree un servicio (requerirá permiso)
    }

    /**
     * Display the specified resource.
     * (Lo usaremos si el cliente quiere ver el detalle de UN servicio)
     */
    public function show(Servicio $servicio)
    {
        // Cargar el servicio con sus precios
        $servicio->load(['preciosServicios.tipoVehiculo']);
        return response()->json($servicio);
    }

    /**
     * Update the specified resource in storage.
     * (Lo usaremos más adelante para el panel de Admin)
     */
    public function update(Request $request, Servicio $servicio)
    {
        // Lógica para que un admin actualice un servicio (requerirá permiso)
    }

    /**
     * Remove the specified resource from storage.
     * (Lo usaremos más adelante para el panel de Admin)
     */
    public function destroy(Servicio $servicio)
    {
        // Lógica para que un admin elimine un servicio (requerirá permiso)
    }
}
