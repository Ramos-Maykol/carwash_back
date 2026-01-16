<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReservaController extends Controller
{
    /**
     * Muestra una lista de TODAS las reservas.
     * Protegido por rol: 'admin'
     */
    public function index(Request $request)
    {
        // Validar filtros opcionales (ej. /api/admin/reservas?estado=confirmada)
        $request->validate([
            'estado' => ['nullable', 'string', Rule::in(['confirmada', 'en_proceso', 'completada', 'cancelada'])],
            'fecha' => 'nullable|date',
        ]);

        // Empezar la consulta
        $query = Reserva::query()->with([
            'cliente', // Cargar el cliente que reservó
            'vehiculo.tipoVehiculo', // El vehículo y su tipo
            'precioServicio.servicio', // El servicio
            'cupoHorario' // La fecha y hora
        ]);

        // Aplicar filtros si existen
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha')) {
            $query->whereHas('cupoHorario', function ($q) use ($request) {
                $q->whereDate('hora_inicio', $request->fecha);
            });
        }

        $reservas = $query->orderBy('id', 'desc')->paginate(15); // Paginado

        return response()->json($reservas);
    }

    /**
     * Muestra una reserva específica.
     * Protegido por rol: 'admin'
     */
    public function show(Reserva $reserva)
    {
        // Cargar todas las relaciones para ver el detalle completo
        $reserva->load([
            'cliente',
            'vehiculo.tipoVehiculo',
            'precioServicio.servicio',
            'cupoHorario',
            'asignaciones.empleado', // Ver qué empleados están asignados
            'pago' // Ver el estado del pago
        ]);

        return response()->json($reserva);
    }

    /**
     * Actualiza el estado de una reserva (ej. marcar como completada).
     * Protegido por rol: 'admin'
     */
    public function update(Request $request, Reserva $reserva)
    {
        $datosValidados = $request->validate([
            'estado' => ['required', 'string', Rule::in(['confirmada', 'en_proceso', 'completada', 'cancelada'])],
        ]);

        $reserva->update($datosValidados);

        return response()->json([
            'message' => 'Estado de la reserva actualizado.',
            'reserva' => $reserva->fresh() // 'fresh' recarga el modelo desde la BD
        ]);
    }
}
