<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SucursalController extends Controller
{
    /**
     * Muestra una lista de todas las sucursales.
     */
    public function index()
    {
        // La lista de sucursales también es útil para el cliente
        // al momento de elegir una, por eso no la protegemos aquí.
        
        $sucursales = Sucursal::all();
        
        // Si no hay sucursales, crear una por defecto
        if ($sucursales->isEmpty()) {
            $sucursal = Sucursal::firstOrCreate(
                ['nombre' => 'Carwash Sede Principal'],
                [
                    'direccion' => 'Av. Ejemplo 123, Ciudad',
                    'telefono' => '987654321',
                ]
            );
            
            // Crear horarios de trabajo para esta sucursal
            $diasLaborables = [1, 2, 3, 4, 5]; // Lunes a Viernes
            foreach ($diasLaborables as $dia) {
                $sucursal->horariosTrabajo()->firstOrCreate(
                    ['dia_semana' => $dia],
                    [
                        'hora_inicio' => '09:00:00',
                        'hora_fin' => '17:00:00',
                    ]
                );
            }
            
            // Horario de sábado
            $sucursal->horariosTrabajo()->firstOrCreate(
                ['dia_semana' => 6],
                [
                    'hora_inicio' => '09:00:00',
                    'hora_fin' => '13:00:00',
                ]
            );
            
            $sucursales = Sucursal::all();
        }
        
        return response()->json($sucursales);
    }

    /**
     * Almacena una nueva sucursal.
     */
    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'nombre' => 'required|string|max:255|unique:sucursals,nombre',
            'direccion' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'esta_activa' => 'sometimes|boolean',
        ]);

        $sucursal = Sucursal::create($datosValidados);

        return response()->json($sucursal, 201); // 201 Creado
    }

    /**
     * Muestra una sucursal específica.
     */
    public function show($id)
    {
        $sucursal = Sucursal::with('horariosTrabajo')->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $sucursal->id,
                'nombre' => $sucursal->nombre,
                'direccion' => $sucursal->direccion,
                'telefono' => $sucursal->telefono,
                'esta_activa' => (bool) $sucursal->esta_activa,
                'horarios_trabajo' => $sucursal->horariosTrabajo,
            ]
        ]);
    }

    /**
     * Actualiza una sucursal específica.
     */
    public function update(Request $request, Sucursal $sucursal)
    {
        $datosValidados = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('sucursals', 'nombre')->ignore($sucursal->id)],
            'direccion' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'esta_activa' => 'sometimes|boolean',
        ]);

        $sucursal->update($datosValidados);

        return response()->json($sucursal);
    }

    /**
     * Elimina una sucursal.
     */
    public function destroy(Sucursal $sucursal)
    {
        try {
            // Revisar si hay cupos u horarios ligados
            if ($sucursal->horariosTrabajo()->count() > 0 || $sucursal->cuposHorarios()->count() > 0) {
                return response()->json([
                    'message' => 'No se puede eliminar, esta sucursal tiene horarios o cupos definidos.'
                ], 409); // 409 Conflicto
            }
            
            $sucursal->delete();

            return response()->json(null, 204); // 204 Sin Contenido

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la sucursal.'
            ], 500);
        }
    }
}
