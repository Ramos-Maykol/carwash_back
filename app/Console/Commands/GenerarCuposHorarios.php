<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sucursal;
use App\Models\CupoHorario;
use App\Models\Empleado;
use Carbon\Carbon;

class GenerarCuposHorarios extends Command
{
    /**
     * La firma y nombre de tu comando.
     * Así lo llamarás: php artisan app:generar-cupos
     * El {--dias=30} es una opción para decirle cuántos días generar.
     */
    protected $signature = 'app:generar-cupos {--dias=30 : El número de días a futuro para generar.}';

    /**
     * La descripción de tu comando.
     */
    protected $description = 'Genera los cupos de horarios disponibles basados en las plantillas de HorarioTrabajo';

    
    /**
     * Aquí va toda la lógica cuando ejecutas el comando.
     */
    public function handle()
    {
        $this->info('Iniciando la generación de cupos...');

        $diasAGenerar = $this->option('dias');
        $hoy = Carbon::today();
        // NOTA: La duración de cada cupo.
        // En un sistema profesional, esto debería estar en la tabla 'sucursales' o 'servicios'.
        // Por ahora, lo dejaremos fijo en 30 minutos.
        $duracionCupo = 30; // en minutos

        // --- 1. Limpieza de Cupos Viejos ---
        // Borra cupos 'disponibles' de ayer hacia atrás que nunca se reservaron.
        $this->info('Limpiando cupos antiguos...');
        $cuposBorrados = CupoHorario::where('hora_inicio', '<', $hoy)
                                   ->where('estado', 'disponible')
                                   ->delete();
        $this->info("Se eliminaron $cuposBorrados cupos antiguos.");

        // --- 2. Obtener Sucursales y Horarios ---
        // Usamos 'with' para cargar las relaciones de forma eficiente (Eager Loading)
        $sucursales = Sucursal::with('horariosTrabajo')->get();

        if ($sucursales->isEmpty()) {
            $this->warn('No se encontraron sucursales. Terminando.');
            return 0;
        }

        // --- 3. El Bucle Principal de Generación ---
        foreach ($sucursales as $sucursal) {
            $this->info("Procesando sucursal: {$sucursal->nombre}");

            $empleadosActivos = Empleado::query()
                ->where('sucursal_id', $sucursal->id)
                ->where('activo', true)
                ->whereHas('cargo', function ($query) {
                    $query->where('nombre_cargo', 'LIKE', '%Lavador%');
                })
                ->pluck('id');

            $empleadoIdsParaSlots = $empleadosActivos->isNotEmpty() ? $empleadosActivos : collect([null]);
            
            // Bucle por cada día (desde hoy hasta N días)
            for ($i = 0; $i < $diasAGenerar; $i++) {
                $fechaActual = $hoy->copy()->addDays($i);
                $diaSemana = $fechaActual->dayOfWeekIso; // 1 (Lunes) a 7 (Domingo)

                // Buscar la plantilla de horario para ESTE día de la semana
                $horario = $sucursal->horariosTrabajo->firstWhere('dia_semana', $diaSemana);

                // Si no hay horario (ej. el carwash cierra los Domingos), saltar al siguiente día.
                if (!$horario) {
                    continue;
                }

                // Convertir las horas de texto (ej. "09:00:00") a objetos Carbon
                $horaInicioDia = Carbon::parse($horario->hora_inicio);
                $horaFinDia = Carbon::parse($horario->hora_fin);

                // Bucle para crear los cupos dentro del día
                $horaActualCupo = $horaInicioDia->copy();

                while ($horaActualCupo->lessThan($horaFinDia)) {
                    
                    // Combinar la fecha (ej. 2025-11-03) con la hora (ej. 09:30:00)
                    $inicioCupo = $fechaActual->copy()->setTime($horaActualCupo->hour, $horaActualCupo->minute, 0);
                    $finCupo = $inicioCupo->copy()->addMinutes($duracionCupo);

                    // --- 4. La Magia: firstOrCreate ---
                    // Esto es súper importante. Intenta buscar un cupo con esta sucursal y hora de inicio.
                    // Si NO lo encuentra, lo crea con los datos del segundo array.
                    // Esto evita duplicados si corres el comando mil veces.
                    foreach ($empleadoIdsParaSlots as $empleadoId) {
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

                    // Movernos al siguiente cupo
                    $horaActualCupo->addMinutes($duracionCupo);
                }
            }
        }

        $this->info('¡Generación de cupos completada exitosamente!');
        return 0;
    }
}
