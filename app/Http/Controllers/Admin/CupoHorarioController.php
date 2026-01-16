<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CupoHorario;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class CupoHorarioController extends Controller
{
    /**
     * Muestra una lista de cupos, filtrada por sucursal y fecha.
     * A diferencia del endpoint público, este muestra TODOS los cupos (disponibles, reservados, etc.)
     */
    public function index(Request $request)
    {
        $request->validate([
            'sucursal_id' => 'required|integer|exists:sucursals,id',
            'fecha' => 'required|date_format:Y-m-d',
        ]);

        $fecha = Carbon::parse($request->fecha);

        $cupos = CupoHorario::where('sucursal_id', $request->sucursal_id)
            ->whereDate('hora_inicio', $fecha)
            ->orderBy('hora_inicio')
            ->get();
            
        return response()->json($cupos);
    }

    /**
     * Almacena un nuevo cupo de horario (creación manual).
     */
    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'sucursal_id' => 'required|integer|exists:sucursals,id',
            'empleado_id' => 'nullable|integer|exists:empleados,id',
            'hora_inicio' => [
                'required',
                'date_format:Y-m-d H:i:s',
                Rule::unique('cupo_horarios', 'hora_inicio')->where(function ($query) use ($request) {
                    return $query
                        ->where('sucursal_id', $request->sucursal_id)
                        ->where('empleado_id', $request->input('empleado_id'));
                }),
            ],
            'hora_fin' => 'required|date_format:Y-m-d H:i:s|after:hora_inicio',
            'estado' => 'required|string|in:disponible,reservado,bloqueado',
        ]);

        $cupo = CupoHorario::create($datosValidados);

        return response()->json($cupo, 201);
    }

    /**
     * Muestra un cupo de horario específico.
     */
    public function show(CupoHorario $cupoHorario)
    {
        return response()->json($cupoHorario);
    }

    /**
     * Actualiza un cupo de horario (ej. para bloquearlo).
     */
    public function update(Request $request, CupoHorario $cupoHorario)
    {
        $datosValidados = $request->validate([
            'estado' => 'required|string|in:disponible,reservado,bloqueado',
            // Opcionalmente, permitir cambiar horas si no está reservado
            'empleado_id' => 'sometimes|nullable|integer|exists:empleados,id',
            'hora_inicio' => [
                'sometimes',
                'date_format:Y-m-d H:i:s',
                Rule::unique('cupo_horarios', 'hora_inicio')->ignore($cupoHorario->id)->where(function ($query) use ($request, $cupoHorario) {
                    $empleadoId = $request->has('empleado_id') ? $request->input('empleado_id') : $cupoHorario->empleado_id;
                    return $query
                        ->where('sucursal_id', $cupoHorario->sucursal_id)
                        ->where('empleado_id', $empleadoId);
                }),
            ],
            'hora_fin' => 'sometimes|date_format:Y-m-d H:i:s|after:hora_inicio',
        ]);

        $cupoHorario->update($datosValidados);

        return response()->json($cupoHorario);
    }

    /**
     * Elimina un cupo de horario.
     */
    public function destroy(CupoHorario $cupoHorario)
    {
        // Regla de negocio: No se puede borrar un cupo que ya está reservado.
        if ($cupoHorario->estado === 'reservado') {
            return response()->json([
                'message' => 'No se puede eliminar un cupo que ya tiene una reserva activa.'
            ], 409); // 409 Conflicto
        }

        $cupoHorario->delete();

        return response()->json(null, 204);
    }
}
