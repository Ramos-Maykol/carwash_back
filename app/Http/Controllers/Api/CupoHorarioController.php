<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CupoHorario;
use App\Models\Empleado;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CupoHorarioController extends Controller
{
    /**
     * Muestra los cupos de horarios disponibles.
     *
     * Este endpoint es PÚBLICO.
     * Recibe dos parámetros por la URL (Query Params):
     * ?sucursal_id=1
     * ?fecha=2025-11-10 (Formato YYYY-MM-DD)
     */
    public function index(Request $request)
    {
        try {
            // 1. Validar la entrada
            $datosValidados = $request->validate([
                'sucursal_id' => 'required|integer',
                'fecha' => 'required|date_format:Y-m-d',
            ]);

            $sucursalId = $datosValidados['sucursal_id'];
            
            // 2. Verificar si la sucursal existe (no crear datos por defecto desde un endpoint público)
            $sucursal = Sucursal::find($sucursalId);
            if (!$sucursal) {
                return response()->json([
                    'message' => 'Sucursal no encontrada.'
                ], 404);
            }
            
            $fecha = Carbon::parse($datosValidados['fecha'])->startOfDay(); // Asegurarnos que es al inicio del día
            $hoy = Carbon::today();

            // 3. Validar que la fecha no sea en el pasado
            if ($fecha->isPast() && !$fecha->isSameDay($hoy)) {
                return response()->json([
                    'message' => 'No puedes consultar fechas en el pasado.'
                ], 422); // 422 Unprocessable Entity
            }

            // 4. Verificar si ya existen cupos para esta fecha y sucursal
            $cuposExistentes = CupoHorario::where('sucursal_id', $sucursalId)
                ->whereDate('hora_inicio', $fecha)
                ->exists();

            // 5. Si no hay cupos, generarlos automáticamente
            if (!$cuposExistentes) {
                $this->generarCuposParaFecha($sucursalId, $fecha);
            }

            // 6. La consulta Mágica
            // Busca cupos en la sucursal correcta, para la fecha correcta,
            // que estén 'disponibles', y (MUY IMPORTANTE) que la hora de inicio
            // aún no haya pasado (si están consultando para hoy).
            $cupos = CupoHorario::where('sucursal_id', $sucursalId)
                ->where('estado', 'disponible')
                ->whereDate('hora_inicio', $fecha) // Compara solo la parte de la FECHA
                ->where('hora_inicio', '>=', Carbon::now()) // No mostrar cupos que ya pasaron (ej. 9:00 AM si son las 9:05 AM)
                ->orderBy('hora_inicio', 'asc')
                ->get();

            // 7. Devolver el JSON
            return response()->json($cupos);
        } catch (\Exception $e) {
            \Log::error('Error en CupoHorarioController@index: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'message' => 'Error interno del servidor: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Genera cupos horarios para una fecha específica basándose en los horarios de trabajo de la sucursal.
     */
    private function generarCuposParaFecha($sucursalId, Carbon $fecha)
    {
        $sucursal = Sucursal::with('horariosTrabajo')->find($sucursalId);
        
        if (!$sucursal) {
            return;
        }

        $empleadosActivos = Empleado::query()
            ->where('sucursal_id', $sucursal->id)
            ->where('activo', true)
            ->whereHas('cargo', function ($query) {
                $query->where('nombre_cargo', 'LIKE', '%Lavador%');
            })
            ->pluck('id');

        $diaSemana = $fecha->dayOfWeekIso; // 1 (Lunes) a 7 (Domingo)
        $duracionCupo = 30; // minutos

        // Buscar la plantilla de horario para este día de la semana
        $horario = $sucursal->horariosTrabajo->firstWhere('dia_semana', $diaSemana);

        // Si no hay horario (ej. el carwash cierra los Domingos), no generar cupos
        if (!$horario) {
            return;
        }

        // Convertir las horas de texto a objetos Carbon
        $horaInicioDia = Carbon::parse($horario->hora_inicio);
        $horaFinDia = Carbon::parse($horario->hora_fin);

        // Bucle para crear los cupos dentro del día
        $horaActualCupo = $horaInicioDia->copy();

        while ($horaActualCupo->lessThan($horaFinDia)) {
            // Combinar la fecha con la hora
            $inicioCupo = $fecha->copy()->setTime($horaActualCupo->hour, $horaActualCupo->minute, 0);
            $finCupo = $inicioCupo->copy()->addMinutes($duracionCupo);

            // Solo crear si el cupo está en el futuro
            if ($inicioCupo->isFuture() || $inicioCupo->isCurrentMinute()) {
                // Regla de negocio: cantidad de cupos = cantidad de empleados activos en la sucursal.
                // Si no hay empleados asignados, se crea 1 cupo (fallback) para no romper el flujo.
                $empleadoIdsParaSlot = $empleadosActivos->isNotEmpty() ? $empleadosActivos : collect([null]);

                foreach ($empleadoIdsParaSlot as $empleadoId) {
                    CupoHorario::firstOrCreate(
                        [
                            'sucursal_id' => $sucursal->id,
                            'hora_inicio' => $inicioCupo,
                            'empleado_id' => $empleadoId,
                        ],
                        [
                            'hora_fin' => $finCupo,
                            'estado' => 'disponible',
                        ]
                    );
                }
            }

            // Movernos al siguiente cupo
            $horaActualCupo->addMinutes($duracionCupo);
        }
    }
}
