<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HorarioTrabajo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class HorarioTrabajoController extends Controller
{
    /**
     * Muestra todos los horarios de una sucursal específica.
     */
    public function index(Request $request)
    {
        $request->validate(['sucursal_id' => 'required|integer|exists:sucursals,id']);
        
        $horarios = HorarioTrabajo::where('sucursal_id', $request->sucursal_id)
            ->orderBy('dia_semana')
            ->get();
            
        return response()->json($horarios);
    }

    /**
     * Almacena un nuevo horario de trabajo para una sucursal.
     */
    public function store(Request $request)
    {
        Log::info('Payload recibido horarios trabajo', $request->all());

        $datosValidados = $request->validate([
            'sucursal_id' => 'required|integer|exists:sucursals,id',
            'horarios' => 'required|array',
            'horarios.*.dia_semana' => [
                'required',
                'integer',
                'between:1,7',
            ],
            'horarios.*.hora_inicio' => 'required|date_format:H:i',
            'horarios.*.hora_fin' => 'required|date_format:H:i',
        ]);

        foreach ($datosValidados['horarios'] as $horarioItem) {
            $inicio = Carbon::createFromFormat('H:i', $horarioItem['hora_inicio']);
            $fin = Carbon::createFromFormat('H:i', $horarioItem['hora_fin']);
            if ($fin <= $inicio) {
                return response()->json(['error' => "En el día {$horarioItem['dia_semana']} la hora fin debe ser posterior a la hora inicio"], 422);
            }
        }

        try {
            $resultado = DB::transaction(function () use ($datosValidados) {
                HorarioTrabajo::where('sucursal_id', $datosValidados['sucursal_id'])->delete();

                $creados = [];
                foreach ($datosValidados['horarios'] as $entrada) {
                    $entrada['sucursal_id'] = $datosValidados['sucursal_id'];
                    $creados[] = HorarioTrabajo::create($entrada);
                }

                return $creados;
            });

            return response()->json([
                'data' => $resultado,
                'count' => count($resultado),
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Error guardando horarios de trabajo', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Muestra un horario de trabajo específico.
     */
    public function show(HorarioTrabajo $horarioTrabajo)
    {
        return response()->json($horarioTrabajo);
    }

    /**
     * Actualiza un horario de trabajo.
     */
    public function update(Request $request, HorarioTrabajo $horarioTrabajo)
    {
        $datosValidados = $request->validate([
            // No permitimos cambiar la sucursal_id o el dia_semana en una actualización,
            // es más limpio borrar y crear uno nuevo.
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ]);
        
        $horarioTrabajo->update($datosValidados);

        return response()->json($horarioTrabajo);
    }

    /**
     * Elimina un horario de trabajo.
     */
    public function destroy(HorarioTrabajo $horarioTrabajo)
    {
        $horarioTrabajo->delete();
        
        // ¡Importante! Al borrar un horario, deberíamos también borrar
        // los cupos futuros 'disponibles' generados por este horario.
        // (Lógica de limpieza más avanzada)
        
        // Por ahora, solo borramos la plantilla:
        return response()->json(null, 204);
    }
}
