<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CargoController extends Controller
{
    /**
     * Muestra una lista de todos los cargos.
     */
    public function index()
    {
        return response()->json(Cargo::all());
    }

    /**
     * Almacena un nuevo cargo.
     */
    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'nombre_cargo' => 'required|string|max:255|unique:cargos,nombre_cargo',
            'descripcion' => 'nullable|string',
        ]);

        $cargo = Cargo::create($datosValidados);

        return response()->json($cargo, 201); // 201 Creado
    }

    /**
     * Muestra un cargo específico.
     */
    public function show(Cargo $cargo)
    {
        return response()->json($cargo);
    }

    /**
     * Actualiza un cargo específico.
     */
    public function update(Request $request, Cargo $cargo)
    {
        $datosValidados = $request->validate([
            'nombre_cargo' => ['required', 'string', 'max:255', Rule::unique('cargos', 'nombre_cargo')->ignore($cargo->id)],
            'descripcion' => 'nullable|string',
        ]);

        $cargo->update($datosValidados);

        return response()->json($cargo);
    }

    /**
     * Elimina un cargo.
     */
    public function destroy(Cargo $cargo)
    {
        try {
            // Revisar si algún empleado tiene este cargo
            if ($cargo->empleados()->count() > 0) {
                return response()->json([
                    'message' => 'No se puede eliminar el cargo, hay empleados asignados a él.'
                ], 409); // 409 Conflicto
            }
            
            $cargo->delete();

            return response()->json(null, 204); // 204 Sin Contenido

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el cargo.'
            ], 500);
        }
    }
}
