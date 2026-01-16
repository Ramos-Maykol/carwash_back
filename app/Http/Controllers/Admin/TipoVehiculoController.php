<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoVehiculo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TipoVehiculoController extends Controller
{
    /**
     * Muestra una lista de todos los tipos de vehículo.
     */
    public function index()
    {
        return response()->json(TipoVehiculo::all());
    }

    /**
     * Almacena un nuevo tipo de vehículo.
     */
    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'nombre' => 'required|string|max:255|unique:tipos_vehiculo,nombre',
            'descripcion' => 'nullable|string',
        ]);

        $tipoVehiculo = TipoVehiculo::create($datosValidados);

        return response()->json($tipoVehiculo, 201); // 201 Creado
    }

    /**
     * Muestra un tipo de vehículo específico.
     */
    public function show(TipoVehiculo $tipoVehiculo)
    {
        return response()->json($tipoVehiculo);
    }

    /**
     * Actualiza un tipo de vehículo específico.
     */
    public function update(Request $request, TipoVehiculo $tipoVehiculo)
    {
        $datosValidados = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('tipos_vehiculo', 'nombre')->ignore($tipoVehiculo->id)],
            'descripcion' => 'nullable|string',
        ]);

        $tipoVehiculo->update($datosValidados);

        return response()->json($tipoVehiculo);
    }

    /**
     * Elimina un tipo de vehículo.
     */
    public function destroy(TipoVehiculo $tipoVehiculo)
    {
        try {
            // Revisar si algún precio o vehículo depende de este tipo
            if ($tipoVehiculo->preciosServicio()->count() > 0 || $tipoVehiculo->vehiculos()->count() > 0) {
                return response()->json([
                    'message' => 'No se puede eliminar, este tipo de vehículo ya está en uso (en precios o vehículos de clientes).'
                ], 409); // 409 Conflicto
            }
            
            $tipoVehiculo->delete();

            return response()->json(null, 204); // 204 Sin Contenido

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el tipo de vehículo.'
            ], 500);
        }
    }
}
